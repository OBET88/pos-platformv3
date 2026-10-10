<div class="space-y-6">
    <div class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-4xl">
                <p class="text-xs font-semibold uppercase tracking-[0.32em] text-emerald-700">Akuntansi Tenant · Setting</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">Kelola COA dan mapping akun default per tenant.</h1>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">Halaman ini dipakai untuk memastikan tenant punya COA dasar dan mapping akun yang siap dipakai engine jurnal otomatis. Fokus utamanya adalah kesiapan UAT dan wiring transaksi ke jurnal.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="button" wire:click="provisionDefaultChart" wire:loading.attr="disabled" wire:target="provisionDefaultChart" class="inline-flex items-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70">
                    <span wire:loading.remove wire:target="provisionDefaultChart">Pastikan COA Default</span>
                    <span wire:loading wire:target="provisionDefaultChart">Menyiapkan COA...</span>
                </button>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-2">
            <span class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
                Setting COA & Mapping
            </span>
            <a href="{{ route('platform.admin.accounting.reports') }}" wire:navigate class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-white">
                Laporan & Jurnal
            </a>
        </div>

        <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Total Tenant</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($tenants->count()) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Tenant Terpilih</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $selectedTenant?->name ?? 'Belum dipilih' }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Akun COA</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($accounts->count()) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 {{ $missingMappingsCount === 0 ? 'bg-emerald-50' : 'bg-amber-50' }} p-4">
                <p class="text-xs uppercase tracking-wide {{ $missingMappingsCount === 0 ? 'text-emerald-700' : 'text-amber-700' }}">Mapping Belum Terisi</p>
                <p class="mt-2 text-2xl font-semibold {{ $missingMappingsCount === 0 ? 'text-emerald-900' : 'text-amber-900' }}">{{ number_format($missingMappingsCount) }}</p>
            </div>
        </div>
    </div>

    @if (session('platform_admin_message'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-800 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Update Tersimpan</p>
            <p class="mt-1">{{ session('platform_admin_message') }}</p>
        </div>
    @endif

    @if (session('platform_admin_error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-800 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-rose-700">Perlu Perhatian</p>
            <p class="mt-1">{{ session('platform_admin_error') }}</p>
        </div>
    @endif

    <section class="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
        <div class="rounded-[32px] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-700">Daftar Tenant</p>
            <h2 class="mt-2 text-xl font-semibold text-slate-900">Pilih tenant yang akan diatur.</h2>
            <div class="mt-5 space-y-3">
                @foreach ($tenants as $tenant)
                    <button type="button" wire:click="selectTenant({{ $tenant->id }})" class="w-full rounded-2xl border px-4 py-3 text-left transition {{ (string) $tenant->id === $selectedTenantId ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-slate-50 text-slate-800 hover:bg-slate-100' }}">
                        <p class="text-sm font-semibold">{{ $tenant->name }}</p>
                        <p class="mt-1 text-xs {{ (string) $tenant->id === $selectedTenantId ? 'text-slate-300' : 'text-slate-500' }}">{{ $tenant->code }} · {{ $tenant->owner_email }}</p>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="space-y-6">
            <section class="rounded-[32px] border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-5 md:px-6">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-700">Mapping Akun</p>
                            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Akun default untuk engine jurnal otomatis.</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-500">Diskon diarahkan ke akun contra revenue, sedangkan redeem diarahkan ke akun beban promosi. Nomor akun bisa beda tiap tenant karena mapping ini diset via UI.</p>
                        </div>
                        @if ($missingMappingsCount > 0)
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">{{ $missingMappingsCount }} mapping belum lengkap</span>
                        @else
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Mapping lengkap</span>
                        @endif
                    </div>
                </div>

                <form wire:submit="saveMappings" class="p-5 md:p-6">
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($mappingSummary as $mapping)
                            <div>
                                <label for="mapping-{{ $mapping['key'] }}" class="text-sm font-semibold text-slate-900">{{ $mapping['label'] }}</label>
                                <select id="mapping-{{ $mapping['key'] }}" wire:model="mappingInputs.{{ $mapping['key'] }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                    <option value="">Pilih akun</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->code }} · {{ $account->name }}</option>
                                    @endforeach
                                </select>
                                @error('mappingInputs.'.$mapping['key'])<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveMappings" class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70">
                            <span wire:loading.remove wire:target="saveMappings">Simpan Mapping</span>
                            <span wire:loading wire:target="saveMappings">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </section>

            <section class="grid gap-6 2xl:grid-cols-[minmax(0,1.2fr)_380px]">
                <div class="rounded-[32px] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-700">Master COA</p>
                            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Daftar akun tenant terpilih.</h2>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ number_format($accounts->count()) }} akun</span>
                    </div>

                    <div class="mt-5 overflow-hidden rounded-3xl border border-slate-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Kode</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Nama Akun</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Kategori</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Saldo Normal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse ($accounts as $account)
                                        <tr>
                                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $account->code }}</td>
                                            <td class="px-4 py-3 text-slate-700">{{ $account->name }}</td>
                                            <td class="px-4 py-3 text-slate-500">{{ str($account->category)->replace('_', ' ')->title() }}</td>
                                            <td class="px-4 py-3 text-slate-500">{{ str($account->normal_balance)->title() }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada akun untuk tenant ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="rounded-[32px] border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-700">Tambah Akun</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Buat COA tambahan bila tenant membutuhkannya.</h2>

                    <form wire:submit="createAccount" class="mt-5 space-y-4">
                        <div>
                            <label for="account-code" class="text-sm font-semibold text-slate-900">Kode Akun</label>
                            <input id="account-code" wire:model.live="accountForm.code" type="text" placeholder="misalnya 6150" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                            @error('accountForm.code')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="account-name" class="text-sm font-semibold text-slate-900">Nama Akun</label>
                            <input id="account-name" wire:model.live="accountForm.name" type="text" placeholder="misalnya Beban Admin QRIS" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                            @error('accountForm.name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="account-category" class="text-sm font-semibold text-slate-900">Kategori</label>
                            <select id="account-category" wire:model.live="accountForm.category" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                <option value="asset">Asset</option>
                                <option value="liability">Liability</option>
                                <option value="equity">Equity</option>
                                <option value="revenue">Revenue</option>
                                <option value="contra_revenue">Contra Revenue</option>
                                <option value="cogs">COGS</option>
                                <option value="expense">Expense</option>
                            </select>
                            @error('accountForm.category')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="account-normal-balance" class="text-sm font-semibold text-slate-900">Saldo Normal</label>
                            <select id="account-normal-balance" wire:model.live="accountForm.normal_balance" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                <option value="debit">Debit</option>
                                <option value="credit">Credit</option>
                            </select>
                            @error('accountForm.normal_balance')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                            <input wire:model.live="accountForm.allow_manual_entry" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                            <span class="font-medium">Boleh dipakai jurnal manual</span>
                        </label>

                        <button type="submit" wire:loading.attr="disabled" wire:target="createAccount" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70">
                            <span wire:loading.remove wire:target="createAccount">Tambah Akun</span>
                            <span wire:loading wire:target="createAccount">Menyimpan Akun...</span>
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </section>
</div>
