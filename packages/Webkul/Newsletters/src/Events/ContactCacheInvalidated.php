<?php

namespace Webkul\Newsletters\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactCacheInvalidated
{
    use Dispatchable, SerializesModels;

    /**
     * The contact group ID.
     *
     * @var int
     */
    public int $contactGroupId;

    /**
     * Create a new event instance.
     *
     * @param int $contactGroupId
     * @return void
     */
    public function __construct(int $contactGroupId)
    {
        $this->contactGroupId = $contactGroupId;
    }
}

