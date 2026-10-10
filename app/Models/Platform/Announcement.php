<?php

namespace App\Models\Platform;

use App\Models\User;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    protected $table = 'platform_announcements';

    protected $fillable = [
        'title',
        'message',
        'message_html',
        'tone',
        'is_active',
        'show_as_modal',
        'starts_at',
        'ends_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_as_modal' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function dismissals(): HasMany
    {
        return $this->hasMany(AnnouncementDismissal::class, 'announcement_id');
    }

    public function tenantReads(): HasMany
    {
        return $this->hasMany(AnnouncementUserRead::class, 'announcement_id');
    }

    /**
     * Versi HTML tersanitasi untuk ditampilkan; fallback ke pesan polos.
     */
    public function renderedHtml(): string
    {
        if (filled($this->message_html)) {
            return HtmlSanitizer::clean($this->message_html);
        }

        return nl2br(e((string) $this->message));
    }

    public function scopeActiveWindow(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $inner): void {
                $inner->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $inner): void {
                $inner->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }
}
