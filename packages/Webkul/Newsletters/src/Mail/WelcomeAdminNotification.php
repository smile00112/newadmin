<?php

namespace Webkul\Newsletters\Mail;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Webkul\Shop\Mail\Mailable;
use Webkul\User\Contracts\Admin;

class WelcomeAdminNotification extends Mailable
{
    /**
     * Create a new mailable instance.
     *
     * @return void
     */
    public function __construct(
        public Admin $admin,
        public string $password
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('support@targetx.su', 'TargetX'),
            subject: 'Добро пожаловать в TargetX!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'newsletters::emails.welcome-admin',
        );
    }
}

