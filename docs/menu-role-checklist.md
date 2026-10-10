# Checklist Pembagian Menu Tenant

Dokumen ini dirapikan berdasarkan kondisi kode saat ini di tenant app:

- sidebar tenant di `../poslive/config/sidebar.php`
- seed role dan permission di `../poslive/database/seeders/RolePermissionSeeder.php`
- mapping fitur paket di `app/Livewire/Platform/Admin/Concerns/InteractsWithPlans.php`

## Kondisi Aktual

- [x] Sidebar tenant dibagi menjadi 3 section utama: `Utama`, `Operasional`, `Admin`.
- [x] Visibilitas menu ditentukan oleh 2 lapis: `feature` paket dan `permission` role.
- [x] Role sistem default yang sudah ada: `administrator`, `owner`, `admin`, `kasir`.
- [x] Bootstrap owner tenant dari platform sudah default ke role `owner`.
- [x] Role `administrator` masih menjadi bypass penuh lewat `Gate::before(...)`.
- [x] `owner` sudah diposisikan sebagai role bisnis tertinggi yang eksplisit, terpisah dari bypass `administrator`.

## Daftar Menu Aktual

### Utama

- [x] `Home`
- [x] `POS`
- [x] `Langganan`
- [x] `Akun`

### Operasional

- [x] `Laporan`
- [x] `Rekap Produk`
- [x] `Stok Masuk`
- [x] `Stok Keluar`
- [x] `Laporan Stok Mentah`
- [x] `Produksi / Masak`
- [x] `Shift History Self`
- [x] `Laporan Harian Self`

### Admin

- [x] `Produk`
- [x] `Kategori`
- [x] `Floor Map Manager`
- [x] `User`
- [x] `Usaha & Cabang`
- [x] `Produksi / Masak vs Jual`
- [x] `Setting Resep`
- [x] `Role Permission`
- [x] `Rekap Kasir Owner`
- [x] `Shift History [Admin]`
- [x] `Laporan Harian [Admin]`
- [x] `Audit Aktivitas`
- [x] `Laporan Akuntansi`
- [x] `Entry Keuangan Admin`
- [x] `Jurnal Manual`
- [x] `Setting Akuntansi`
- [x] `Branding`
- [x] `Promo Voucher`
- [x] `Daftar Voucher`

## Saran Struktur Role

### 1. `kasir`

Fokus: transaksi dan laporan pribadi.

Menu inti:

- [x] `Home`
- [x] `POS`
- [x] `Akun`
- [x] `Shift History Self`
- [x] `Laporan Harian Self`

Menu operasional yang masih aman untuk default saat ini:

- [x] `Laporan`
- [x] `Rekap Produk`
- [x] `Stok Masuk`
- [x] `Stok Keluar`
- [x] `Laporan Stok Mentah`
- [x] `Produksi / Masak`

Menu yang sebaiknya tetap disembunyikan:

- [x] Semua menu section `Admin`
- [x] `Langganan`

Catatan:

- Role `kasir` sekarang masih cukup lebar karena juga memegang stok dan produksi.
- Kalau mau lebih rapi, area gudang dan produksi sebaiknya dipisah ke role turunan, bukan dibebankan ke `kasir`.

### 2. `admin`

Fokus: operasional harian, master data, tim, dan kontrol toko.

Menu yang disarankan:

- [x] Semua akses `kasir`
- [x] `Produk`
- [x] `Kategori`
- [x] `Floor Map Manager`
- [x] `User`
- [x] `Usaha & Cabang`
- [x] `Produksi / Masak vs Jual`
- [x] `Setting Resep`
- [x] `Role Permission`
- [x] `Shift History [Admin]`
- [x] `Laporan Harian [Admin]`
- [x] `Branding`
- [x] `Promo Voucher`
- [x] `Daftar Voucher`

Menu yang sebaiknya tidak default untuk `admin`:

- [ ] `Langganan`
- [ ] `Rekap Kasir Owner`
- [ ] `Audit Aktivitas`
- [ ] `Laporan Akuntansi`
- [ ] `Entry Keuangan Admin`
- [ ] `Jurnal Manual`
- [ ] `Setting Akuntansi`

Catatan:

- Struktur seed saat ini sudah mengikuti arah ini: `admin` belum diberi akses subscription, audit, owner recap, dan akuntansi.
- Ini pembagian yang aman untuk tenant admin operasional.

### 3. `owner`

Fokus: monitoring bisnis, keputusan, kontrol finansial, dan approval.

Menu yang disarankan:

- [x] Semua akses `admin`
- [x] `Langganan`
- [x] `Rekap Kasir Owner`
- [x] `Audit Aktivitas`
- [x] `Laporan Akuntansi`
- [x] `Entry Keuangan Admin`
- [x] `Jurnal Manual`
- [x] `Setting Akuntansi`

Catatan:

- Seed saat ini juga sudah mengikuti arah ini.
- `owner` sudah tepat dijadikan role bisnis tertinggi.
- `owner` tetap lebih aman daripada memakai `administrator`, karena aksesnya masih lewat permission eksplisit.

### 4. `administrator`

Fokus: bypass internal dan emergency access.

Aturan yang disarankan:

- [x] Jangan dipakai sebagai role operasional harian tenant
- [x] Simpan untuk kebutuhan super access / recovery
- [x] Jangan dijadikan acuan pembagian menu bisnis

