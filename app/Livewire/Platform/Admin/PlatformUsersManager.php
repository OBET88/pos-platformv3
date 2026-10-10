<?php

namespace App\Livewire\Platform\Admin;

use App\Livewire\Platform\Admin\Concerns\AuthorizesPlatformAdmin;
use App\Models\Platform\PlatformRole;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.platform-admin')]
class PlatformUsersManager extends Component
{
    use AuthorizesPlatformAdmin;
    use WithPagination;

    public array $form = [
        'name' => '',
        'email' => '',
        'password' => '',
        'role' => '',
    ];

    /** @var array<int, array<int, string>> keyed by user id */
    public array $roleSelections = [];

    public function mount(): void
    {
        $this->authorizePlatformPermission('manage_platform_users');
    }

    public function createUser(): void
    {
        $this->authorizePlatformPermission('manage_platform_users');

        $validated = $this->validate([
            'form.name' => ['required', 'string', 'max:150'],
            'form.email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'form.password' => ['required', 'string', 'min:8'],
            'form.role' => ['nullable', 'string', 'exists:platform_roles,name'],
        ])['form'];

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'is_platform_admin' => false,
        ]);

        if (filled($validated['role'] ?? null)) {
            $roleId = PlatformRole::query()->where('name', $validated['role'])->value('id');

            if ($roleId) {
                $user->platformRoles()->sync([$roleId]);
            }
        }

        $this->reset('form');
        $this->form = ['name' => '', 'email' => '', 'password' => '', 'role' => ''];

        session()->flash('platform_admin_message', 'Pengguna platform berhasil dibuat.');
    }

    public function saveRoles(int $userId): void
    {
        $this->authorizePlatformPermission('manage_platform_users');

        $user = User::query()->findOrFail($userId);

        if ($user->is_platform_admin) {
            session()->flash('platform_admin_error', 'Super-admin selalu punya semua akses; tidak perlu role.');

            return;
        }

        $selected = $this->roleSelections[$userId] ?? [];

        $roleIds = PlatformRole::query()->whereIn('name', (array) $selected)->pluck('id')->all();
        $user->platformRoles()->sync($roleIds);

        session()->flash('platform_admin_message', 'Role '.$user->name.' diperbarui.');
    }

    public function toggleSuperAdmin(int $userId): void
    {
        $this->authorizePlatformPermission('manage_platform_users');

        $target = User::query()->findOrFail($userId);

        // Anti-lockout: user tidak boleh mencabut status super-admin dirinya sendiri.
        if ($target->id === auth()->id() && $target->is_platform_admin) {
            session()->flash('platform_admin_error', 'Anda tidak dapat mencabut akses super-admin milik sendiri.');

            return;
        }

        $target->forceFill(['is_platform_admin' => ! $target->is_platform_admin])->save();

        session()->flash('platform_admin_message', 'Status super-admin '.$target->name.' diperbarui.');
    }

    public function render()
    {
        $roles = PlatformRole::query()->orderBy('label')->get();

        $users = User::query()
            ->with('platformRoles')
            ->orderByDesc('is_platform_admin')
            ->orderBy('name')
            ->paginate(15);

        foreach ($users as $user) {
            if (! array_key_exists($user->id, $this->roleSelections)) {
                $this->roleSelections[$user->id] = $user->platformRoles->pluck('name')->all();
            }
        }

        return view('livewire.platform.admin.platform-users-manager', [
            'roles' => $roles,
            'users' => $users,
            'roleSelections' => $this->roleSelections,
            'title' => 'Platform Admin - Pengguna Platform',
        ]);
    }
}
