<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlatformPermission extends Model
{
    protected $table = 'platform_permissions';

    protected $fillable = [
        'name',
        'label',
        'group',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            PlatformRole::class,
            'platform_permission_role',
            'platform_permission_id',
            'platform_role_id'
        );
    }
}
