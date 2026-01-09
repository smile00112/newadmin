<?php

namespace Webkul\Newsletters\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Newsletters\Events\MailingListStatsUpdated;
use Webkul\Newsletters\Models\MailingList;
use Webkul\Newsletters\Models\CustomerNumber;
use Webkul\Newsletters\Models\MailInstance;
use Webkul\Newsletters\Models\TelegramBotInstance;
use Webkul\Newsletters\Services\MailingChannelFactory;
use Webkul\Newsletters\Repositories\CompanyAccountRepository;
use Illuminate\Support\Facades\Log;

class SendChannelMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;

    public function __construct(
        protected string $channelType,
        protected int $instanceId,
        protected int $customerId,
        protected string $message,
        protected int $mailingListId
    ) {
        $this->onQueue('mailing-send');
    }

    public function handle()
    {
        Log::info('SendChannelMessage handle started', [
            'channel_type'   => $this->channelType,
            'instance_id'    => $this->instanceId,
            'customer_id'    => $this->customerId,
            'mailing_list_id'=> $this->mailingListId,
        ]);

        $customer = CustomerNumber::with('mailingList')->findOrFail($this->customerId);
        $mailingList = MailingList::findOrFail($this->mailingListId);

        // Get the appropriate instance based on channel type
        $instance = $this->getInstance();
        if (!$instance) {
            Log::error('SendChannelMessage: Instance not found', [
                'channel_type' => $this->channelType,
                'instance_id' => $this->instanceId,
            ]);
            $customer->update(['sending' => true, 'send_error' => true]);
            return;
        }

        // Mark as sending
        $customer->update(['sending' => true]);

        // Create channel and send message
        $channel = MailingChannelFactory::create($this->channelType);
        $messageId = $channel->sendMessage($instance, $customer, $this->message);

        if ($messageId) {
            $customer->update([
                'delivered' => true,
                'sent_at' => now(),
                'greenapi_chat_id' => $messageId,
            ]);

            // Deduct from account balance
            if ($mailingList->company_id) {
                $accountRepository = app(CompanyAccountRepository::class);
                $account = $accountRepository->getOrCreateForCompany($mailingList->company_id);
                $account->decrement('balance', 1);
            }

            Log::info('Message sent successfully', [
                'channel_type' => $this->channelType,
                'customer_id' => $this->customerId,
                'message_id' => $messageId,
            ]);
        } else {
            $customer->update(['send_error' => true]);

            Log::error('Message sending failed', [
                'channel_type' => $this->channelType,
                'customer_id' => $this->customerId,
            ]);
        }

        // Broadcast stats update
        $this->broadcastStats($mailingList);
    }

    protected function getInstance(): ?object
    {
        return match ($this->channelType) {
            'email' => MailInstance::find($this->instanceId),
            'telegram' => TelegramBotInstance::find($this->instanceId),
            default => null,
        };
    }

    protected function broadcastStats(MailingList $mailingList): void
    {
        $mailingListStats = MailingList::with('customerNumbers')->withCount([
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
    }
}



