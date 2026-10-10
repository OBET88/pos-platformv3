<div class="space-y-6" x-data="{ open: false }">
    <div class="max-w-3xl">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-700">Hak Akses Platform</p>
        <h1 class="mt-2 text-2xl font-semibold text-slate-900">Kelola akun staff platform.</h1>
        <p class="mt-2 text-sm text-slate-500">Buat akun staff dan tetapkan role. Super-admin (ditandai khusus) selalu punya seluruh akses.</p>
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

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <button type="button" @click="open = !open" class="flex w-full items-center justify-between">
            <span class="text-left">
                <span class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Tambah</span>
                <span class="mt-1 block text-xl font-semibold text-slate-900">Akun Staff Platform Baru</span>
            </span>
            <span class="text-sm font-semibold text-emerald-700" x-text="open ? 'Tutup' : 'Buka'"></span>
        </button>

        <form x-show="open" x-cloak wire:submit="createUser" class="mt-5 grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nama</label>
                <input wire:model="form.name" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                @error('form.name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Email</label>
                <input wire:model="form.email" type="email" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                @error('form.email')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Password</label>
                <input wire:model="form.password" type="password" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                @error('form.password')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Role Awal</label>
                <select wire:model="form.role" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                    <option value="">— tanpa role —</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->label }}</option>
                    @endforeach
                </select>
                @error('form.role')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Buat Akun</button>
            </div>
        </form>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-xl font-semibold text-slate-900">Pengguna Platform</h3>

        <div class="mt-4 space-y-4">
            @foreach ($users as $user)
                <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="text-base font-semibold text-slate-900">{{ $user->name }}</h4>
                                @if ($user->is_platform_admin)
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">SUPER ADMIN</span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">STAFF</span>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-slate-500">{{ $user->email }}</p>
                        </div>

                        <button type="button" wire:click="toggleSuperAdmin({{ $user->id }})" wire:confirm="Ubah status super-admin user ini?" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                            {{ $user->is_platform_admin ? 'Cabut Super Admin' : 'Jadikan Super Admin' }}
                        </button>
                    </div>

                    @unless ($user->is_platform_admin)
                        <div class="mt-4 border-t border-slate-200 pt-4">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Role</p>
                            <div class="flex flex-wrap gap-3">
                                @foreach ($roles as $role)
                                    <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                        <input type="checkbox" wire:model="roleSelections.{{ $user->id }}" value="{{ $role->name }}" class="rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                                        {{ $role->label }}
                                    </label>
                                @endforeach
                            </div>
                            <button type="button" wire:click="saveRoles({{ $user->id }})" class="mt-3 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Simpan Role</button>
                        </div>
                    @endunless
                </article>
            @endforeach
        </div>

        <div class="mt-4">{{ $users->links() }}</div>
    </section>
</div>