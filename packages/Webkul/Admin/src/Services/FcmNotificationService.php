<?php

namespace Webkul\Admin\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FcmNotificationService
{
    protected $messaging;

    public function __construct()
    {
        try {
            // Инициализация Firebase с credentials
            $factory = (new Factory)->withServiceAccount(base_path('firebase-credentials.json'));
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Firebase initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Отправка уведомления одному пользователю
     *
     * @param string $token FCM token
     * @param string $title Заголовок уведомления
     * @param string $body Текст уведомления
     * @param array $data Дополнительные данные
     * @return bool
     */
    public function sendToDevice($token, $title, $body, $data = [])
    {
        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($message);

            Log::info('FCM notification sent successfully', [
                'token' => substr($token, 0, 20) . '...',
                'title' => $title
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('FCM notification failed', [
                'error' => $e->getMessage(),
                'token' => substr($token, 0, 20) . '...',
            ]);

            return false;
        }
    }

    /**
     * Отправка уведомления нескольким пользователям
     *
     * @param array $tokens Массив FCM токенов
     * @param string $title Заголовок уведомления
     * @param string $body Текст уведомления
     * @param array $data Дополнительные данные
     * @return array
     */
    public function sendToMultipleDevices(array $tokens, $title, $body, $data = [])
    {
        $results = [];

        foreach ($tokens as $token) {
            $results[$token] = $this->sendToDevice($token, $title, $body, $data);
        }

        return $results;
    }

    /**
     * Отправка уведомления всем администраторам
     *
     * @param string $title Заголовок уведомления
     * @param string $body Текст уведомления
     * @param array $data Дополнительные данные
     * @return array
     */
    public function sendToAllAdmins($title, $body, $data = [])
    {
        $admins = \Webkul\User\Models\Admin::whereNotNull('fcm_token')->get();
        $tokens = $admins->pluck('fcm_token')->toArray();

        if (empty($tokens)) {
            Log::warning('No FCM tokens found for admins');
            return [];
        }

        return $this->sendToMultipleDevices($tokens, $title, $body, $data);
    }
}

