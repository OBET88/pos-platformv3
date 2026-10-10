<?php

namespace App\Livewire\Platform\Admin\Concerns;

use Illuminate\Database\Eloquent\Model;
use Livewire\Exceptions\MethodNotFoundException;

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

        if (! $user || ! (bool) $user->is_platform_admin) {
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
}
