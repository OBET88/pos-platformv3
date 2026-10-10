<?php

namespace App\Models\Platform;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InboxItem extends Model
{
    protected $table = 'platform_inbox_items';

    protected $fillable = [
        'tenant_id',
        'category',
        'event_key',
        'title',
        'message',
        'tone',
        'source_type',
        'source_id',
        'fingerprint',
        'payload',
        'occurred_at',
        'follow_up_status',
        'follow_up_note',
        'follow_up_user_id',
        'follow_up_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
        'follow_up_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function reads(): HasMany
    {
        return $this->hasMany(InboxItemRead::class, 'inbox_item_id');
    }

    public function followUpUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follow_up_user_id');
    }
}
