<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlatformTenantReady extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $ownerName,
        public readonly string $companyName,
        public readonly string $tenantCode,
        public readonly string $username,
        public readonly ?string $loginUrl = null,
        public readonly ?string $forgotPasswordUrl = null,
        public readonly ?string $businessName = null,
        public readonly ?string $branchName = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tenant Anda sudah siap digunakan',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'platform.mail.tenant-ready',
        );
    }
}
