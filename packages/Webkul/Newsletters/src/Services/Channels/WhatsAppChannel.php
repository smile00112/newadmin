<?php

namespace Webkul\Newsletters\Services\Channels;

use Webkul\Newsletters\Contracts\MailingChannelInterface;
use Webkul\Newsletters\Models\MailingList;
use Webkul\Newsletters\Models\VacapInstance;
use Webkul\Newsletters\Models\CustomerNumber;
use Webkul\Newsletters\Services\GreenAPIService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class WhatsAppChannel implements MailingChannelInterface
{
    public function getChannelType(): string
    {
        return 'whatsapp';
    }

    public function sendMessage(object $instance, CustomerNumber $customer, string $message): ?string
    {
        if (!$instance instanceof VacapInstance) {
            Log::error('WhatsAppChannel: Invalid instance type', ['instance' => get_class($instance)]);
            return null;
        }

        $phoneNumber = $this->getRecipientIdentifier($customer);
        if (!$phoneNumber) {
            Log::error('WhatsAppChannel: Customer has no phone number', ['customer_id' => $customer->id]);
            return null;
        }

        try {
            $greenApiService = new GreenAPIService($instance->link_name, $instance->login, $instance->password);
            $response = $greenApiService->sendMessage($phoneNumber . '@c.us', $message);

            Log::info("WhatsApp API sendMessage response", ['response' => $response]);

            if (isset($response['idMessage'])) {
                Log::info("WhatsApp message sent successfully", [
                    'instance_id' => $instance->id,
                    'phone' => $phoneNumber,
                    'response' => $response
                ]);

                return $response['idMessage'];
            }

            Log::error("WhatsApp API error", [
                'instance_id' => $instance->id,
                'phone' => $phoneNumber,
                'response' => $response
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error("WhatsApp sending failed", [
                'instance_id' => $instance->id,
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function validateRecipient(CustomerNumber $customer): bool
    {
        $phone = $this->getRecipientIdentifier($customer);
        return !empty($phone) && preg_match('/^\d{10,15}$/', $phone);
    }

    public function getActiveInstances(MailingList $mailingList): Collection
    {
        return $mailingList->whatsappInstances()
            ->where('active', true)
            ->where('blocked', false)
            ->get();
    }

    public function getRecipientIdentifier(CustomerNumber $customer): ?string
    {
        return $customer->phone_number ?? null;
    }

    /**
     * Send file by URL via WhatsApp.
     */
    public function sendFileByUrl(
        VacapInstance $instance,
        string $phoneNumber,
        string $urlFile,
        string $fileName,
        ?string $caption = null
    ): ?string {
        try {
            $greenApiService = new GreenAPIService($instance->link_name, $instance->login, $instance->password);
            $response = $greenApiService->sendFileByUrl(
                $phoneNumber . '@c.us',
                $urlFile,
                $fileName,
                $caption
            );

            if (isset($response['idMessage'])) {
                return $response['idMessage'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error("WhatsApp file sending failed", ['error' => $e->getMessage()]);
            return null;
        }
    }
}



