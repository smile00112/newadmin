<?php

namespace Webkul\Newsletters\Services;

use Webkul\Newsletters\Models\MailingList;
use Webkul\Newsletters\Models\VacapInstance;
use Webkul\Newsletters\Contracts\MailingChannelInterface;
use Webkul\Newsletters\Jobs\ProcessWhatsAppBatchByInstances;
use Webkul\Newsletters\Jobs\ProcessMailingBatch;
use Webkul\Newsletters\Repositories\CompanyAccountRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MailingListStarterService
{
    public function __construct(
        protected CompanyAccountRepository $accountRepository
    ) {}

    /**
     * Start mailing list processing.
     *
     * @param MailingList $mailingList
     * @return array ['success' => bool, 'message' => string, 'status_code' => int]
     */
    public function start(MailingList $mailingList): array
    {
        // Validate account balance
        $balanceCheck = $this->validateAccountBalance($mailingList);
        if (!$balanceCheck['success']) {
            return $balanceCheck;
        }

        // Get channel type
        $channelType = $mailingList->channel_type ?? 'whatsapp';
        $channel = MailingChannelFactory::create($channelType);

        // Validate instances
        $instancesCheck = $this->validateInstances($mailingList, $channel);
        if (!$instancesCheck['success']) {
            return $instancesCheck;
        }

        // Validate customers
        $customersCheck = $this->validateCustomers($mailingList);
        if (!$customersCheck['success']) {
            return $customersCheck;
        }

        // Reset sending state for all customer numbers of this mailing list
        DB::table('newsletters_customer_numbers')
            ->where('mailing_list_id', $mailingList->id)
            ->update([
                'sending'   => false,
                'delivered' => false,
                'viewed'    => false,
            ]);

        // Activate mailing list
        $mailingList->update([
            'active' => true,
            'status' => 'pending'
        ]);

        // Dispatch appropriate job
        $this->dispatchMailingJob($mailingList, $channel);

        Log::info('Mailing list started successfully', [
            'mailing_list_id' => $mailingList->id,
            'channel_type' => $channelType,
            'user_id' => auth()->id(),
        ]);

        return [
            'success' => true,
            'message' => trans('newsletters::app.admin.mailing-lists.mailing-started'),
            'status_code' => 200
        ];
    }

    /**
     * Validate account balance.
     */
    protected function validateAccountBalance(MailingList $mailingList): array
    {
        if (!$mailingList->company_id) {
            return ['success' => true];
        }

        $account = $this->accountRepository->getOrCreateForCompany($mailingList->company_id);
        
        if ($account->balance <= 0) {
            return [
                'success' => false,
                'message' => trans('newsletters::app.admin.account.insufficient-balance'),
                'status_code' => 402
            ];
        }

        return ['success' => true];
    }

    /**
     * Validate instances for the mailing list.
     */
    protected function validateInstances(MailingList $mailingList, MailingChannelInterface $channel): array
    {
        $instances = $channel->getActiveInstances($mailingList);

        // Special handling for WhatsApp: try to auto-link company instances
        if ($channel->getChannelType() === 'whatsapp' && $instances->isEmpty() && $mailingList->company_id) {
            $instances = $this->autoLinkWhatsAppInstances($mailingList);
        }

        if ($instances->isEmpty()) {
            $channelType = $channel->getChannelType();
            $transKey = "newsletters::app.admin.mailing-lists.no-{$channelType}-instances";
            
            return [
                'success' => false,
                'message' => trans($transKey) ?: "Для этого списка рассылки не настроены " . ucfirst($channelType) . " инстансы.",
                'status_code' => 400
            ];
        }

        return ['success' => true];
    }

    /**
     * Auto-link company's active WhatsApp instances to mailing list.
     */
    protected function autoLinkWhatsAppInstances(MailingList $mailingList): \Illuminate\Support\Collection
    {
        $companyActiveInstances = VacapInstance::where('company_id', $mailingList->company_id)
            ->where('active', true)
            ->get();

        if ($companyActiveInstances->isNotEmpty()) {
            $mailingList->whatsappInstances()->sync($companyActiveInstances->pluck('id')->toArray());
            
            Log::info('Automatically linked company WhatsApp instances to mailing list', [
                'mailing_list_id' => $mailingList->id,
                'company_id' => $mailingList->company_id,
                'instances_count' => $companyActiveInstances->count(),
            ]);

            return $mailingList->whatsappInstances()
                ->where('active', true)
                ->where('blocked', false)
                ->get();
        }

        return collect();
    }

    /**
     * Validate customers for the mailing list.
     */
    protected function validateCustomers(MailingList $mailingList): array
    {
        if ($mailingList->customerNumbers()->count() === 0) {
            return [
                'success' => false,
                'message' => trans('newsletters::app.admin.mailing-lists.no-customer-numbers'),
                'status_code' => 400
            ];
        }

        return ['success' => true];
    }

    /**
     * Dispatch the appropriate mailing job based on channel type.
     */
    protected function dispatchMailingJob(MailingList $mailingList, MailingChannelInterface $channel): void
    {
        $delay = $this->calculateMailingDelay($mailingList);
        $customerIds = $mailingList->customerNumbers()
            ->where('sending', false)
            ->where('send_error', false)
            ->pluck('id')
            ->toArray();
        $companyId = $mailingList->company_id;
        $channelType = $channel->getChannelType();

        // Use specialized WhatsApp job or unified job for other channels
        $job = $channelType === 'whatsapp'
            ? ProcessWhatsAppBatchByInstances::class
            : ProcessMailingBatch::class;

        $queue = $channelType === 'whatsapp'
            ? 'whatsapp-batch-instances'
            : 'mailing-batch';

        if ($delay > 0) {
            $job::dispatch($mailingList->id, $customerIds, 0, $companyId)
                ->delay(now()->addSeconds($delay))
                ->onQueue($queue);

            Log::info('Mailing list scheduled with delay', [
                'mailing_list_id' => $mailingList->id,
                'channel_type' => $channelType,
                'delay_seconds' => $delay,
                'customers_count' => count($customerIds),
                'scheduled_at' => now()->addSeconds($delay)->toDateTimeString(),
            ]);
        } else {
            $job::dispatch($mailingList->id, $customerIds, 0, $companyId)
                ->onQueue($queue);

            Log::info('Starting mailing list without delay', [
                'mailing_list_id' => $mailingList->id,
                'channel_type' => $channelType,
                'customers_count' => count($customerIds),
            ]);
        }
    }

    /**
     * Calculate delay based on mailing list parameters.
     */
    protected function calculateMailingDelay(MailingList $mailingList): int
    {
        if ($mailingList->start_at && $mailingList->start_at->isFuture()) {
            return now()->diffInSeconds($mailingList->start_at);
        }

        return 0;
    }
}

