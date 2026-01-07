<?php

namespace Webkul\Newsletters\Observers;

use Webkul\Newsletters\Models\NewslettersContact;
use Webkul\Newsletters\Events\ContactCacheInvalidated;
use Illuminate\Support\Facades\Event;

class ContactObserver
{
    /**
     * Handle the NewslettersContact "created" event.
     *
     * @param NewslettersContact $contact
     * @return void
     */
    public function created(NewslettersContact $contact): void
    {
        if ($contact->contact_group_id) {
            Event::dispatch(new ContactCacheInvalidated($contact->contact_group_id));
        }
    }

    /**
     * Handle the NewslettersContact "updated" event.
     *
     * @param NewslettersContact $contact
     * @return void
     */
    public function updated(NewslettersContact $contact): void
    {
        // Check if contact_group_id changed or any filterable field changed
        $filterableFields = [
            'gender',
            'last_order_date',
            'registration_date',
            'birth_date',
            'orders_count',
            'average_check',
            'total_check',
            'average_order_rating',
            'favorite_category',
            'favorite_dish',
            'store',
            'contact_group_id',
        ];

        $hasRelevantChange = false;
        foreach ($filterableFields as $field) {
            if ($contact->isDirty($field)) {
                $hasRelevantChange = true;
                break;
            }
        }

        if ($hasRelevantChange) {
            // Invalidate cache for both old and new contact_group_id if changed
            if ($contact->isDirty('contact_group_id')) {
                $oldGroupId = $contact->getOriginal('contact_group_id');
                if ($oldGroupId) {
                    Event::dispatch(new ContactCacheInvalidated($oldGroupId));
                }
            }

            if ($contact->contact_group_id) {
                Event::dispatch(new ContactCacheInvalidated($contact->contact_group_id));
            }
        }
    }

    /**
     * Handle the NewslettersContact "deleted" event.
     *
     * @param NewslettersContact $contact
     * @return void
     */
    public function deleted(NewslettersContact $contact): void
    {
        if ($contact->contact_group_id) {
            Event::dispatch(new ContactCacheInvalidated($contact->contact_group_id));
        }
    }
}