Catatan:

- Saat ini `administrator` bypass semua Gate.
- Kalau role ini dipakai untuk owner bisnis, batas antar-menu dan antar-permission jadi tidak berarti.

## Matriks Menu x Role

Legend:

- `Y` = default boleh tampil
- `N` = default jangan tampil
- `O` = opsional, tergantung tenant atau kebutuhan cabang

| Menu | kasir | admin | owner | administrator |
| --- | --- | --- | --- | --- |
| Home | Y | Y | Y | Y |
| POS | Y | Y | Y | Y |
| Langganan | N | N | Y | Y |
| Akun | Y | Y | Y | Y |
| Laporan | Y | Y | Y | Y |
| Rekap Produk | Y | Y | Y | Y |
| Stok Masuk | O | Y | Y | Y |
| Stok Keluar | O | Y | Y | Y |
| Laporan Stok Mentah | O | Y | Y | Y |
| Produksi / Masak | O | Y | Y | Y |
| Shift History Self | Y | Y | Y | Y |
| Laporan Harian Self | Y | Y | Y | Y |
| Produk | N | Y | Y | Y |
| Kategori | N | Y | Y | Y |
| Floor Map Manager | N | Y | Y | Y |
| User | N | Y | Y | Y |
| Usaha & Cabang | N | Y | Y | Y |
| Produksi / Masak vs Jual | N | Y | Y | Y |
| Setting Resep | N | Y | Y | Y |
| Role Permission | N | Y | Y | Y |
| Rekap Kasir Owner | N | N | Y | Y |
| Shift History [Admin] | N | Y | Y | Y |
| Laporan Harian [Admin] | N | Y | Y | Y |
| Audit Aktivitas | N | N | Y | Y |
| Laporan Akuntansi | N | N | Y | Y |
| Entry Keuangan Admin | N | N | Y | Y |
| Jurnal Manual | N | N | Y | Y |
| Setting Akuntansi | N | N | Y | Y |
| Branding | N | Y | Y | Y |
| Promo Voucher | N | Y | Y | Y |
| Daftar Voucher | N | Y | Y | Y |

## Saran Role Turunan

Kalau mau pembagian lebih presisi dari sekadar `kasir`, `admin`, `owner`, ini struktur yang paling masuk akal:

- `kasir`
  Fokus POS, laporan diri, transaksi harian.
- `gudang`
  Fokus `Stok Masuk`, `Stok Keluar`, `Laporan Stok Mentah`.
- `produksi`
  Fokus `Produksi / Masak`, `Setting Resep`, opsional `Produksi / Masak vs Jual`.
- `supervisor`
  Fokus laporan operasional dan monitoring shift tanpa akses finansial sensitif.

Catatan:

- Role turunan ini belum menjadi role sistem default.
- Tetapi model permission sekarang sudah cukup siap untuk mendukungnya.

## Temuan Yang Perlu Dirapikan

- [ ] `Usaha & Cabang` masih memakai feature `menu_users`, jadi paket belum bisa memisahkan manajemen user dan organisasi.
- [ ] `Entry Keuangan Admin`, `Jurnal Manual`, dan `Setting Akuntansi` masih berbagi permission `manage_accounting_settings`, jadi kontrol akuntansi belum granular.
- [ ] `kasir` masih terlalu lebar karena punya stok dan produksi sekaligus.
- [ ] Dokumen lama yang menyebut `owner` belum ada sebaiknya tidak dipakai lagi sebagai acuan.

## Checklist Implementasi Lanjutan

- [ ] Buat feature baru khusus `menu_organization` agar `Usaha & Cabang` tidak ikut `menu_users`
- [ ] Pecah permission akuntansi menjadi minimal:
- [ ] `view_accounting_reports`
- [ ] `manage_accounting_settings`
- [ ] `manage_accounting_admin_entry`
- [ ] `manage_accounting_manual_journal`
- [ ] `reverse_accounting_admin_entry`
- [ ] Pertimbangkan role turunan `gudang` dan `produksi`
- [ ] Uji kombinasi 4 akun minimal: `kasir`, `admin`, `owner`, `administrator`
- [ ] Pastikan sidebar tetap konsisten dengan middleware route `can:` dan `subscription.feature:`

## Risiko Yang Perlu Dihindari

- [ ] Jangan samakan `owner` dengan `administrator`
- [ ] Jangan mengandalkan sidebar saja untuk keamanan
- [ ] Jangan memberi akses akuntansi penuh ke `admin` kalau approval dan reversal seharusnya milik `owner`
- [ ] Jangan menyatukan terlalu banyak kerjaan gudang dan produksi ke role `kasir`

## Saran Final

Kalau targetnya struktur sederhana, aman, dan mudah dijelaskan ke tenant:

- `kasir` = transaksi dan laporan pribadi
- `admin` = operasional + master data + tim
- `owner` = admin + audit + langganan + finansial
- `administrator` = bypass internal saja

Kalau targetnya struktur lebih matang:

1. Tetapkan matriks menu per role sebagai acuan bisnis
2. Pisahkan `Usaha & Cabang` dari `menu_users`
3. Pecah permission akuntansi yang masih terlalu gabung
4. Tambah role turunan `gudang` dan `produksi`
5. Review ulang preset paket agar cocok dengan pembagian role baru
