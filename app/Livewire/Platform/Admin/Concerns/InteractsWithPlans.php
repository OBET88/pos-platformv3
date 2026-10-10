<?php

namespace App\Livewire\Platform\Admin\Concerns;

use App\Models\Platform\Plan;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait InteractsWithPlans
{
    protected const SYSTEM_FEATURE_KEYS = [
        'menu_subscription_center',
        'subscription_center',
    ];

    protected const VISIBLE_PLAN_PRESET_KEYS = [
        'basic',
        'pro',
        'plus_plus',
    ];

    protected const LEGACY_COMPATIBILITY_MAP = [
        'menu_dashboard' => ['pos'],
        'menu_subscription_center' => ['subscription_center'],
        'menu_reports' => ['basic_reports'],
        'menu_product_reports' => ['basic_reports'],
        'menu_stock_in' => ['stock'],
        'menu_stock_out' => ['stock'],
        'menu_marketplace_orders' => ['stock'],
        'menu_stock_conversion' => ['stock'],
        'menu_stock_transfer' => ['stock'],
        'menu_finished_stock_opname' => ['stock'],
        'menu_raw_stock_opname' => ['stock'],
        'menu_raw_stock_reports' => ['stock'],
        'menu_production_make' => ['production'],
        'menu_shift_history_self' => ['pos'],
        'menu_daily_report_self' => ['basic_reports'],
        'menu_products' => ['pos'],
        'menu_categories' => ['pos'],
        'menu_floor_map_manager' => ['pos'],
        'menu_customer_display' => ['pos'],
        'menu_users' => ['pos'],
        'menu_finished_vs_sold' => ['advanced_reports'],
        'menu_recipes' => ['production'],
        'menu_kitchen_settings' => ['production', 'pos'],
        'menu_roles' => ['pos'],
        'menu_owner_cashier_recap' => ['advanced_reports'],
        'menu_shift_history_admin' => ['pos'],
        'menu_daily_report_admin' => ['basic_reports'],
        'menu_audit_resume' => ['audit'],
        'menu_accounting_report' => ['accounting', 'accounting_report'],
        'menu_accounting_cash_flow' => [],
        'menu_accounting_owner_summary' => [],
        'menu_accounting_admin_entry' => ['accounting', 'accounting_admin_entry'],
        'menu_accounting_manual_journal' => ['accounting', 'accounting_manual_journal'],
        'menu_accounting_settings' => ['accounting', 'accounting_coa'],
        'menu_tax_settings' => ['accounting', 'accounting_coa', 'pos'],
        'menu_tax_reports' => ['accounting', 'accounting_report', 'basic_reports'],
        'menu_branding' => ['pos'],
        'menu_promo_settings' => ['promotions'],
        'menu_promo_vouchers' => ['promotions'],
        'menu_membership' => ['menu_users', 'pos'],
        'membership_points' => ['menu_membership', 'menu_users', 'pos'],
        'membership_redeem' => ['membership_points', 'menu_membership', 'menu_users', 'pos'],
        'membership_tier' => ['membership_points', 'menu_membership', 'menu_users', 'pos'],
    ];

    protected const PLAN_PRESETS = [
        'trial' => [
            'label' => 'Trial',
            'description' => 'Template percobaan ringan untuk tenant baru: cukup untuk coba POS, master data, user dasar, dan laporan harian sederhana.',
            'badge' => 'Onboarding',
            'price_hint' => 'Saran harga: gratis atau bundle onboarding 7-14 hari.',
            'ideal_for' => 'Cocok untuk prospek baru yang masih validasi alur kasir, setup produk, dan kualitas onboarding.',
            'limits' => [
                'max_businesses' => 1,
                'max_branches' => 1,
                'max_users' => 3,
            ],
            'features' => [
                'menu_dashboard',
                'menu_products',
                'menu_categories',
                'menu_users',
                'menu_shift_history_self',
                'menu_daily_report_self',
                'menu_branding',
                'menu_subscription_center',
                'pos',
                'subscription_center',
            ],
        ],
        'basic' => [
            'label' => 'Basic',
            'description' => 'Template operasional dasar untuk outlet aktif: POS, laporan inti, stok dasar, user, dan branding tenant.',
            'badge' => 'Paling Laku',
            'price_hint' => 'Saran harga: Rp99.000-Rp199.000 per bulan.',
            'ideal_for' => 'Cocok untuk outlet tunggal yang butuh POS stabil, stok dasar, dan laporan operasional inti.',
            'limits' => [
                'max_businesses' => 1,
                'max_branches' => 2,
                'max_users' => 8,
            ],
            'features' => [
                'menu_dashboard',
                'menu_subscription_center',
                'menu_reports',
                'menu_product_reports',
                'menu_stock_in',
                'menu_stock_out',
                'menu_stock_conversion',
                'menu_shift_history_self',
                'menu_daily_report_self',
                'menu_products',
                'menu_categories',
                'menu_users',
                'menu_membership',
                'membership_points',
                'menu_shift_history_admin',
                'menu_daily_report_admin',
                'menu_branding',
                'subscription_center',
                'pos',
                'stock',
                'basic_reports',
            ],
        ],
        'pro' => [
            'label' => 'Pro',
            'description' => 'Template growth untuk bisnis yang butuh stok lebih lengkap, produksi, promo, role permission, floor map, dan audit aktivitas.',
            'badge' => 'Upsell',
            'price_hint' => 'Saran harga: Rp249.000-Rp499.000 per bulan.',
            'ideal_for' => 'Cocok untuk tenant dine-in atau multi-shift yang mulai butuh kontrol tim, promo, dan produksi lebih rapi.',
            'limits' => [
                'max_businesses' => 3,
                'max_branches' => 10,
                'max_users' => 25,
            ],
            'features' => [
                'menu_dashboard',
                'menu_subscription_center',
                'menu_reports',
                'menu_product_reports',
                'menu_stock_in',
                'menu_stock_out',
                'menu_stock_conversion',
                'menu_stock_transfer',
                'menu_finished_stock_opname',
                'menu_raw_stock_opname',
                'menu_raw_stock_reports',
                'menu_production_make',
                'menu_shift_history_self',
                'menu_daily_report_self',
                'menu_products',
                'menu_categories',
                'menu_floor_map_manager',
                'menu_users',
                'menu_membership',
                'membership_points',
                'membership_redeem',
                'menu_finished_vs_sold',
                'menu_recipes',
                'menu_roles',
                'menu_owner_cashier_recap',
                'menu_shift_history_admin',
                'menu_daily_report_admin',
                'menu_audit_resume',
                'menu_branding',
                'menu_promo_settings',
                'menu_promo_vouchers',
                'menu_tax_settings',
                'menu_tax_reports',
                'subscription_center',
                'pos',
                'stock',
                'basic_reports',
                'advanced_reports',
                'production',
                'audit',
                'promotions',
            ],
        ],
        'enterprise' => [
            'label' => 'Enterprise',
            'description' => 'Template paling lengkap: seluruh modul Pro ditambah area akuntansi penuh untuk tenant multi-branch atau kebutuhan finance yang lebih serius.',
            'badge' => 'Enterprise Only',
            'price_hint' => 'Saran harga: mulai Rp799.000 per bulan atau custom quotation.',
            'ideal_for' => 'Cocok untuk grup usaha multi-branch, tenant dengan tim finance internal, atau kebutuhan approval enterprise.',
            'limits' => [
                'max_businesses' => 999,
                'max_branches' => 999,
                'max_users' => 999,
            ],
            'features' => [
                'menu_dashboard',
                'menu_subscription_center',
                'menu_reports',
                'menu_product_reports',
                'menu_stock_in',
                'menu_stock_out',
                'menu_stock_conversion',
                'menu_stock_transfer',
                'menu_finished_stock_opname',
                'menu_raw_stock_opname',
                'menu_raw_stock_reports',
                'menu_production_make',
                'menu_shift_history_self',
                'menu_daily_report_self',
                'menu_products',
                'menu_categories',
                'menu_floor_map_manager',
                'menu_users',
                'menu_membership',
                'membership_points',
                'membership_redeem',
                'membership_tier',
                'menu_finished_vs_sold',
                'menu_recipes',
                'menu_roles',
                'menu_owner_cashier_recap',
                'menu_shift_history_admin',
                'menu_daily_report_admin',
                'menu_audit_resume',
                'menu_accounting_report',
                'menu_accounting_cash_flow',
                'menu_accounting_owner_summary',
                'menu_accounting_admin_entry',
                'menu_accounting_manual_journal',
                'menu_accounting_settings',
                'menu_tax_settings',
                'menu_tax_reports',
                'menu_branding',
                'menu_promo_settings',
                'menu_promo_vouchers',
                'subscription_center',
                'pos',
                'stock',
                'basic_reports',
                'advanced_reports',
                'production',
                'accounting',
                'accounting_report',
                'accounting_coa',
                'accounting_admin_entry',
                'accounting_manual_journal',
                'audit',
                'promotions',
                'priority_support',
            ],
        ],
        'plus_plus' => [
            'label' => 'Plus +',
            'description' => 'Template lengkap untuk tenant yang butuh operasional Pro ditambah area akuntansi penuh dan kontrol bisnis yang lebih luas.',
            'badge' => 'Recommended',
            'price_hint' => 'Saran harga: mulai Rp799.000 per bulan atau custom quotation.',
            'ideal_for' => 'Cocok untuk tenant yang sudah butuh kontrol finance, audit, promo, dan operasional lintas cabang dalam satu paket.',
            'limits' => [
                'max_businesses' => 999,
                'max_branches' => 999,
                'max_users' => 999,
            ],
            'features' => [
                'menu_dashboard',
                'menu_subscription_center',
                'menu_reports',
                'menu_product_reports',
                'menu_stock_in',
                'menu_stock_out',
                'menu_marketplace_orders',
                'menu_stock_conversion',
                'menu_stock_transfer',
                'menu_finished_stock_opname',
                'menu_raw_stock_opname',
                'menu_raw_stock_reports',
                'menu_production_make',
                'menu_shift_history_self',
                'menu_daily_report_self',
                'menu_products',
                'menu_categories',
                'menu_floor_map_manager',
                'menu_users',
                'menu_membership',
                'membership_points',
                'membership_redeem',
                'membership_tier',
                'menu_finished_vs_sold',
                'menu_recipes',
                'menu_roles',
                'menu_owner_cashier_recap',
                'menu_shift_history_admin',
                'menu_daily_report_admin',
                'menu_audit_resume',
                'menu_accounting_report',
                'menu_accounting_cash_flow',
                'menu_accounting_owner_summary',
                'menu_accounting_admin_entry',
                'menu_accounting_manual_journal',
                'menu_accounting_settings',
                'menu_tax_settings',
                'menu_tax_reports',
                'menu_branding',
                'menu_promo_settings',
                'menu_promo_vouchers',
                'subscription_center',
                'pos',
                'stock',
                'basic_reports',
                'advanced_reports',
                'production',
                'accounting',
                'accounting_report',
                'accounting_coa',
                'accounting_admin_entry',
                'accounting_manual_journal',
                'audit',
                'promotions',
                'priority_support',
            ],
        ],
    ];

    protected const FEATURE_CATALOG = [
        'menu_dashboard' => 'Dashboard & POS',
        'menu_subscription_center' => 'Pusat Langganan',
        'menu_reports' => 'Laporan Umum',
        'menu_product_reports' => 'Rekap Produk',
        'menu_stock_in' => 'Stok Masuk',
        'menu_stock_out' => 'Stok Keluar',
        'menu_marketplace_orders' => 'Marketplace',
        'menu_stock_conversion' => 'Konversi / Repack Stok',
        'menu_stock_transfer' => 'Transfer Stok Gudang - Kios',
        'menu_finished_stock_opname' => 'Opname Stok Jadi',
        'menu_raw_stock_opname' => 'Opname Bahan Baku',
        'menu_raw_stock_reports' => 'Laporan Stok Bahan',
        'menu_production_make' => 'Produksi / Masak',
        'menu_shift_history_self' => 'Riwayat Shift Sendiri',
        'menu_daily_report_self' => 'Laporan Harian Sendiri',
        'menu_products' => 'Produk',
        'menu_categories' => 'Kategori',
        'menu_floor_map_manager' => 'Floor Map & Meja',
        'menu_customer_display' => 'Layar Pelanggan',
        'menu_users' => 'User & Organisasi',
        'menu_finished_vs_sold' => 'Perbandingan Produksi vs Jual',
        'menu_recipes' => 'Resep Produk',
        'menu_kitchen_settings' => 'Pengaturan Dapur',
        'menu_roles' => 'Role & Permission',
        'menu_owner_cashier_recap' => 'Rekap Kasir Owner',
        'menu_shift_history_admin' => 'Riwayat Shift Admin',
        'menu_daily_report_admin' => 'Laporan Harian Admin',
        'menu_audit_resume' => 'Audit Aktivitas',
        'menu_accounting_report' => 'Laporan Akuntansi',
        'menu_accounting_cash_flow' => 'Arus Kas',
        'menu_accounting_owner_summary' => 'Omzet & Kas',
        'menu_accounting_admin_entry' => 'Entry Keuangan Admin',
        'menu_accounting_manual_journal' => 'Jurnal Manual',
        'menu_accounting_settings' => 'Setting Akuntansi / COA',
        'menu_tax_settings' => 'Pengaturan Pajak & Service Charge',
        'menu_tax_reports' => 'Laporan Pajak & Service Charge',
        'menu_branding' => 'Branding Tenant',
        'menu_promo_settings' => 'Pengaturan Promo',
        'menu_promo_vouchers' => 'Voucher Promo',
        'menu_membership' => 'Menu Membership',
        'membership_points' => 'Membership Poin',
        'membership_redeem' => 'Redeem Poin Membership',
        'membership_tier' => 'Tier Membership',
        'subscription_center' => 'Kompatibilitas Lama: Subscription Center',
        'pos' => 'Kompatibilitas Lama: POS / Kasir',
        'stock' => 'Kompatibilitas Lama: Manajemen Stok',
        'basic_reports' => 'Kompatibilitas Lama: Laporan Dasar',
        'advanced_reports' => 'Kompatibilitas Lama: Laporan Lanjutan',
        'production' => 'Kompatibilitas Lama: Produksi',
        'accounting' => 'Kompatibilitas Lama: Akuntansi',
        'accounting_report' => 'Kompatibilitas Lama: Laporan Akuntansi',
        'accounting_coa' => 'Kompatibilitas Lama: COA / Mapping',
        'accounting_admin_entry' => 'Kompatibilitas Lama: Entry Keuangan Admin',
        'accounting_manual_journal' => 'Kompatibilitas Lama: Jurnal Manual',
        'audit' => 'Kompatibilitas Lama: Audit Aktivitas',
        'promotions' => 'Kompatibilitas Lama: Promo & Voucher',
        'priority_support' => 'Kompatibilitas Lama: Priority Support',
    ];

    protected const FEATURE_GROUPS = [
        'Operasional' => [
            'description' => 'Menu tenant untuk aktivitas harian operasional.',
            'badge' => 'Core Module',
            'business_context' => 'Umumnya menjadi nilai jual utama setelah POS aktif, terutama untuk owner yang ingin memantau stok dan performa harian.',
            'sell_when' => 'Tawarkan saat tenant mulai minta kontrol stok, laporan outlet, atau workflow produksi dasar.',
            'features' => [
                'menu_reports',
                'menu_product_reports',
                'menu_stock_in',
                'menu_stock_out',
                'menu_marketplace_orders',
                'menu_stock_conversion',
                'menu_stock_transfer',
                'menu_finished_stock_opname',
                'menu_raw_stock_opname',
                'menu_raw_stock_reports',
                'menu_production_make',
                'menu_shift_history_self',
                'menu_daily_report_self',
                'menu_tax_reports',
            ],
        ],
        'Utama' => [
            'description' => 'Menu utama yang muncul pertama kali setelah tenant login.',
            'badge' => 'Wajib',
            'business_context' => 'Biasanya wajib di hampir semua paket karena memengaruhi pengalaman pertama tenant setelah login.',
            'sell_when' => 'Pertahankan di semua paket kecuali Anda sedang membuat bundle eksperimen yang sangat terbatas.',
            'features' => [
                'menu_dashboard',
                'menu_subscription_center',
            ],
        ],
        'Master Data' => [
            'description' => 'Menu admin tenant untuk pengelolaan master data inti.',
            'badge' => 'Growth Driver',
            'business_context' => 'Penting untuk tenant yang mengelola katalog menu, resep, dan area layanan seperti meja dine-in.',
            'sell_when' => 'Naikkan nilai paket saat tenant punya banyak SKU, butuh meja, atau mulai pakai resep produksi.',
            'features' => [
                'menu_products',
                'menu_categories',
                'menu_floor_map_manager',
                'menu_customer_display',
                'menu_finished_vs_sold',
                'menu_recipes',
                'menu_kitchen_settings',
            ],
        ],
        'User & Kontrol' => [
            'description' => 'Menu pengelolaan user, role, dan kontrol operasional admin.',
            'badge' => 'Operational Control',
            'business_context' => 'Grup ini terasa bernilai saat owner mulai delegasi ke kasir, supervisor, atau admin cabang.',
            'sell_when' => 'Tawarkan untuk bisnis dengan lebih dari satu shift, lebih dari satu peran kerja, atau kebutuhan audit aktivitas.',
            'features' => [
                'menu_users',
                'menu_roles',
                'menu_owner_cashier_recap',
                'menu_shift_history_admin',
                'menu_daily_report_admin',
                'menu_audit_resume',
            ],
        ],
        'Akuntansi' => [
            'description' => 'Menu akuntansi dibuat granular agar paket bisa hanya berisi bagian yang dibutuhkan, misalnya COA saja.',
            'badge' => 'Premium Add-on',
            'business_context' => 'Nilainya tinggi, tapi tidak semua tenant membutuhkannya. Cocok dijual sebagai pembeda paket premium atau add-on finance.',
            'sell_when' => 'Tawarkan saat tenant mulai meminta jurnal manual, laporan finance, atau integrasi proses pembukuan internal.',
            'features' => [
                'menu_accounting_report',
                'menu_accounting_cash_flow',
                'menu_accounting_owner_summary',
                'menu_accounting_admin_entry',
                'menu_accounting_manual_journal',
                'menu_accounting_settings',
                'menu_tax_settings',
                'menu_tax_reports',
                'accounting_coa',
                'accounting_report',
                'accounting_admin_entry',
                'accounting_manual_journal',
            ],
        ],
        'Branding & Promo' => [
            'description' => 'Menu untuk branding tenant dan pengaturan promo.',
            'badge' => 'Upsell Cepat',
            'business_context' => 'Sering efektif sebagai modul upsell karena mudah dipahami owner non-teknis dan terasa langsung manfaatnya.',
            'sell_when' => 'Tawarkan saat tenant mulai fokus ke kampanye marketing, identitas brand, atau voucher repeat-order.',
            'features' => [
                'menu_branding',
                'menu_promo_settings',
                'menu_promo_vouchers',
            ],
        ],
        'Membership' => [
            'description' => 'Modul loyalti pelanggan untuk daftar member, pengumpulan poin, redeem poin, dan tier benefit.',
            'badge' => 'Retention Driver',
            'business_context' => 'Cocok untuk tenant yang mulai fokus ke repeat order, pelanggan loyal, dan promo yang lebih terarah.',
            'sell_when' => 'Tawarkan saat tenant meminta program loyalti, poin pelanggan, atau diskon khusus member.',
            'features' => [
                'menu_membership',
                'membership_points',
                'membership_redeem',
                'membership_tier',
            ],
        ],
        'Kompatibilitas Legacy' => [
            'description' => 'Key lama tetap tersedia agar paket lama tetap terbaca. Gunakan ini hanya jika masih butuh bundle akses lama.',
            'badge' => 'Teknis',
            'business_context' => 'Area ini lebih teknis dan biasanya tidak perlu dijelaskan ke calon tenant sebagai nilai jual utama.',
            'sell_when' => 'Gunakan terutama saat migrasi tenant lama atau saat aplikasi tenant masih membaca bundle legacy tertentu.',
            'features' => [
                'subscription_center',
                'pos',
                'stock',
                'basic_reports',
                'advanced_reports',
                'production',
                'accounting',
                'accounting_report',
                'accounting_coa',
                'accounting_admin_entry',
                'accounting_manual_journal',
                'audit',
                'promotions',
                'priority_support',
            ],
        ],
    ];

    public function planPresets(): array
    {
        $plansByCode = Plan::query()
            ->whereIn('code', self::VISIBLE_PLAN_PRESET_KEYS)
            ->get([
                'id',
                'code',
                'name',
                'features_json',
                'max_businesses',
                'max_branches',
                'max_users',
            ])
            ->keyBy('code');

        return collect(self::VISIBLE_PLAN_PRESET_KEYS)
            ->filter(fn (string $presetKey) => array_key_exists($presetKey, self::PLAN_PRESETS))
            ->mapWithKeys(function (string $presetKey) use ($plansByCode): array {
                $preset = self::PLAN_PRESETS[$presetKey];
                /** @var Plan|null $plan */
                $plan = $plansByCode->get($presetKey);

                return [$presetKey => [
                    ...$preset,
                    'label' => (string) ($plan?->name ?? $preset['label'] ?? Str::headline($presetKey)),
                    'features' => collect($plan?->features_json ?? $preset['features'] ?? [])
                        ->filter(fn ($feature) => is_string($feature) && trim($feature) !== '')
                        ->map(fn (string $feature) => trim($feature))
                        ->unique()
                        ->values()
                        ->all(),
                    'limits' => [
                        'max_businesses' => (int) ($plan?->max_businesses ?? $preset['limits']['max_businesses'] ?? 1),
                        'max_branches' => (int) ($plan?->max_branches ?? $preset['limits']['max_branches'] ?? 1),
                        'max_users' => (int) ($plan?->max_users ?? $preset['limits']['max_users'] ?? 3),
                    ],
                    'plan_id' => $plan?->id,
                    'source' => $plan ? 'database' : 'fallback',
                ]];
            })
            ->all();
    }

    public function planPresetDefaults(string $presetKey): array
    {
        $preset = $this->planPresets()[$presetKey] ?? null;

        return [
            'max_businesses' => (int) ($preset['limits']['max_businesses'] ?? 1),
            'max_branches' => (int) ($preset['limits']['max_branches'] ?? 1),
            'max_users' => (int) ($preset['limits']['max_users'] ?? 3),
        ];
    }

    public function featureCatalog(): array
    {
        return self::FEATURE_CATALOG;
    }

    public function packageFeatureCatalog(): array
    {
        return collect(self::FEATURE_CATALOG)
            ->reject(fn (string $label, string $featureKey) => $this->isSystemFeatureKey($featureKey))
            ->all();
    }

    public function featureGroups(): array
    {
        return collect(self::FEATURE_GROUPS)
            ->map(function (array $group): array {
                return [
                    'description' => $group['description'],
                    'badge' => $group['badge'] ?? null,
                    'business_context' => $group['business_context'] ?? null,
                    'sell_when' => $group['sell_when'] ?? null,
                    'features' => collect($group['features'])
                        ->reject(fn (string $feature) => $this->isSystemFeatureKey($feature))
                        ->filter(fn (string $feature) => array_key_exists($feature, self::FEATURE_CATALOG))
                        ->mapWithKeys(fn (string $feature) => [$feature => self::FEATURE_CATALOG[$feature]])
                        ->all(),
                ];
            })
            ->all();
    }

    public function featureLabel(string $featureKey): string
    {
        return self::FEATURE_CATALOG[$featureKey] ?? Str::headline(str_replace('_', ' ', $featureKey));
    }

    public function systemFeatureLabels(): array
    {
        return collect(self::SYSTEM_FEATURE_KEYS)
            ->filter(fn (string $featureKey) => array_key_exists($featureKey, self::FEATURE_CATALOG))
            ->mapWithKeys(fn (string $featureKey) => [$featureKey => self::FEATURE_CATALOG[$featureKey]])
            ->all();
    }

    public function packageFeatureCount(array|Collection $features): int
    {
        return $this->packageFeatureKeys($features)->count();
    }

    public function packageFeatureKeys(array|Collection $features): Collection
    {
        return collect($features)
            ->filter(fn ($feature) => is_string($feature) && trim($feature) !== '')
            ->reject(fn (string $feature) => $this->isSystemFeatureKey($feature))
            ->values();
    }

    public function filteredFeatureGroupsForPlan(int $planId, array $planSearchInputs): array
    {
        $search = Str::of($planSearchInputs[$planId] ?? '')
            ->lower()
            ->trim()
            ->toString();

        return collect($this->featureGroups())
            ->map(function (array $group) use ($search): array {
                if ($search === '') {
                    return $group;
                }

                $filteredFeatures = collect($group['features'])
                    ->filter(function (string $label, string $key) use ($search): bool {
                        return Str::of($label.' '.$key)
                            ->lower()
                            ->contains($search);
                    })
                    ->all();

                return [
                    'description' => $group['description'],
                    'badge' => $group['badge'] ?? null,
                    'business_context' => $group['business_context'] ?? null,
                    'sell_when' => $group['sell_when'] ?? null,
                    'features' => $filteredFeatures,
                ];
            })
            ->filter(fn (array $group) => ! empty($group['features']))
            ->all();
    }

    protected function isSystemFeatureKey(string $featureKey): bool
    {
        return in_array($featureKey, self::SYSTEM_FEATURE_KEYS, true);
    }

    protected function normalizeCustomFeatures(string $rawInput): Collection
    {
        return collect(preg_split('/[\r\n,]+/', $rawInput) ?: [])
            ->map(function (string $feature): string {
                return Str::of($feature)
                    ->trim()
                    ->lower()
                    ->replaceMatches('/[^a-z0-9\s_-]/', '')
                    ->replace(' ', '_')
                    ->replaceMatches('/_+/', '_')
                    ->trim('_')
                    ->toString();
            })
            ->filter()
            ->values();
    }

    protected function expandLegacyCompatibility(Collection $selectedFeatures): Collection
    {
        $derivedLegacyFeatures = $selectedFeatures
            ->flatMap(function (string $feature): array {
                return self::LEGACY_COMPATIBILITY_MAP[$feature] ?? [];
            });

        return $selectedFeatures
            ->merge($derivedLegacyFeatures)
            ->filter(fn ($feature) => is_string($feature) && array_key_exists($feature, self::FEATURE_CATALOG))
            ->unique()
            ->values();
    }

    protected function fillPlanInputs(Plan $plan, array &$planFeatureInputs, array &$planCustomFeatureInputs, array &$planSearchInputs, array &$planMetaInputs, array &$planDeactivationConfirmations): void
    {
        $features = collect($plan->features_json ?? [])
            ->filter(fn ($feature) => is_string($feature) && trim($feature) !== '')
            ->map(fn (string $feature) => trim($feature))
            ->unique()
            ->values();

        $planFeatureInputs[$plan->id] = $features
            ->filter(fn (string $feature) => array_key_exists($feature, self::FEATURE_CATALOG))
            ->values()
            ->all();

        $planCustomFeatureInputs[$plan->id] = $features
            ->reject(fn (string $feature) => array_key_exists($feature, self::FEATURE_CATALOG))
            ->implode(PHP_EOL);

        $planSearchInputs[$plan->id] = $planSearchInputs[$plan->id] ?? '';
        $planDeactivationConfirmations[$plan->id] = $planDeactivationConfirmations[$plan->id] ?? false;

        $planMetaInputs[$plan->id] = [
            'code' => $plan->code,
            'name' => $plan->name,
            'price' => (string) $plan->price,
            'billing_cycle' => $plan->billing_cycle,
            'max_businesses' => $plan->max_businesses,
            'max_branches' => $plan->max_branches,
            'max_users' => $plan->max_users,
        ];
    }

    protected function plansQuery()
    {
        return Plan::query()
            ->withCount('subscriptions')
            ->withCount([
                'subscriptions as active_subscriptions_count' => function ($query) {
                    $query
                        ->whereIn('status', ['trial', 'active'])
                        ->where(function ($dateQuery) {
                            $dateQuery
                                ->where('expired_at', '>', now())
                                ->orWhere(function ($trialQuery) {
                                    $trialQuery
                                        ->whereNull('expired_at')
                                        ->where('trial_ends_at', '>', now());
                                })
                                ->orWhere(function ($openEndedQuery) {
                                    $openEndedQuery
                                        ->whereNull('expired_at')
                                        ->whereNull('trial_ends_at');
                                });
                        });
                },
            ])
            ->orderByDesc('is_active')
            ->orderBy('price')
            ->orderBy('id');
    }

    protected function activeSubscriptionCountForPlan(Plan $plan): int
    {
        if (isset($plan->active_subscriptions_count)) {
            return (int) $plan->active_subscriptions_count;
        }

        return (int) $plan->subscriptions()
            ->whereIn('status', ['trial', 'active'])
            ->where(function ($dateQuery) {
                $dateQuery
                    ->where('expired_at', '>', now())
                    ->orWhere(function ($trialQuery) {
                        $trialQuery
                            ->whereNull('expired_at')
                            ->where('trial_ends_at', '>', now());
                    })
                    ->orWhere(function ($openEndedQuery) {
                        $openEndedQuery
                            ->whereNull('expired_at')
                            ->whereNull('trial_ends_at');
                    });
            })
            ->count();
    }

    protected function nextDuplicateCode(string $baseCode): string
    {
        $baseCode = Str::lower($baseCode).'_copy';
        $candidate = $baseCode;
        $suffix = 2;

        while (Plan::query()->where('code', $candidate)->exists()) {
            $candidate = $baseCode.'_'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
