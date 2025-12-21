<?php

namespace Webkul\Newsletters\Services;

use Webkul\Newsletters\Contracts\MailingChannelInterface;
use Webkul\Newsletters\Services\Channels\WhatsAppChannel;
use Webkul\Newsletters\Services\Channels\EmailChannel;
use Webkul\Newsletters\Services\Channels\TelegramChannel;
use InvalidArgumentException;

class MailingChannelFactory
{
    /**
     * Create a channel instance by type.
     */
    public static function create(string $channelType): MailingChannelInterface
    {
        return match ($channelType) {
            'whatsapp' => new WhatsAppChannel(),
            'email' => new EmailChannel(),
            'telegram' => new TelegramChannel(),
            default => throw new InvalidArgumentException("Unknown channel type: {$channelType}"),
        };
    }

    /**
     * Get all available channel types.
     */
    public static function getAvailableChannels(): array
    {
        return ['whatsapp', 'email', 'telegram'];
    }
}



