<?php

namespace Webkul\Newsletters\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Newsletters\Models\MailingList;
use Webkul\Newsletters\Services\WhatsAppMailingService;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppMailingList implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3;

    protected $mailingListId;

    public function __construct(int $mailingListId)
    {
        $this->mailingListId = $mailingListId;
        $this->onQueue('whatsapp-mailing');
    }

    public function handle(WhatsAppMailingService $whatsappService)
    {
        Log::info('ProcessWhatsAppMailingList INFO', [
            'mailing_list_id' => $this->mailingListId,
            'all_lists' => MailingList::all(),
        ]);

        $mailingList = MailingList::with(['whatsappInstances'])
            ->where('active', true)
            ->findOrFail($this->mailingListId);

        // Check for active instances
        $activeInstances = $mailingList->whatsappInstances()
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
        if (!$mailingList->active || $activeInstances->isEmpty()) {
            Log::warning("Mailing list is inactive or has no active WhatsApp instances", [
                'mailing_list_id' => $this->mailingListId
            ]);
            
            // Проверяем, есть ли вообще инстансы у рассылки
            $allInstances = $mailingList->whatsappInstances()->where('active', true)->get();
            
            // Если есть активные инстансы, но все заблокированы, переносим на 00:01
            if ($allInstances->isNotEmpty() && $mailingList->active) {
                $delayUntilReset = $this->calculateDelayUntilResetTime();
                
                Log::info("All instances blocked, rescheduling mailing list until reset time (00:01)", [
                    'mailing_list_id' => $this->mailingListId,
                    'delay_seconds' => $delayUntilReset,
                    'rescheduled_at' => now()->addSeconds($delayUntilReset)->toDateTimeString(),
                ]);
                
                ProcessWhatsAppMailingList::dispatch($this->mailingListId)
                    ->delay(now()->addSeconds($delayUntilReset));
            }
            
            return;
        }

        // Check if we should start mailing based on time constraints
        if (!$this->shouldStartMailing($mailingList)) {
            // Reschedule job if time constraints not met
            $delay = $this->calculateDelayForMailing($mailingList);
            if ($delay > 0) {
                Log::info("Mailing list rescheduled due to time constraints", [
                    'mailing_list_id' => $this->mailingListId,
                    'delay_seconds' => $delay,
                    'rescheduled_at' => now()->addSeconds($delay)->toDateTimeString(),
                ]);

                ProcessWhatsAppMailingList::dispatch($this->mailingListId)
                    ->delay(now()->addSeconds($delay));
                return;
            }
        }

        // Вместо старой логики с ProcessWhatsAppBatch, используем новую:
        $mailingList->customerNumbers()
            ->where('sending', false)
            ->where('send_error', false)
            ->chunk(100, function ($customers) use ($mailingList) {
                $customerIds = $customers->pluck('id')->toArray();
                
                // Используем новый job для обработки по инстансам
                ProcessWhatsAppBatchByInstances::dispatch($mailingList->id, $customerIds, 0)
                    ->onQueue('whatsapp-batch-instances');
            });
    }

    /**
     * Check if mailing should start based on time constraints.
     */
    protected function shouldStartMailing($mailingList): bool
    {
        // Явно используем часовой пояс из конфигурации
        $timezone = config('app.timezone', 'UTC');
        $now = now()->setTimezone($timezone);
        //TODO - вынести в отдельный сервис
        // Check start_at
        if ($mailingList->start_at) {
            $startAt = \Carbon\Carbon::parse($mailingList->start_at);
            // Убеждаемся, что start_at в правильном часовом поясе
            if ($startAt->timezone->getName() !== $timezone) {
                $startAt = $startAt->setTimezone($timezone);
            }
            if ($startAt->isFuture()) {
                return false;
            }
        }

        // Check mailing_hours_from and mailing_hours_to
        if ($mailingList->mailing_hours_from) {
            $fromTime = $mailingList->mailing_hours_from;
            $toTime = $mailingList->mailing_hours_to;

            // Преобразуем время в минуты для корректного сравнения
            $fromMinutes = $this->timeToMinutes($fromTime);
            $currentMinutes = $now->hour * 60 + $now->minute;

            if (!$toTime) {
                // Если toTime не указан, проверяем только fromTime
                // Можно начинать, если текущее время >= fromTime
                return $currentMinutes >= $fromMinutes;
            }

            // Если toTime указан, проверяем диапазон
            $toMinutes = $this->timeToMinutes($toTime);

            // Проверяем, переходит ли диапазон через полночь
            $spansMidnight = $toMinutes < $fromMinutes;

            if ($spansMidnight) {
                // Диапазон переходит через полночь (например, 10:00 - 03:00)
                // Мы в диапазоне, если: currentMinutes >= fromMinutes ИЛИ currentMinutes <= toMinutes
                return $currentMinutes >= $fromMinutes || $currentMinutes <= $toMinutes;
            } else {
                // Обычный диапазон в пределах одного дня (например, 10:00 - 18:00)
                // Мы в диапазоне, если: currentMinutes >= fromMinutes И currentMinutes <= toMinutes
                return $currentMinutes >= $fromMinutes && $currentMinutes <= $toMinutes;
            }
        }

        return true;
    }

    /**
     * Calculate delay in seconds if mailing should be postponed.
     */
    protected function calculateDelayForMailing($mailingList): int
    {
        //TODO - вынести в отдельный сервис

        // Явно используем часовой пояс из конфигурации
        $timezone = config('app.timezone', 'UTC');
        $now = now()->setTimezone($timezone);
        $delay = 0;

        // Check start_at (datetime)
        if ($mailingList->start_at) {
            $startAt = \Carbon\Carbon::parse($mailingList->start_at);
            // Убеждаемся, что start_at в правильном часовом поясе
            if ($startAt->timezone->getName() !== $timezone) {
                $startAt = $startAt->setTimezone($timezone);
            }
            if ($startAt->isFuture()) {
                $secondsUntilStart = $now->diffInSeconds($startAt, false);
                if ($secondsUntilStart > 0) {
                    $delay = max($delay, $secondsUntilStart);
                }
            }
        }

        // Check mailing_hours_from (time)
        if ($mailingList->mailing_hours_from) {
            $fromTime = $mailingList->mailing_hours_from;
            $toTime = $mailingList->mailing_hours_to;

            if (!$toTime) {
                // Если toTime не указан, считаем что диапазон не переходит через полночь
                $hoursFromToday = $now->copy()->setTimeFromTimeString($fromTime);
                $secondsUntilFrom = $now->diffInSeconds($hoursFromToday, false);
                if ($secondsUntilFrom > 0) {
                    $delay = max($delay, $secondsUntilFrom);
                }
            } else {
                // Преобразуем время в минуты для корректного сравнения
                $fromMinutes = $this->timeToMinutes($fromTime);
                $toMinutes = $this->timeToMinutes($toTime);
                // Используем час и минуту из объекта $now, который уже в правильном часовом поясе
                $currentMinutes = $now->hour * 60 + $now->minute;

                // Проверяем, переходит ли диапазон через полночь
                $spansMidnight = $toMinutes < $fromMinutes;

                if ($spansMidnight) {
                    // Диапазон переходит через полночь (например, 10:00 - 03:00)
                    // Мы в диапазоне, если: currentMinutes >= fromMinutes ИЛИ currentMinutes <= toMinutes
                    if ($currentMinutes >= $fromMinutes || $currentMinutes <= $toMinutes) {
                        // Текущее время в диапазоне - можно начинать сразу (delay = 0)
                        $delay = 0;
                    } else {
                        // Текущее время между окончанием и началом (например, 04:00 - 09:59)
                        // Устанавливаем задержку до начала времени рассылки
                        $hoursFromToday = $now->copy()->setTimeFromTimeString($fromTime);
                        $secondsUntilFrom = $now->diffInSeconds($hoursFromToday, false);
                        if ($secondsUntilFrom > 0) {
                            $delay = max($delay, $secondsUntilFrom);
                        }
                    }
                } else {
                    // Обычный диапазон в пределах одного дня (например, 10:00 - 18:00)
                    if ($currentMinutes < $fromMinutes) {
                        // Вычисляем секунды до начала времени рассылки
                        $hoursFromToday = $now->copy()->setTimeFromTimeString($fromTime);
                        $secondsUntilFrom = $now->diffInSeconds($hoursFromToday, false);
                        if ($secondsUntilFrom > 0) {
                            $delay = max($delay, $secondsUntilFrom);
                        }
                    } elseif ($currentMinutes > $toMinutes) {
                        // Если текущее время больше времени окончания - переносим на завтра
                        $hoursFromTomorrow = $now->copy()->addDay()->setTimeFromTimeString($fromTime);
                        $secondsUntilFrom = $now->diffInSeconds($hoursFromTomorrow, false);
                        if ($secondsUntilFrom > 0) {
                            $delay = max($delay, $secondsUntilFrom);
                        }
                    }
                    // Если текущее время в диапазоне mailing_hours_from - mailing_hours_to, delay = 0 (можно начинать)
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
