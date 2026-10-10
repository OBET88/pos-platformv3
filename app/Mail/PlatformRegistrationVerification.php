<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlatformRegistrationVerification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $ownerName,
        public readonly string $companyName,
        public readonly string $verificationUrl,
        public readonly string $expiresAtLabel,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verifikasi email untuk pendaftaran tenant Anda',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'platform.mail.registration-verification',
        );
    }
}
