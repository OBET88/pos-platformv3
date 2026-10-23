<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformPaymentEvent extends Model
{
    protected $fillable = [
        'payment_transaction_id',
        'payment_provider',
        'event_key',
        'provider_reference',
        'provider_event_key',
        'signature_valid',
        'payload_json',
        'process_status',
        'received_at',
        'processed_at',
        'failure_reason',
    ];

    protected $casts = [
        'signature_valid' => 'boolean',
        'payload_json' => 'array',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PlatformPaymentTransaction::class, 'payment_transaction_id');
    }
}
