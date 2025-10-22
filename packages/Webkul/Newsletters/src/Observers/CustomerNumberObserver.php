<?php

namespace Webkul\Newsletters\Observers;

use Webkul\Newsletters\Models\CustomerNumber;
use Webkul\Newsletters\Events\MailingListStatsUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerNumberObserver
{
    /**
     * Handle the CustomerNumber "created" event.
     */
    public function created(CustomerNumber $customerNumber): void
    {
        $this->broadcastStatsUpdate($customerNumber->mailing_list_id);
    }

    /**
     * Handle the CustomerNumber "updated" event.
     */
    public function updated(CustomerNumber $customerNumber): void
    {
        // Check if delivered or incoming_message status changed
        if ($customerNumber->isDirty(['delivered', 'incoming_message', 'viewed'])) {
            $this->broadcastStatsUpdate($customerNumber->mailing_list_id);
        }
    }

    /**
     * Handle the CustomerNumber "deleted" event.
     */
    public function deleted(CustomerNumber $customerNumber): void
    {
        $this->broadcastStatsUpdate($customerNumber->mailing_list_id);
    }

    /**
     * Broadcast stats update for a mailing list.
     */
    private function broadcastStatsUpdate(int $mailingListId): void
    {
        try {
            // Get updated stats from database
            $stats = DB::table('newsletters_customer_numbers')
                ->where('mailing_list_id', $mailingListId)
                ->selectRaw('
                    COUNT(*) as total_count,
                    SUM(CASE WHEN delivered = 1 THEN 1 ELSE 0 END) as sent_count,
                    SUM(CASE WHEN incoming_message = 1 THEN 1 ELSE 0 END) as incoming_count,
                    SUM(CASE WHEN viewed = 1 THEN 1 ELSE 0 END) as viewed_count
                ')
                ->first();

            if ($stats) {
                // Broadcast the update
                broadcast(new MailingListStatsUpdated($mailingListId, [
                    'sent_count' => (int) $stats->sent_count,
                    'incoming_count' => (int) $stats->incoming_count,
                    'viewed_count' => (int) $stats->viewed_count,
                    'total_count' => (int) $stats->total_count
                ]));

                Log::info('Mailing list stats updated', [
                    'mailing_list_id' => $mailingListId,
                    'stats' => $stats
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to broadcast mailing list stats update', [
                'mailing_list_id' => $mailingListId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
