<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class PlatformPaymentTransaction extends Model
{
    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'plan_id',
        'invoice_number',
        'type',
        'payment_method',
        'payment_channel',
        'payment_provider',
        'provider_reference',
        'amount_subtotal',
        'amount_discount',
        'amount_total',
        'billing_cycle',
        'status',
        'due_at',
        'paid_at',
        'expires_at',
        'notes',
        'meta_json',
    ];

    protected $casts = [
        'amount_subtotal' => 'decimal:2',
        'amount_discount' => 'decimal:2',
        'amount_total' => 'decimal:2',
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'meta_json' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function proofs(): HasMany
    {
        return $this->hasMany(PlatformPaymentProof::class, 'payment_transaction_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(PlatformPaymentEvent::class, 'payment_transaction_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid' && $this->paid_at instanceof Carbon;
    }
}
