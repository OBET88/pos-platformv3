<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantDatabaseConfig extends Model
{
    protected $fillable = [
        'tenant_id',
        'connection_name',
        'db_host',
        'db_port',
        'db_name',
        'db_username',
        'db_password_encrypted',
        'status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
