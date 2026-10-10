<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingRegistration extends Model
{
    protected $table = 'platform_pending_registrations';

    protected $fillable = [
        'owner_email',
        'verification_token',
        'payload',
        'verification_sent_at',
        'email_verified_at',
        'consumed_at',
        'expires_at',
        'tenant_id',
    ];

    protected $casts = [
        'payload' => 'array',
        'verification_sent_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'consumed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
