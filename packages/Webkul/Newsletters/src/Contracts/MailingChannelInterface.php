<?php

namespace Webkul\Newsletters\Contracts;

use Webkul\Newsletters\Models\MailingList;
use Webkul\Newsletters\Models\CustomerNumber;

interface MailingChannelInterface
{
    /**
     * Get the channel type identifier.
     */
    public function getChannelType(): string;

    /**
     * Send a message to a recipient.
     *
     * @param object $instance The channel instance (VacapInstance, TelegramBotInstance, MailInstance)
     * @param CustomerNumber $customer The recipient
     * @param string $message The message content
     * @return string|null Message ID on success, null on failure
     */
    public function sendMessage(object $instance, CustomerNumber $customer, string $message): ?string;

    /**
     * Validate if the recipient has valid contact info for this channel.
     */
    public function validateRecipient(CustomerNumber $customer): bool;

    /**
     * Get active instances for the mailing list.
     */
    public function getActiveInstances(MailingList $mailingList): \Illuminate\Support\Collection;

    /**
     * Get the recipient identifier from customer (phone, email, telegram_id).
     */
    public function getRecipientIdentifier(CustomerNumber $customer): ?string;
}


