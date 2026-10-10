<div class="space-y-6">
    <div class="max-w-3xl">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-700">Hak Akses Platform</p>
        <h1 class="mt-2 text-2xl font-semibold text-slate-900">Atur role & permission staff platform.</h1>
        <p class="mt-2 text-sm text-slate-500">Beri akses sesuai tugas. Contoh: verifikator pembayaran hanya menyetujui pembayaran, admin konten hanya mengelola pengumuman.</p>
    </div>

    @if (session('platform_admin_message'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('platform_admin_message') }}
        </div>
    @endif

    @if (session('platform_admin_error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            {{ session('platform_admin_error') }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_400px]">
        <section class="space-y-4">
            @foreach ($roles as $role)
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-semibold text-slate-900">{{ $role->label }}</h2>
                                <code class="rounded bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $role->name }}</code>
                                @if ($role->is_system)
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">SISTEM</span>
                                @endif
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $role->users_count }} user</span>
                            </div>
                            <p class="mt-2 text-sm text-slate-500">
                                {{ $role->permissions->pluck('label')->join(', ') ?: 'Belum ada permission.' }}
                            </p>
                        </div>

                        @if ($canManage)
                            <div class="flex gap-2">
                                <button type="button" wire:click="edit({{ $role->id }})" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Edit</button>
                                @unless ($role->is_system)
                                    <button type="button" wire:click="deleteRole({{ $role->id }})" wire:confirm="Hapus role ini?" class="rounded-xl border border-rose-300 bg-white px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">Hapus</button>
                                @endunless
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>

        @if ($canManage)
            <aside class="h-fit rounded-3xl border border-slate-200 bg-white p-5 shadow-sm xl:sticky xl:top-6">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Form Role</p>
                <h2 class="mt-2 text-xl font-semibold text-slate-900">{{ $editingRoleId ? 'Edit Role' : 'Role Baru' }}</h2>

                <form wire:submit="save" class="mt-4 space-y-4">
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Teknis</label>
                        <input wire:model="newRoleName" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" placeholder="mis. finance-staff" @disabled($editingRoleId !== null && (bool) optional($roles->firstWhere('id', $editingRoleId))->is_system)>
                        @error('newRoleName')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Label</label>
                        <input wire:model="newRoleLabel" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" placeholder="mis. Staff Keuangan">
                        @error('newRoleLabel')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Permission</p>
                        @foreach ($permissions as $group => $groupPermissions)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                <p class="mb-2 text-xs font-semibold text-slate-600">{{ $group }}</p>
                                <div class="space-y-1.5">
                                    @foreach ($groupPermissions as $permission)
                                        <label class="flex items-center gap-2 text-sm text-slate-700">
                                            <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" class="rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                                            {{ $permission->label }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        @error('selectedPermissions.*')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Simpan Role</button>
                        @if ($editingRoleId)
                            <button type="button" wire:click="cancelEdit" class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Batal</button>
                        @endif
                    </div>
                </form>
            </aside>
        @endif
    </div>
</div>