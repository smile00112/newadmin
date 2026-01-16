<?php

namespace Webkul\Newsletters\Mail;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Webkul\Shop\Mail\Mailable;
use Webkul\User\Contracts\Admin;

class NewUserNotification extends Mailable
{
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string
     */
    public $queue = 'mailing-send';

    /**
     * Create a new mailable instance.
     *
     * @return void
     */
    public function __construct(
        public Admin $newAdmin,
        public string $companyName,
        public string $plan,
        public string $password
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'support@targetx.su'),
                config('mail.from.name', 'TargetX')
            ),
            subject: 'Новый пользователь зарегистрирован в системе',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'newsletters::emails.new-user-notification',
        );
    }
}

