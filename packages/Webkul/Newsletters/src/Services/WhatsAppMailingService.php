<?php

namespace Webkul\Newsletters\Services;

use Webkul\Newsletters\Models\MailingList;
use Webkul\Newsletters\Models\VacapInstance;
use Webkul\Newsletters\Models\CustomerNumber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Webkul\Newsletters\Services\GreenAPIService;

class WhatsAppMailingService
{
    /**
     * Send WhatsApp message using VacapInstance
     */
    public function sendMessage(VacapInstance $instance, string $phoneNumber, string $message): bool
    {

        try {

            $greenApiService = new GreenAPIService($instance->link_name, $instance->login, $instance->password);
            //'chatId' => $phone.'@c.us',
            $response = $greenApiService->sendMessage($phoneNumber.'@c.us', $message);
dd($response);
            if ($response['idMessage']) {
                Log::info("WhatsApp message sent successfully", [
                    'instance_id' => $instance->id,
                    'phone' => $phoneNumber,
                    'response' => $response
                ]);
                return true;
            }

            Log::error("WhatsApp API error", [
                'instance_id' => $instance->id,
                'phone' => $phoneNumber,
                'response' => $response
            ]);

            return false;
        } catch (\Exception $e) {
            echo $e->getMessage();
            Log::error("WhatsApp sending failed", [
                'instance_id' => $instance->id,
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get random VacapInstance from mailing list
     */
    public function getRandomInstance(MailingList $mailingList): ?VacapInstance
    {
        return $mailingList->whatsappInstances()
            ->inRandomOrder()
            ->first();
    }

    /**
     * Check rate limit (40 messages per second)
     */
    public function checkRateLimit(): bool
    {
        $key = 'whatsapp_rate_limit:' . now()->format('Y-m-d-H-i-s');
        $current = Redis::get($key) ?? 0;

        if ($current >= 40) {
            return false;
        }

        Redis::incr($key);
        Redis::expire($key, 1); // Expire after 1 second

        return true;
    }

    public function makeRandomMessage($text): string
    {
        return $result = preg_replace_callback('/\{([^}]+)\}/', function($matches) {
            $options = explode('|', $matches[1]);
            return $options[array_rand($options)];
        }, $text);

    }
}
