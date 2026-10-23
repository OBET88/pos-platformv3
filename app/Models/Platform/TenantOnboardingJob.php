<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantOnboardingJob extends Model
{
    protected $fillable = [
        'tenant_id',
        'status',
        'payload',
        'access_token_hash',
        'access_expires_at',
        'attempts',
        'available_at',
        'leased_at',
        'lease_expires_at',
        'lease_token',
        'last_error_class',
        'last_error',
        'last_started_at',
        'completed_at',
        'failed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'available_at' => 'datetime',
        'access_expires_at' => 'datetime',
        'leased_at' => 'datetime',
        'lease_expires_at' => 'datetime',
        'last_started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
