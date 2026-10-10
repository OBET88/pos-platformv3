<?php

namespace App\Livewire\Platform\Admin;

use App\Livewire\Platform\Admin\Concerns\AuthorizesPlatformAdmin;
use App\Models\Platform\PlatformPermission;
use App\Models\Platform\PlatformRole;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.platform-admin')]
class RolesManager extends Component
{
    use AuthorizesPlatformAdmin;

    public ?int $editingRoleId = null;

    public string $newRoleName = '';

    public string $newRoleLabel = '';

    public array $selectedPermissions = [];

    public function mount(): void
    {
        $this->authorizePlatformPermission('view_roles');
    }

    public function edit(int $roleId): void
    {
        $this->authorizePlatformPermission('manage_roles');

        $role = PlatformRole::query()->with('permissions')->findOrFail($roleId);

        $this->editingRoleId = $role->id;
        $this->newRoleName = $role->name;
        $this->newRoleLabel = $role->label;
        $this->selectedPermissions = $role->permissions->pluck('name')->all();
    }

    public function save(): void
    {
        $this->authorizePlatformPermission('manage_roles');

        $validated = $this->validate([
            'newRoleName' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9\-_]+$/'],
            'newRoleLabel' => ['required', 'string', 'max:150'],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['string', 'exists:platform_permissions,name'],
        ]);

        $role = PlatformRole::query()->updateOrCreate(
            ['name' => $validated['newRoleName']],
            ['label' => $validated['newRoleLabel']]
        );

        $permissionIds = PlatformPermission::query()
            ->whereIn('name', $validated['selectedPermissions'] ?? [])
            ->pluck('id')
            ->all();

        $role->permissions()->sync($permissionIds);

        session()->flash('platform_admin_message', 'Role '.$role->label.' tersimpan.');

        $this->resetRoleForm();
    }

    public function deleteRole(int $roleId): void
    {
        $this->authorizePlatformPermission('manage_roles');

        $role = PlatformRole::query()->withCount('users')->findOrFail($roleId);

        if ($role->is_system) {
            session()->flash('platform_admin_error', 'Role sistem tidak dapat dihapus.');

            return;
        }

        if ($role->users_count > 0) {
            session()->flash('platform_admin_error', 'Role masih dipakai '.$role->users_count.' user. Lepas dulu dari user.');

            return;
        }

        $role->delete();

        session()->flash('platform_admin_message', 'Role berhasil dihapus.');

        $this->resetRoleForm();
    }

    public function cancelEdit(): void
    {
        $this->resetRoleForm();
    }

    private function resetRoleForm(): void
    {
        $this->editingRoleId = null;
        $this->newRoleName = '';
        $this->newRoleLabel = '';
        $this->selectedPermissions = [];
    }

    public function render()
    {
        return view('livewire.platform.admin.roles-manager', [
            'roles' => PlatformRole::query()->with(['permissions'])->withCount('users')->orderBy('is_system', 'desc')->orderBy('label')->get(),
            'permissions' => PlatformPermission::query()->orderBy('group')->orderBy('name')->get()->groupBy('group'),
            'canManage' => $this->platformUserCan('manage_roles'),
            'title' => 'Platform Admin - Role & Hak Akses',
        ]);
    }
}
