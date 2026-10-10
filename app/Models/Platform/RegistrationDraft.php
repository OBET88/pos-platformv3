<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;

class RegistrationDraft extends Model
{
    protected $table = 'platform_registration_drafts';

    protected $fillable = [
        'resume_token',
        'owner_email',
        'payload',
        'last_seen_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'last_seen_at' => 'datetime',
    ];
}
