<?php

namespace Webkul\Newsletters\Services\Channels;

use Webkul\Newsletters\Contracts\MailingChannelInterface;
use Webkul\Newsletters\Models\MailingList;
use Webkul\Newsletters\Models\MailInstance;
use Webkul\Newsletters\Models\CustomerNumber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

class EmailChannel implements MailingChannelInterface
{
    public function getChannelType(): string
    {
        return 'email';
    }

    public function sendMessage(object $instance, CustomerNumber $customer, string $message): ?string
    {
        if (!$instance instanceof MailInstance) {
            Log::error('EmailChannel: Invalid instance type', ['instance' => get_class($instance)]);
            return null;
        }

        $email = $this->getRecipientIdentifier($customer);
        if (!$email) {
            Log::error('EmailChannel: Customer has no email', ['customer_id' => $customer->id]);
            return null;
        }

        try {
            $transport = new EsmtpTransport(
                $instance->host,
                $instance->port,
                $instance->encryption === 'none' ? false : true
            );
            $transport->setUsername($instance->username);
            $transport->setPassword($instance->password);

            $mailer = new Mailer($transport);

            $emailMessage = (new Email())
                ->from($instance->from_name 
                    ? "{$instance->from_name} <{$instance->from_email}>" 
                    : $instance->from_email)
                ->to($email)
                ->subject($this->extractSubject($message))
                ->html($message);

            $mailer->send($emailMessage);

            $messageId = uniqid('email_', true);

            Log::info('Email sent successfully', [
                'instance_id' => $instance->id,
                'customer_id' => $customer->id,
                'email' => $email,
                'message_id' => $messageId,
            ]);

            return $messageId;
        } catch (\Exception $e) {
            Log::error('Email sending failed', [
                'instance_id' => $instance->id,
                'customer_id' => $customer->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function validateRecipient(CustomerNumber $customer): bool
    {
        $email = $this->getRecipientIdentifier($customer);
        return $email && filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function getActiveInstances(MailingList $mailingList): Collection
    {
        return $mailingList->mailInstances()->where('active', true)->get();
    }

    public function getRecipientIdentifier(CustomerNumber $customer): ?string
    {
        return $customer->email ?? null;
    }

    /**
     * Extract subject from message (first line or default).
     */
    private function extractSubject(string $message): string
    {
        $lines = explode("\n", $message);
        $firstLine = trim($lines[0] ?? '');
        
        if (strlen($firstLine) > 0 && strlen($firstLine) <= 100) {
            return strip_tags($firstLine);
        }
        
        return 'Рассылка';
    }
}


