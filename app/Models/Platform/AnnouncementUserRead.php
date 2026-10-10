<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementUserRead extends Model
{
    protected $table = 'platform_announcement_user_reads';

    protected $fillable = [
        'announcement_id',
        'tenant_code',
        'tenant_user_id',
        'tenant_user_email',
        'dismissed_at',
    ];

    protected $casts = [
        'dismissed_at' => 'datetime',
    ];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }
}
