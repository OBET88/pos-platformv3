<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingJournal extends Model
{
    protected $fillable = [
        'tenant_id',
        'business_id',
        'branch_id',
        'journal_no',
        'journal_date',
        'source_type',
        'source_id',
        'event_key',
        'reference_no',
        'description',
        'posting_status',
        'posted_at',
        'posted_by',
        'meta',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'journal_date' => 'date',
        'posted_at' => 'datetime',
        'meta' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(AccountingJournalLine::class, 'journal_id');
    }

    public function scopeForTenant(Builder $query, Tenant|int $tenant): Builder
    {
        return $query->where('tenant_id', $tenant instanceof Tenant ? $tenant->getKey() : $tenant);
    }

    public function scopePosted(Builder $query): Builder
    {
        return $query->where('posting_status', 'posted');
    }

    public function scopePendingMapping(Builder $query): Builder
    {
        return $query->where('posting_status', 'pending_mapping');
    }
}
