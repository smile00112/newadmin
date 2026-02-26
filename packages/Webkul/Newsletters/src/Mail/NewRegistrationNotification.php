<?php

namespace Webkul\Newsletters\Mail;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Webkul\Shop\Mail\Mailable;
use Webkul\User\Contracts\Admin;

class NewRegistrationNotification extends Mailable
{
    public function __construct(
        public Admin $admin
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('support@targetx.su', 'TargetX'),
            subject: 'Новая регистрация в TargetX',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'newsletters::emails.new-registration-admin',
        );
    }
}
