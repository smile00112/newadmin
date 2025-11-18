<?php

namespace Webkul\Newsletters\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Newsletters\Models\MailingList;
use Webkul\Newsletters\Models\CustomerNumber;
use Webkul\Newsletters\Services\WhatsAppMailingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Webkul\Newsletters\Events\MailingListCompleted;
use Webkul\Admin\Services\FcmNotificationService;

class ProcessWhatsAppBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    protected $mailingListId;
    protected $customerIds;

    public function __construct(int $mailingListId, array $customerIds)
    {
        $this->mailingListId = $mailingListId;
        $this->customerIds = $customerIds;
        $this->onQueue('whatsapp-batch');
    }

    public function handle(WhatsAppMailingService $whatsappService)
    {
        Log::info("Start ProcessWhatsAppBatch", [
            'message' => $this->mailingListId,
        ]);

        $mailingList = MailingList::with('whatsappInstances')->findOrFail($this->mailingListId);
        $customers = CustomerNumber::whereIn('id', $this->customerIds)->where('sending', false)->get();



//        if ($customers->isEmpty()) {
//            Log::info("Mailing list completed: no more customers to process", [
//                'mailing_list_id' => $this->mailingListId
//            ]);
//
//            $mailingList->update([
//                'status' => 'completed',
//              // 'sent_at' => now(),
//            ]);
//
//            try {
//                // Broadcast completion event
//                broadcast(new MailingListCompleted($mailingList));
//            } catch (\Exception $e) {
//                Log::error('Failed to broadcast MailingListCompleted', [
//                    'mailing_list_id' => $this->mailingListId,
//                    'error' => $e->getMessage(),
//                ]);
//            }
//
//            try {
//                // Send FCM notification to all admins
//                $fcm = app(FcmNotificationService::class);
//                if ($fcm && $fcm->isInitialized()) {
//                    $title = 'Рассылка завершена';
//                    $body = 'Рассылка #' . $this->mailingListId . ' завершена';
//                    $fcm->sendToAllAdmins($title, $body, [
//                        'type' => 'mailing.completed',
//                        'mailing_list_id' => (string) $this->mailingListId,
//                    ]);
//                }
//            } catch (\Exception $e) {
//                Log::error('Failed to send FCM for MailingListCompleted', [
//                    'mailing_list_id' => $this->mailingListId,
//                    'error' => $e->getMessage(),
//                ]);
//            }
//
//            return;
//        }

        // Check if mailing hours allow sending
        if (!$this->isWithinMailingHours($mailingList)) {
            Log::info("Batch processing postponed due to mailing hours", [
                'mailing_list_id' => $this->mailingListId,
                'current_time' => now()->format('H:i'),
                'mailing_hours_from' => $mailingList->mailing_hours_from,
                'mailing_hours_to' => $mailingList->mailing_hours_to,
            ]);

            // Reschedule batch for next day if outside mailing hours
            if ($mailingList->mailing_hours_from) {
                $delay = $this->calculateDelayUntilNextMailingHour($mailingList);
                ProcessWhatsAppBatch::dispatch($this->mailingListId, $this->customerIds)
                    ->delay(now()->addSeconds($delay))
                    ->onQueue('whatsapp-batch');
            }
            return;
        }

        // Get all active instances for round-robin selection
        $instances = $mailingList->whatsappInstances()->where('active', true)->get();
        if ($instances->isEmpty()) {
            Log::error("No active WhatsApp instance available for mailing list", [
                'mailing_list_id' => $this->mailingListId
            ]);
            return;
        }

        // Получаем максимальную задержку для проверки времени рассылки
        $maxMessageDelay = $this->calculateMaxMessageDelay($mailingList);
        $messageIndex = 0;
        $messageDelayIndex = 0;
        $instanceCounter = 0; // Counter for round-robin instance selection
        $instancesCount = $instances->count();

        // Фиксируем базовое время для всех сообщений в этом батче
        $baseTime = now();
        $totalDelay = 0; // Накопительная задержка для расчета времени отправки

        foreach ($customers as $customer) {
            //проверка стоит ли номер в стопе
            if(\Webkul\Newsletters\Models\StopList::where('phone_number', $customer->phone_number)->exists()){
                Log::error('Number in stopList', [
                    'phone' => $customer->phone_number
                ]);

                $customer->update([
                    'send_error' => true
                ]);
                continue;
            }

            // Check if we're still within mailing hours before sending each message
            // Используем максимальную задержку для проверки, чтобы не превысить время рассылки
            if ($messageIndex > 0 && !$this->isWithinMailingHours($mailingList, $totalDelay + $maxMessageDelay)) {
                // If sending this message would exceed mailing hours, schedule remaining for tomorrow
                $remainingCustomers = $customers->slice($messageIndex)->pluck('id')->toArray();
                if (!empty($remainingCustomers)) {
                    $delay = $this->calculateDelayUntilNextMailingHour($mailingList);
                    ProcessWhatsAppBatch::dispatch($this->mailingListId, $remainingCustomers)
                        ->delay($baseTime->copy()->addSeconds($delay))
                        ->onQueue('whatsapp-batch');

                    Log::info("Remaining messages scheduled for next mailing hour", [
                        'mailing_list_id' => $this->mailingListId,
                        'remaining_count' => count($remainingCustomers),
                        'scheduled_at' => $baseTime->copy()->addSeconds($delay)->toDateTimeString(),
                    ]);
                }
                break;
            }

            // Check rate limit before each message
            if (!$whatsappService->checkRateLimit()) {
                // If rate limit exceeded, delay the remaining messages
                $rateLimitDelay = $this->calculateMessageDelay($mailingList);
                ProcessWhatsAppBatch::dispatch($this->mailingListId, [$customer->id])
                    ->delay($baseTime->copy()->addSeconds($rateLimitDelay))
                    ->onQueue('whatsapp-batch');
                continue;
            }

            // Select instance using round-robin (cyclic selection)
            // Instance counter increases only when we actually send a message
            $instanceIndex = $instanceCounter % $instancesCount;
            $instance = $instances->get($instanceIndex);
            $instanceCounter++; // Increment for next message

            // Вычисляем задержку для текущего сообщения
            $currentMessageDelay = $this->calculateMessageDelay($mailingList);
            $totalDelay += $currentMessageDelay;

            Log::info("Start Sending message to customer", [
                'customer_id' => $customer->id,
                'mailing_list_id' => $this->mailingListId,
                'instance_id' => $instance->id,
                'instance_index' => $instanceIndex,
                'messageIndex' => $messageIndex,
                'messageDelayIndex' => $messageDelayIndex,
                'currentMessageDelay' => $currentMessageDelay,
                'totalDelay' => $totalDelay,
                'now' => now()->format("Y-m-d H:i:s"),
                'scheduled_at' => $baseTime->copy()->addSeconds($totalDelay)->format("Y-m-d H:i:s"),
            ]);

            // Send individual message with delay based on calculated delay
            $randomMessage = $whatsappService->makeRandomMessage($mailingList->message_text);
            SendWhatsAppMessage::dispatch(
                $instance->id,
                $customer->id,
                $randomMessage
            )
                ->delay($baseTime->copy()->addSeconds($totalDelay))
                ->onQueue('whatsapp-send');

            $messageIndex++;
            //увеличиваем $messageDelayIndex только после отправки по сообщению всем инстансам
            //это надо чтобы задержка была только между сообщениями инстанса
            //между сообщениями разных инстансов, задержки быть не должно
            if( $instanceIndex === 0 && $instanceCounter > 1)  $messageDelayIndex++;
        }
    }

    /**
     * Check if current time is within mailing hours.
     */
    protected function isWithinMailingHours($mailingList, int $secondsFromNow = 0): bool
    {
        if (!$mailingList->mailing_hours_from) {
            return true; // No time restriction
        }

        // Явно используем часовой пояс из конфигурации
        $timezone = config('app.timezone', 'UTC');
        $checkTime = now()->setTimezone($timezone)->addSeconds($secondsFromNow);

        $fromTime = $mailingList->mailing_hours_from;
        $toTime = $mailingList->mailing_hours_to;

        // Преобразуем время в минуты для корректного сравнения
        $fromMinutes = $this->timeToMinutes($fromTime);
        $currentMinutes = $checkTime->hour * 60 + $checkTime->minute;

        if (!$toTime) {
            // Если toTime не указан, проверяем только fromTime
            // Можно отправлять, если текущее время >= fromTime
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

    /**
     * Calculate delay until next mailing hour starts.
     */
    protected function calculateDelayUntilNextMailingHour($mailingList): int
    {
        if (!$mailingList->mailing_hours_from) {
            return 0;
        }

        // Явно используем часовой пояс из конфигурации
        $timezone = config('app.timezone', 'UTC');
        $now = now()->setTimezone($timezone);
        $delay = 0;

        $fromTime = $mailingList->mailing_hours_from;
        $toTime = $mailingList->mailing_hours_to;

        if (!$toTime) {
            // Если toTime не указан, считаем что диапазон не переходит через полночь
            $hoursFromToday = $now->copy()->setTimeFromTimeString($fromTime);
            $secondsUntilFrom = $now->diffInSeconds($hoursFromToday, false);
            if ($secondsUntilFrom > 0) {
                $delay = $secondsUntilFrom;
            } else {
                // Если время уже прошло сегодня, переносим на завтра
                $hoursFromTomorrow = $now->copy()->addDay()->setTimeFromTimeString($fromTime);
                $secondsUntilFrom = $now->diffInSeconds($hoursFromTomorrow, false);
                if ($secondsUntilFrom > 0) {
                    $delay = $secondsUntilFrom;
                }
            }
        } else {
            // Преобразуем время в минуты для корректного сравнения
            $fromMinutes = $this->timeToMinutes($fromTime);
            $toMinutes = $this->timeToMinutes($toTime);
            $currentMinutes = $now->hour * 60 + $now->minute;

            // Проверяем, переходит ли диапазон через полночь
            $spansMidnight = $toMinutes < $fromMinutes;

            if ($spansMidnight) {
                // Диапазон переходит через полночь (например, 10:00 - 03:00)
                // Если мы вне диапазона, вычисляем задержку до начала
                if (!($currentMinutes >= $fromMinutes || $currentMinutes <= $toMinutes)) {
                    // Текущее время между окончанием и началом (например, 04:00 - 09:59)
                    // Устанавливаем задержку до начала времени рассылки
                    $hoursFromToday = $now->copy()->setTimeFromTimeString($fromTime);
                    $secondsUntilFrom = $now->diffInSeconds($hoursFromToday, false);
                    if ($secondsUntilFrom > 0) {
                        $delay = $secondsUntilFrom;
                    }
                }
            } else {
                // Обычный диапазон в пределах одного дня (например, 10:00 - 18:00)
                if ($currentMinutes < $fromMinutes) {
                    // Вычисляем секунды до начала времени рассылки
                    $hoursFromToday = $now->copy()->setTimeFromTimeString($fromTime);
                    $secondsUntilFrom = $now->diffInSeconds($hoursFromToday, false);
                    if ($secondsUntilFrom > 0) {
                        $delay = $secondsUntilFrom;
                    }
                } elseif ($currentMinutes > $toMinutes) {
                    // Если текущее время больше времени окончания - переносим на завтра
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
        
        // По умолчанию возвращаем 0 секунд (как было раньше)
        return 0;
    }

    /**
     * Вычисляет максимальную задержку для проверки времени рассылки
     * Используется для определения, не превысит ли отправка время рассылки
     */
    protected function calculateMaxMessageDelay($mailingList): int
    {
        $delayFrom = $mailingList->message_delay_from;
        $delayTo = $mailingList->message_delay_to;
        
        // Если заполнено только message_delay_to
        if ($delayTo && !$delayFrom) {
            return (int) $delayTo;
        }
        
        // Если заполнены оба поля - берем максимальное значение
        if ($delayFrom && $delayTo) {
            return max((int) $delayFrom, (int) $delayTo);
        }
        
        // Если заполнено только message_delay_from
        if ($delayFrom && !$delayTo) {
            return (int) $delayFrom;
        }
        
        // По умолчанию возвращаем 0 секунд
        return 0;
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
