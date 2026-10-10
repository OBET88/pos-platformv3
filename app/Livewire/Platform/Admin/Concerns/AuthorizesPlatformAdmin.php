<?php

namespace App\Livewire\Platform\Admin\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Otorisasi defence-in-depth untuk komponen Livewire admin platform.
 *
 * Middleware ['auth', 'platform.admin'] hanya berlaku pada route halaman.
 * Request update Livewire masuk lewat endpoint /livewire/update sehingga
 * logika aksi komponen bisa dipanggil tanpa melewati middleware route
 * tersebut. Trait ini memastikan setiap komponen memverifikasi sendiri
 * bahwa pengguna yang mengeksekusi aksi adalah admin platform.
 */
trait AuthorizesPlatformAdmin
{
    public function bootAuthorizesPlatformAdmin(): void
    {
        $this->authorizeAdminAccess();
    }

    public function bootedAuthorizesPlatformAdmin(): void
    {
        //
    }

    protected function authorizeAdminAccess(): void
    {
        $user = auth()->user();

        $allowed = $user
            && ((bool) $user->is_platform_admin || $user->hasAnyPlatformRole());

        if (! $allowed) {
            abort(403, 'Akses hanya untuk admin platform.');
        }
    }

    /**
     * Guard tambahan sebelum mutasi pada resource tertentu.
     */
    protected function authorizeAdminMutation(?Model $model = null): void
    {
        $this->authorizeAdminAccess();
    }

    /**
     * Pastikan user punya permission platform tertentu (super-admin selalu lolos).
     */
    protected function authorizePlatformPermission(string $permission): void
    {
        $user = auth()->user();

        if (! $user || ! $user->hasPlatformPermission($permission)) {
            abort(403, 'Anda tidak memiliki hak akses untuk tindakan ini.');
        }
    }

    protected function platformUserCan(string $permission): bool
    {
        $user = auth()->user();

        return (bool) $user && $user->hasPlatformPermission($permission);
    }
}
