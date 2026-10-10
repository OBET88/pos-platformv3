<?php

namespace App\Models\Platform;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformPaymentProof extends Model
{
    protected $fillable = [
        'payment_transaction_id',
        'bank_name',
        'account_name',
        'account_number_masked',
        'transfer_amount',
        'transfer_date',
        'proof_file_path',
        'submitted_by_name',
        'submitted_by_email',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'transfer_amount' => 'decimal:2',
        'transfer_date' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PlatformPaymentTransaction::class, 'payment_transaction_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
