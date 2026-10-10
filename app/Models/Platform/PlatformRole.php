<?php

namespace App\Models\Platform;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlatformRole extends Model
{
    protected $table = 'platform_roles';

    protected $fillable = [
        'name',
        'label',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            PlatformPermission::class,
            'platform_permission_role',
            'platform_role_id',
            'platform_permission_id'
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'platform_role_user',
            'platform_role_id',
            'user_id'
        );
    }
}
