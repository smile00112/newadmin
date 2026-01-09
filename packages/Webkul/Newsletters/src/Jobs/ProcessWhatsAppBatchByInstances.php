<?php

namespace Webkul\Newsletters\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Newsletters\Events\MailingListStatsUpdated;
use Webkul\Newsletters\Events\WhatsAppMessageSent;
use Webkul\Newsletters\Models\MailingList;
use Webkul\Newsletters\Models\CustomerNumber;
use Webkul\Newsletters\Services\WhatsAppMailingService;
use Webkul\Newsletters\Jobs\SendWhatsAppMessageWithEvent;
use Webkul\Newsletters\Repositories\CompanyAccountRepository;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppBatchByInstances implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    protected $mailingListId;
    protected $customerIds;
    protected $batchIndex;
    protected $companyId;

    public function __construct(int $mailingListId, array $customerIds, int $batchIndex = 0, ?int $companyId = null)
    {
        $this->mailingListId = $mailingListId;
        $this->customerIds = $customerIds;
        $this->batchIndex = $batchIndex;
        $this->companyId = $companyId;
        $this->onQueue('whatsapp-batch-instances');
    }

    public function handle(WhatsAppMailingService $whatsappService)
    {
        Log::info('ProcessWhatsAppBatchByInstances started', [
            'mailing_list_id' => $this->mailingListId,
            'customer_ids_count' => count($this->customerIds),
            'batch_index' => $this->batchIndex,
        ]);

        $mailingList = MailingList::with('whatsappInstances')->findOrFail($this->mailingListId);

        // Verify company access
        if ($this->companyId !== null && $mailingList->company_id !== $this->companyId) {
            Log::warning("Mailing list does not belong to company", [
                'mailing_list_id' => $this->mailingListId,
                'expected_company_id' => $this->companyId,
                'actual_company_id' => $mailingList->company_id,
            ]);
            return;
        }

        // Check account balance
        if ($mailingList->company_id) {
            $accountRepository = app(CompanyAccountRepository::class);
            $account = $accountRepository->getOrCreateForCompany($mailingList->company_id);
            if ($account->balance <= 0) {
                Log::warning("Account balance is insufficient, stopping mailing", [
                    'mailing_list_id' => $this->mailingListId,
                    'company_id' => $mailingList->company_id,
                    'balance' => $account->balance,
                ]);
                // Update mailing list status to paused
                $mailingList->update(['active' => false, 'status' => 'paused']);
                return;
            }
        }

        // Check for active and non-blocked instances
        $activeInstances = $mailingList->whatsappInstances()
            ->where('active', true)
            ->where('blocked', false)
            ->get();
        if (!$mailingList->active || $activeInstances->isEmpty()) {
            Log::warning("Mailing list is inactive or has no active WhatsApp instances", [
                'mailing_list_id' => $this->mailingListId
            ]);
            return;
        }

        // Проверка времени рассылки
        if (!$this->isWithinMailingHours($mailingList)) {
            Log::info("Batch processing postponed due to mailing hours", [
                'mailing_list_id' => $this->mailingListId,
                'current_time' => now()->format('H:i'),
            ]);

            if ($mailingList->mailing_hours_from) {
                $delay = $this->calculateDelayUntilNextMailingHour($mailingList);
                ProcessWhatsAppBatchByInstances::dispatch($this->mailingListId, $this->customerIds, $this->batchIndex, $this->companyId)
                    ->delay(now()->addSeconds($delay))
                    ->onQueue('whatsapp-batch-instances');
            }
            return;
        }

        // Get active and non-blocked instances, filtering by max_messages_per_instance limit
        $instances = $mailingList->whatsappInstances()
            ->where('active', true)
            ->where('blocked', false)
            ->get()
            ->filter(function ($instance) use ($mailingList) {
                // Check if instance has reached the limit
                if ($mailingList->max_messages_per_instance) {
                    return $instance->sending_message_count < $mailingList->max_messages_per_instance;
                }
                return true;
            });
        $instancesCount = $instances->count();
        
        if ($instancesCount === 0) {
            Log::warning("All instances are blocked or reached message limit", [
                'mailing_list_id' => $this->mailingListId
            ]);
            
            // Проверяем, есть ли вообще инстансы у рассылки
            $allInstances = $mailingList->whatsappInstances()->where('active', true)->get();
            
            // Если есть активные инстансы, но все заблокированы, переносим на 00:01
            if ($allInstances->isNotEmpty()) {
                $delayUntilReset = $this->calculateDelayUntilResetTime();
                
                Log::info("All instances blocked, rescheduling batch until reset time (00:01)", [
                    'mailing_list_id' => $this->mailingListId,
                    'delay_seconds' => $delayUntilReset,
                    'rescheduled_at' => now()->addSeconds($delayUntilReset)->toDateTimeString(),
                ]);
                
                ProcessWhatsAppBatchByInstances::dispatch($this->mailingListId, $this->customerIds, $this->batchIndex, $this->companyId)
                    ->delay(now()->addSeconds($delayUntilReset))
                    ->onQueue('whatsapp-batch-instances');
            }
            
            return;
        }
        $customersQuery = CustomerNumber::whereIn('id', $this->customerIds)
            ->where('sending', false)
            ->where('send_error', false);
        
        // Filter by company if provided
        if ($this->companyId !== null) {
            $customersQuery->where('company_id', $this->companyId);
        }
        
        $customers = $customersQuery->get();

        if ($customers->isEmpty()) {
            Log::info("No customers to process in batch", [
                'mailing_list_id' => $this->mailingListId,
                'batch_index' => $this->batchIndex,
            ]);
            return;
        }

        // Берем количество сообщений по числу инстансов
        $batchSize = min($instancesCount, $customers->count());
        $batchCustomers = $customers->take($batchSize);
        $remainingCustomers = $customers->skip($batchSize);

        Log::info('Processing batch by instances', [
            'mailing_list_id' => $this->mailingListId,
            'batch_size' => $batchSize,
            'instances_count' => $instancesCount,
            'batch_index' => $this->batchIndex,
            'remaining_customers' => $remainingCustomers->count(),
        ]);

        // Отправляем по одному сообщению на каждый инстанс одновременно
        $baseTime = now();
        $instanceIndex = 0;

        foreach ($batchCustomers as $customer) {
            // Проверка стоп-листа (с учетом company_id)
            $stopListQuery = \Webkul\Newsletters\Models\StopList::where('phone_number', $customer->phone_number);
            if ($this->companyId !== null) {
                $stopListQuery->where('company_id', $this->companyId);
            }
            if ($stopListQuery->exists()) {
                Log::warning('Number in stopList', [
                    'phone' => $customer->phone_number,
                    'customer_id' => $customer->id,
                ]);

                $customer->update([
                    'sending' => true,
                    'send_error' => true
                ]);


                $mailingListStats = $mailingList->with('customerNumbers')->withCount([
                    'customerNumbers as numbers_delivered' => function ($query) {
                        $query->where('sending', true)->orWhere('send_error', true);
                    },
                    'customerNumbers as numbers_viewed' => function ($query) {
                        $query->where('viewed', true);
                    },
                    'customerNumbers as incoming_messages_count' => function ($query) {
                        $query->where('incoming_message', true);
                    }
                ])->find($mailingList->id);

                $stats = [
                    'sent_count' => (int) $mailingListStats->numbers_delivered,
                    'incoming_count' => (int) $mailingListStats->incoming_messages_count,
                    'viewed_count' => (int) $mailingListStats->numbers_viewed,
                    'total_count' => (int) $mailingListStats->customerNumbers->count()
                ];

                broadcast(new MailingListStatsUpdated($mailingList->id, $stats));

//                broadcast(new WhatsAppMessageSent(
//                    $mailingList->id,
//                    $customer->id,
//                    null,
//                    ''
//                ));

                continue;
            }

            // Проверка rate limit
            if (!$whatsappService->checkRateLimit()) {
                Log::warning('Rate limit exceeded, rescheduling customer', [
                    'customer_id' => $customer->id,
                ]);
                // Переносим этого клиента в следующую партию
                $remainingCustomers->push($customer);
                continue;
            }

            // Выбираем инстанс по кругу (только незаблокированные и не превысившие лимит)
            $availableInstances = $instances->values();
            if ($availableInstances->isEmpty()) {
                Log::warning('No available instances for customer', [
                    'customer_id' => $customer->id,
                ]);
                $remainingCustomers->push($customer);
                continue;
            }
            
            $instance = $availableInstances->get($instanceIndex % $instancesCount);
            $instanceIndex++;

            // Генерируем случайное сообщение
            $randomMessage = $whatsappService->makeRandomMessage($mailingList->message_text);

            // Отправляем сообщение без задержки (все сообщения партии отправляются одновременно)
            SendWhatsAppMessageWithEvent::dispatch(
                $instance->id,
                $customer->id,
                $randomMessage,
                $this->mailingListId
            )
                ->onQueue('whatsapp-send');
        }

        // Если есть оставшиеся клиенты, создаем следующую партию с задержкой
        if ($remainingCustomers->isNotEmpty()) {
            $messageDelay = $this->calculateMessageDelay($mailingList);
            $nextBatchIndex = $this->batchIndex + 1;
            $remainingCustomerIds = $remainingCustomers->pluck('id')->toArray();

            Log::info('Scheduling next batch', [
                'mailing_list_id' => $this->mailingListId,
                'next_batch_index' => $nextBatchIndex,
                'remaining_customers_count' => count($remainingCustomerIds),
                'delay_seconds' => $messageDelay,
            ]);

            ProcessWhatsAppBatchByInstances::dispatch(
                $this->mailingListId,
                $remainingCustomerIds,
                $nextBatchIndex,
                $this->companyId
            )
                ->delay(now()->addSeconds($messageDelay))
                ->onQueue('whatsapp-batch-instances');
        } else {

            $mailingList->update([
                'status' => 'completed',
                'active' => false,
            ]);

            Log::info('All batches processed', [
                'mailing_list_id' => $this->mailingListId,
                'last_batch_index' => $this->batchIndex,
            ]);
        }
    }

    /**
     * Check if current time is within mailing hours.
     */
    protected function isWithinMailingHours($mailingList): bool
    {
        if (!$mailingList->mailing_hours_from) {
            return true;
        }

        $timezone = config('app.timezone', 'UTC');
        $checkTime = now()->setTimezone($timezone);

        $fromTime = $mailingList->mailing_hours_from;
        $toTime = $mailingList->mailing_hours_to;

        $fromMinutes = $this->timeToMinutes($fromTime);
        $currentMinutes = $checkTime->hour * 60 + $checkTime->minute;

        if (!$toTime) {
            return $currentMinutes >= $fromMinutes;
        }

        $toMinutes = $this->timeToMinutes($toTime);
        $spansMidnight = $toMinutes < $fromMinutes;

        if ($spansMidnight) {
            return $currentMinutes >= $fromMinutes || $currentMinutes <= $toMinutes;
        } else {
            return $currentMinutes >= $fromMinutes && $currentMinutes <= $toMinutes;
        }
    }

    /**
     * Calculate delay until next mailing hour starts.
     */
    protected function calculateDelayUntilNextMailingHour($mailingList): int
    {
        if (!$mailingList->mailing_hours_from) {
            return 0;
        }

        $timezone = config('app.timezone', 'UTC');
        $now = now()->setTimezone($timezone);
        $delay = 0;

        $fromTime = $mailingList->mailing_hours_from;
        $toTime = $mailingList->mailing_hours_to;

        if (!$toTime) {
            $hoursFromToday = $now->copy()->setTimeFromTimeString($fromTime);
            $secondsUntilFrom = $now->diffInSeconds($hoursFromToday, false);
            if ($secondsUntilFrom > 0) {
                $delay = $secondsUntilFrom;
            } else {
                $hoursFromTomorrow = $now->copy()->addDay()->setTimeFromTimeString($fromTime);
                $secondsUntilFrom = $now->diffInSeconds($hoursFromTomorrow, false);
                if ($secondsUntilFrom > 0) {
                    $delay = $secondsUntilFrom;
                }
            }
        } else {
            $fromMinutes = $this->timeToMinutes($fromTime);
            $toMinutes = $this->timeToMinutes($toTime);
            $currentMinutes = $now->hour * 60 + $now->minute;

            $spansMidnight = $toMinutes < $fromMinutes;

            if ($spansMidnight) {
                if (!($currentMinutes >= $fromMinutes || $currentMinutes <= $toMinutes)) {
                    $hoursFromToday = $now->copy()->setTimeFromTimeString($fromTime);
                    $secondsUntilFrom = $now->diffInSeconds($hoursFromToday, false);
                    if ($secondsUntilFrom > 0) {
                        $delay = $secondsUntilFrom;
                    }
                }
            } else {
                if ($currentMinutes < $fromMinutes) {
                    $hoursFromToday = $now->copy()->setTimeFromTimeString($fromTime);
                    $secondsUntilFrom = $now->diffInSeconds($hoursFromToday, false);
                    if ($secondsUntilFrom > 0) {
                        $delay = $secondsUntilFrom;
                    }
                } elseif ($currentMinutes > $toMinutes) {
                    $hoursFromTomorrow = $now->copy()->addDay()->setTimeFromTimeString($fromTime);
                    $secondsUntilFrom = $now->diffInSeconds($hoursFromTomorrow, false);
                    if ($secondsUntilFrom > 0) {
                        $delay = $secondsUntilFrom;
                    }
                }
            }
        }

        return (int) $delay;
    }

    /**
     * Вычисляет задержку между сообщениями на основе настроек mailing list
     * 
     * Логика:
     * - Если заполнено только message_delay_to (и не заполнено message_delay_from) - используется message_delay_to
     * - Если заполнены оба поля - случайное значение из интервала [message_delay_from, message_delay_to]
     * - Если заполнено только message_delay_from - используется message_delay_from
     */
    protected function calculateMessageDelay($mailingList): int
    {
        $delayFrom = $mailingList->message_delay_from;
        $delayTo = $mailingList->message_delay_to;
        
        // Если заполнено только message_delay_to
        if ($delayTo && !$delayFrom) {
            return (int) $delayTo;
        }
        
        // Если заполнены оба поля - случайное значение из интервала
        if ($delayFrom && $delayTo) {
            // Убеждаемся, что from <= to
            $min = min((int) $delayFrom, (int) $delayTo);
            $max = max((int) $delayFrom, (int) $delayTo);
            return rand($min, $max);
        }
        
        // Если заполнено только message_delay_from
        if ($delayFrom && !$delayTo) {
            return (int) $delayFrom;
        }
        
        // По умолчанию возвращаем 5 секунд
        return 5;
    }

    /**
     * Преобразует время в формате "H:i" в минуты от начала дня
     */
    protected function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);
        return (int) $hours * 60 + (int) $minutes;
    }

    /**
     * Вычисляет задержку до следующего времени сброса блокировок (00:01)
     * 
     * @return int Задержка в секундах
     */
    protected function calculateDelayUntilResetTime(): int
    {
        $timezone = config('app.timezone', 'UTC');
        $now = now()->setTimezone($timezone);
        
        // Время сброса блокировок: 00:01
        $resetTime = $now->copy()->setTime(0, 1, 0);
        
        // Если текущее время уже прошло 00:01 сегодня, переносим на завтра
        if ($now->greaterThanOrEqualTo($resetTime)) {
            $resetTime->addDay();
        }
        
        $delay = $now->diffInSeconds($resetTime, false);
        
        return max(0, (int) $delay);
    }
}
