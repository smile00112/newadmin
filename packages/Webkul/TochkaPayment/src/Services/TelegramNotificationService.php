<?php

namespace Webkul\TochkaPayment\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\TochkaPayment\Models\TochkaPaymentHistory;
use Webkul\TochkaPayment\Models\TochkaPaymentSettingsProxy;

class TelegramNotificationService
{
    /**
     * Telegram Bot API base URL.
     */
    protected const TELEGRAM_API_URL = 'https://api.telegram.org/bot';

    /**
     * Send payment notification to Telegram.
     *
     * @param  \Webkul\TochkaPayment\Models\TochkaPaymentHistory  $payment
     * @param  bool|null  $isSuccess  null = auto-detect from status, true = success, false = failed
     * @return bool
     */
    public function sendPaymentNotification(TochkaPaymentHistory $payment, ?bool $isSuccess = null): bool
    {
        // Auto-detect success status if not provided
        if ($isSuccess === null) {
            $isSuccess = $payment->status === TochkaPaymentHistory::STATUS_PAID;
        }

        // Get company settings
        $settings = TochkaPaymentSettingsProxy::forCompany($payment->company_id)->first();

        if (!$settings || !$settings->telegram_bot_token || !$settings->telegram_chat_id) {
            Log::debug('Tochka Payment: Telegram notification skipped - settings not configured', [
                'payment_id' => $payment->id,
                'company_id' => $payment->company_id,
            ]);
            return false;
        }

        try {
            $message = $this->formatPaymentMessage($payment, $isSuccess);

            $response = Http::timeout(10)->post(
                self::TELEGRAM_API_URL . $settings->telegram_bot_token . '/sendMessage',
                [
                    'chat_id' => $settings->telegram_chat_id,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]
            );

            if ($response->successful()) {
                Log::info('Tochka Payment: Telegram notification sent successfully', [
                    'payment_id' => $payment->id,
                    'company_id' => $payment->company_id,
                ]);
                return true;
            } else {
                Log::error('Tochka Payment: Telegram notification failed', [
                    'payment_id' => $payment->id,
                    'company_id' => $payment->company_id,
                    'response' => $response->body(),
                    'status' => $response->status(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Tochka Payment: Telegram notification exception', [
                'payment_id' => $payment->id,
                'company_id' => $payment->company_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Format payment message for Telegram.
     *
     * @param  \Webkul\TochkaPayment\Models\TochkaPaymentHistory  $payment
     * @param  bool  $isSuccess
     * @return string
     */
    protected function formatPaymentMessage(TochkaPaymentHistory $payment, bool $isSuccess): string
    {
        $amount = number_format($payment->amount, 2, '.', ' ') . ' ₽';
        $date = $payment->created_at->format('d.m.Y H:i');
        $statusText = $this->getStatusText($payment->status);

        if ($isSuccess) {
            $message = "✅ <b>Платёж успешно выполнен</b>\n\n";
        } else {
            $message = "❌ <b>Платёж не выполнен</b>\n\n";
        }

        $message .= "📋 Номер заказа: <b>{$payment->order_id}</b>\n";
        
        if ($payment->external_order_id) {
            $message .= "🔖 Внешний номер: {$payment->external_order_id}\n";
        }
        
        $message .= "💵 Сумма: <b>{$amount}</b>\n";
        $message .= "📊 Статус: <b>{$statusText}</b>\n";
        $message .= "📅 Дата: {$date}\n\n";
        
        if ($payment->client_name || $payment->client_email || $payment->client_phone) {
            $message .= "<b>Данные клиента:</b>\n";
            
            if ($payment->client_name) {
                $message .= "👤 Имя: {$payment->client_name}\n";
            }
            
            if ($payment->client_email) {
                $message .= "📧 Email: {$payment->client_email}\n";
            }
            
            if ($payment->client_phone) {
                $message .= "📱 Телефон: {$payment->client_phone}\n";
            }
        }

        if ($payment->operation_id) {
            $message .= "\n🆔 Operation ID: {$payment->operation_id}\n";
        }

        return $message;
    }

    /**
     * Get status text in Russian.
     *
     * @param  string  $status
     * @return string
     */
    protected function getStatusText(string $status): string
    {
        $statuses = [
            TochkaPaymentHistory::STATUS_PENDING => 'Ожидает',
            TochkaPaymentHistory::STATUS_PAID => 'Оплачен',
            TochkaPaymentHistory::STATUS_FAILED => 'Ошибка',
            TochkaPaymentHistory::STATUS_CANCELLED => 'Отменён',
        ];

        return $statuses[$status] ?? $status;
    }
}
