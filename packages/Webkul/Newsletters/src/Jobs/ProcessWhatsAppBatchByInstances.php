<?php

namespace Webkul\Newsletters\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Newsletters\Events\WhatsAppMessageSent;
use Webkul\Newsletters\Models\MailingList;
use Webkul\Newsletters\Models\CustomerNumber;
use Webkul\Newsletters\Services\WhatsAppMailingService;
use Webkul\Newsletters\Jobs\SendWhatsAppMessageWithEvent;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppBatchByInstances implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    protected $mailingListId;
    protected $customerIds;
    protected $batchIndex;

    public function __construct(int $mailingListId, array $customerIds, int $batchIndex = 0)
    {
        $this->mailingListId = $mailingListId;
        $this->customerIds = $customerIds;
        $this->batchIndex = $batchIndex;
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

        if (!$mailingList->active || $mailingList->whatsappInstances->isEmpty()) {
            Log::warning("Mailing list is inactive or has no WhatsApp instances", [
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
                ProcessWhatsAppBatchByInstances::dispatch($this->mailingListId, $this->customerIds, $this->batchIndex)
                    ->delay(now()->addSeconds($delay))
                    ->onQueue('whatsapp-batch-instances');
            }
            return;
        }

        $instances = $mailingList->whatsappInstances;
        $instancesCount = $instances->count();
        $customers = CustomerNumber::whereIn('id', $this->customerIds)
            ->where('sending', false)
            ->where('send_error', false)
            ->get();

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
            // Проверка стоп-листа
            if (\Webkul\Newsletters\Models\StopList::where('phone_number', $customer->phone_number)->exists()) {
                Log::warning('Number in stopList', [
                    'phone' => $customer->phone_number,
                    'customer_id' => $customer->id,
                ]);

                $customer->update(['send_error' => true]);

                broadcast(new WhatsAppMessageSent(
                    $mailingList->id,
                    $customer->id,
                    null

                ));

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

            // Выбираем инстанс по кругу
            $instance = $instances->get($instanceIndex % $instancesCount);
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
            $messageDelay = $mailingList->message_delay ?? 5;
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
                $nextBatchIndex
            )
                ->delay(now()->addSeconds($messageDelay))
                ->onQueue('whatsapp-batch-instances');
        } else {
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
     * Преобразует время в формате "H:i" в минуты от начала дня
     */
    protected function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);
        return (int) $hours * 60 + (int) $minutes;
    }
}
