<?php

namespace App\Mail;

use App\Models\Platform\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlatformTenantAlert extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly string $alertTitle,
        public readonly string $summary,
        public readonly string $adminUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[POS Platform] '.$this->alertTitle.' - '.$this->tenant->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'platform.mail.tenant-alert',
        );
    }
}
