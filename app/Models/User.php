<?php

namespace App\Models;

use App\Models\Platform\PlatformPermission;
use App\Models\Platform\PlatformRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[Fillable(['name', 'email', 'password', 'is_platform_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_platform_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function platformRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            PlatformRole::class,
            'platform_role_user',
            'user_id',
            'platform_role_id'
        );
    }

    /**
     * Cek satu permission platform. Super-admin (flag is_platform_admin) selalu lolos.
     */
    public function hasPlatformPermission(string $permission): bool
    {
        if ($this->is_platform_admin) {
            return true;
        }

        return $this->platformPermissionNames()->contains($permission);
    }

    public function hasPlatformRole(string $role): bool
    {
        return $this->platformRoles->contains('name', $role);
    }

    public function hasAnyPlatformRole(): bool
    {
        return $this->platformRoles->isNotEmpty();
    }

    /**
     * Nama semua permission yang dimiliki user melalui role-nya.
     *
     * @return Collection<int, string>
     */
    public function platformPermissionNames(): Collection
    {
        $this->loadMissing('platformRoles.permissions');

        return $this->platformRoles
            ->flatMap(fn (PlatformRole $role) => $role->permissions->pluck('name'))
            ->unique()
            ->values();
    }

    /**
     * Gabungan nama permission + role untuk ditampilkan di UI.
     *
     * @return array<int, string>
     */
    public function platformAbilityNames(): array
    {
        return PlatformPermission::query()->pluck('name')->all();
    }
}
