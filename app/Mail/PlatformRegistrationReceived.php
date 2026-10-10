<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlatformRegistrationReceived extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $ownerName,
        public readonly string $companyName,
        public readonly string $tenantCode,
        public readonly string $statusUrl,
        public readonly ?string $loginUrl = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran tenant Anda sudah diterima',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'platform.mail.registration-received',
        );
    }
}
