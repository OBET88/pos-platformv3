# Urutan Deploy IDwebhost — Dua Aplikasi (Fresh, Tanpa Tenant)

Dokumen ini adalah **urutan langkah deployment** untuk melepas dua aplikasi ke
IDwebhost yang masih fresh (belum ada tenant):

1. `poslive` — aplikasi tenant POS resto (wildcard `*.nuhafrandesa.com`).
2. `pos-platform` — control plane SaaS, pendaftaran, langganan, invoice, DOKU,
   dan worker onboarding (`resto.nuhafrandesa.com`).

Fakta yang menjadi dasar dokumen ini (Update Bos Robert, 2026-10-31):

- **IDwebhost PUNYA TERMINAL** → migration jalan langsung dengan
  `php artisan migrate`, dan `composer install --no-dev` juga via terminal.
  Tidak perlu trigger browser `update-v1.php` untuk fresh install.
- **Rumahweb TIDAK punya terminal** → dKriuk di Rumahweb tetap pakai prosedur
  `poslive/docs/updatev1.md` + trigger browser (bukan terminal). Rumahweb =
  environment existing/protected, bukan target deploy ini.
- **Tidak ada import dump SQL dari DapurHosting.** IDwebhost fresh; semua data
  dibuat lewat pendaftaran. Dump `DB/*.sql` (dKriuk) hanya untuk referensi.
- DOKU memakai **sandbox** dulu (`https://api-sandbox.doku.com`, credential
  sandbox `BRN-0276-...`). Migrasi ke production dilakukan belakangan, setelah
  akun production dipindahkan.
- Wildcard tenant (`*.nuhafrandesa.com` → `poslive/public`) hanya boleh
  dipublikasikan setelah validation reserved code (12 nama terlarang) sudah
  teruji. Validasi sudah terimplementasi di `TenantDomainSuggestionService` +
  `TenantRegistrationService` (commit terpisah, full suite 168 passed) — wajib
  diproses test ulang di lingkungan rilis.
- Landing root `nuhafrandesa.com` = induk resto + minimarket.
  `mm.nuhafrandesa.com` reserved (belum dibangun, process bangun).
- Tenant code Opsi A: field `tenant_code` opsional di form register; bila
  kosong, auto dari nama tenant (maks. 12 char). Reserved list final (12 word):
  `www, resto, mm, admin, api, app, mail, ftp, cpanel, webmail, support, status`.

Dokumen ini tidak memuat password, token, APP_KEY, atau credential DOKU/DB
yang sebenarnya. Isi `.env` production dibuat di hosting, bukan diunggah dari
local.

Sumber yang sudah dibaca sebelum menulis dokumen ini:
`docs/DEPLOYMENT_PREFLIGHT_WORKSHEET.md`,
`docs/IDWEBHOST_ONBOARDING_RELEASE_RUNBOOK.md`,
`docs/HOSTING_DEPLOYMENT_CHECKLISTS.md`, dan
`poslive/docs/updatev1.md` (daftar file replace).

---

## Fase 0 — Pre-flight (sebelum upload apa pun)

Selesaikan seluruh item ini **di DapurHosting dan di local** sebelum menyentuh
IDwebhost. Tidak ada upload ke IDwebhost sebelum fase ini tuntas.

### 0.1 Backup DapurHosting sebagai fallback

Buat salinan terakhir dari environment DapurHosting sebelum deploy IDwebhost,
sebagai titik kembali bila IDwebhost bermasalah dan Bos Robert ingin kembali
ke URL lama:

1. Export database platform DapurHosting (MySQL dump dari cPanel →
   phpMyAdmin → Export, atau File Manager bila dump sudah tersedia).
2. Export database tenant dKriuk (cara yang sama, database-nya sendiri).
3. Simpan kedua dump dengan nama yang menandai tanggal dan sumbernya,
   contoh `dapurhosting_platform_YYYYMMDD.sql`,
   `dapurhosting_dkriuk_YYYYMMDD.sql`.
4. Simpan dump di tempat yang **tidak** ikut di-upload ke IDwebhost.
5. Catat versi/commit code aplikasi DapurHosting yang sedang jalan
   (untuk rujukan bila perlu rollback ke versi lama).

> Dump ini **fallback saja**. IDwebhost fresh, jadi dump DapurHosting
> **tidak** di-import ke database IDwebhost.

### 0.2 Verifikasi file build fresh

1. Jalankan `npm run build` di `poslive` sampai sukses.
2. Cek timestamp file di `public/build/` — harus baru, sesuai waktu build
   barusan. Bila timestamp lama, build tidak selesai/aplikasi sudah berubah
   sejak build terakhir → ulangi build.
3. `pos-platform` tidak memakai Vite build yang dinamis (aset di
   `pos-platform/public` sudah static) — pastikan folder `pos-platform/public/`
   yang akan diunggah sudah up-to-date.
4. Catat nama file hasil build (misal `manifest.json` + hash `assets/`)
   untuk verifikasi di hosting setelah upload.

### 0.3 Status `diagnose_doku_qris.php` di hosting (separate, tidak blocking)

Catatan penting yang harus dipahami sebelum smoke test DOKU di fase 6:

- Pada 2026-10-30, diagnostik `diagnose_doku_qris.php` di DapurHosting
  menunjukkan endpoint **DOKU sandbox** `api-sandbox.doku.com` mengembalikan
  HTTP 503 untuk semua bentuk request (lihat
  `pos-platform/CHANGELOG_FIXES.md`, entri terkait gangguan upstream DOKU
  sandbox). Endpoint **production** `api.doku.com` menjawab normal (401
  untuk credential sandbox, sesuai expectation).
- Artinya: kegagalan transaksi DOKU sandbox bisa murni **gangguan di sisi
  DOKU**, bukan konfigurasi hosting.
- File `diagnose_doku_qris.php` **tidak** ikut di-upload ke IDwebhost
  (mengandung private key DOKU, sudah dihapus dari repo, dan **sudah
  dikonfirmasi Bos Robert terhapus dari Rumahweb pada 2026-10-31**).
- Status item keamanan ini: **SELESAI** (non-blocking untuk deploy).
- **Tidak blocking** untuk deploy: tetap deploy dengan DOKU sandbox.
  Bila smoke test DOKU di fase 6 gagal dengan gejala mirip 503,
  diagnosis ulang langsung di IDwebhost (uji GET kosong ke
  `api-sandbox.doku.com`) sebelum menyimpulkan konfigurasi salah.

Tandai item ini dengan status saat deploy:
`[ ] DOKU sandbox reachable / [ ] DOKU sandbox down (503, sesuai riwayat 2026-10-30)`
Status ini tidak menghentikan langkah berikutnya, hanya konteks untuk
menafsirkan hasil fase 6.

---

## Fase 1 — Setup IDwebhost di cPanel

Jalankan di terminal cPanel / File Manager IDwebhost (belum ada kode di sana
saat ini).

### 1.1 Buat database MySQL platform

Di cPanel → MySQL® Databases:

1. Buat database, contoh: `nuhafran_pos_platform`
   (nama final sesuai cPanel Bos Robert).
2. Buat user khusus, contoh: `nuhafran_pos` + password kuat.
3. Grant **ALL PRIVILEGES** user ke database tersebut.

> Jangan menggunakan database atau credential DapurHosting.

### 1.2 Buat database MySQL POSLive central

Aplikasi `poslive` memakai **central DB** (default/`tenant` connection,
berisi `tenant_domains` dan kredensial terenkripsi tenant) di samping koneksi
platform-nya. Buat satu database khusus:

1. Buat database, contoh: `nuhafran_poslive_central`.
2. Buat user khusus, contoh: `nuhafran_pos` + password kuat.
3. Grant **ALL PRIVILEGES** user ke database tersebut.

Catatan tentang provision database tenant: onboarding membuat database tenant
baru secara programatik (lihat `PLATFORM_TENANT_DB_ADMIN_*` di `.env`
platform, fase 3.4). Verifikasi user cPanel/MySQL yang dipakai punya privilege
`CREATE DATABASE`/`CREATE USER`/`GRANT` untuk membuat database & user tenant
baru — uji dengan scenario non-produksi (lihat `HOSTING_DEPLOYMENT_CHECKLISTS.md`,
B4).

### 1.3 Buat folder shared input

Buat di **luar** web root, dari File Manager atau terminal:

```bash
mkdir -p /home/nuhafran/.tenant-bootstrap-input
chmod 0700 /home/nuhafran/.tenant-bootstrap-input
```

- Permission directory wajib `0700`.
- File input sementara yang dibuat oleh aplikasi harus `0600` (ditangani
  otomatis oleh aplikasi; cukup pastikan directory-nya sendiri tidak bisa
  diakses user lain).
- Folder ini **tidak** dipakai untuk upload/permanen, hanya handoff bootstrap.
- `pos-platform` dan `poslive` harus menunjuk ke path **persis sama** ini
  (lihat fase 3.4 dan 2.5).

### 1.4 Verifikasi PHP CLI

Di terminal cPanel (IDwebhost punya terminal, jadi ini bisa langsung diuji):

```bash
which php
php -v
```

- Catat path absolut binary yang ditemukan (indikasi dari worksheet:
  `/usr/local/bin/php`).
- Verifikasi binary tersebut bisa menjalankan file `artisan` (uji setelah
  aplikasi ter-upload, fase 3.5).
- Nilai path ini dipakai untuk `PLATFORM_TENANT_APP_PHP_BINARY` di `.env`
  platform.

### 1.5 Verifikasi Cron cPanel tersedia

- Buka cPanel → Cron Jobs.
- Konfirmasi form tersedia (menit/jam/hari/bulan/tahun + command +
  shell/email output).
- **Jangan** membuat Cron di sini — Cron baru dipasang di fase 5, setelah
  aplikasi dan migration platform sudah beres.

Centang kelima item (1.1–1.5) di worksheet
`DEPLOYMENT_PREFLIGHT_WORKSHEET.md` bagian IDwebhost sebelum lanjut.

---

## Fase 2 — Upload & Setup poslive (Aplikasi Tenant)

`poslive` adalah aplikasi yang menangani wildcard tenant
(`*.nuhafrandesa.com`). Aplikasi ini **bukan** target `update-v1.php` pada
deploy fresh — karena IDwebhost punya terminal, inisialisasi database jalan
via `php artisan migrate`, bukan trigger browser.

### 2.1 Tentukan folder aplikasi

- Folder aplikasi: `/home/nuhafran/poslive/`
- Folder web (untuk wildcard tenant): `poslive/public/`
  cPanel → Subdomains/Add-On Domains: buat wildcard subdomain
  `*.nuhafrandesa.com` yang menunjuk ke directory `poslive/public`
  (bukan `public_html` default, kecuali cPanel IDwebhost memakai skema
  document root per-domain — konfirmasi di cPanel).
- `resto.nuhafrandesa.com` dan `mm.nuhafrandesa.com` **tidak**
  ditangani di fase ini (mereka milik `pos-platform`, fase 3).

### 2.2 Upload file

Upload seluruh isi aplikasi `poslive` sesuai `poslive/docs/updatev1.md`:

- Semua file dari daftar **Daftar lengkap file yang di-replace** + seluruh
  struktur aplikasi `poslive` yang belum ada di hosting (karena ini fresh,
  upload aplikasinya utuh, bukan sekadar diff).
- Upload `public/build/` hasil build fresh (fase 0.2) ke `poslive/public/`.
- **JANGAN** upload dari local ke hosting:
  - `.env` (dibuat di hosting, fase 2.4)
  - `vendor/` (harus di-install di hosting, atau hasil `composer install`
    — konfirmasi metode IDwebhost: apakah ada SSH/terminal untuk
    `composer install`, atau `vendor/` perlu di-transfer terpisah sesuai
    kebijakan hosting)
  - `tests/`
  - `docs/updatev1.md`
- Jangan menimpa `index.php` yang sedang aktif — untuk fresh install tidak
  ada `index.php` lama, cukup upload yang baru.

### 2.3 Install/verifikasi `vendor/`

Karena fresh:

- Opsi A (disarankan, sesuai aturan "Jangan unggah vendor"): jalankan
  `composer install --no-dev` di folder aplikasi lewat terminal IDwebhost
  (terminal tersedia, jadi ini bisa langsung).
- Opsi B (bila IDwebhost tidak mengizinkan `composer`): transfer
  `vendor/` hasil build local yang sudah di-review (pastikan PHP version
  local dan IDwebhost kompatibel).

Catat metode yang dipakai di worksheet.

### 2.4 Buat `.env` POSLive di hosting

Buat dari scratch di hosting (bukan menyalin `.env` local). Nilai wajib:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=<hasil php artisan key:generate di hosting>

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nuhafran_poslive_central
DB_USERNAME=nuhafran_pos
DB_PASSWORD=<password>

TENANCY_PLATFORM_CONNECTION=platform
PLATFORM_DB_CONNECTION=mysql
PLATFORM_DB_HOST=127.0.0.1
PLATFORM_DB_DATABASE=nuhafran_pos_platform
PLATFORM_DB_USERNAME=nuhafran_pos
PLATFORM_DB_PASSWORD=<password>
TENANCY_PLATFORM_APP_KEY=<APP_KEY platform yang sama dengan pos-platform/.env>

TENANCY_CENTRAL_HOSTS=nuhafrandesa.com,www.nuhafrandesa.com,resto.nuhafrandesa.com,mm.nuhafrandesa.com
TENANT_BOOTSTRAP_INPUT_DIRECTORY=/home/nuhafran/.tenant-bootstrap-input

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

> Nilai IDwebhost (final, dikonfirmasi Bos Robert 2026-10-31): home `/home/nuhafran`, PHP CLI `/usr/local/bin/php` (8.2.35), DB platform `nuhafran_pos_platform`, DB central `nuhafran_poslive_central`, user DB `nuhafran_pos`, IP publik `202.52.146.137`. Password DB diisi saat bikin `.env` di hosting (jangan ditulis di dokumen ini).

Poin penting:

- `TENANT_BOOTSTRAP_INPUT_DIRECTORY` **WAJIB** diisi, harus path persis sama
  dengan `PLATFORM_TENANT_BOOTSTRAP_INPUT_DIRECTORY` di `.env` platform
  (fase 3.4).
- `TENANCY_CENTRAL_HOSTS` menentukan host mana yang diarahkan ke central
  DB vs tenant DB. Nilai di atas sesuai worksheet.
- `TENANCY_PLATFORM_APP_KEY` harus **identik** dengan `APP_KEY` di
  `.env` `pos-platform` — kunci ini dipakai untuk mengenkrip/dekrip
  kredensial tenant DB yang disimpan di central DB.

### 2.5 Jalankan migration central DB

Dari folder aplikasi `poslive`, lewat terminal IDwebhost:

```bash
php artisan migrate
```

- Ini membuat schema central DB dari nol (fresh, tidak ada data lama,
  tidak perlu backfill).
- Verifikasi tabel inti: `tenant_domains`, tabel permission/role, dan
  tabel onboarding tenant yang dibutuhkan POSLive.
- Jangan jalankan `php artisan migrate --seed` kecuali memang ada seeder
  central yang wajib (lihat aturan: jangan re-run `RolePermissionSeeder`
  pada tenant yang sudah ada — ini fresh, jadi seeder awal aman dijalankan
  sekali jika memang aplikasi meminta).

### 2.6 (Opsional, hanya jika diminta oleh aplikasi) Buat entry central

Bila POSLive membutuhkan record awal di central DB (misal konfigurasi
tenant domain wildcard) sebelum `pos-platform` bisa bootstrap tenant,
buat melalui jalur yang disetujui aplikasi — jangan edit manual `tenant_domains`
tanpa memahami konsekuensi enkripsi kredensial tenant.

---

## Fase 3 — Upload & Setup pos-platform (Control Plane)

`pos-platform` melayani `resto.nuhafrandesa.com` (dan di masa depan
`mm.nuhafrandesa.com`).

### 3.1 Tentukan folder aplikasi

- Folder aplikasi: `/home/nuhafran/pos-platform/`
- Folder web: `pos-platform/public/`
- cPanel → Add-On Domains/Subdomains: arahkan
  `resto.nuhafrandesa.com` (dan nanti `mm.nuhafrandesa.com`)
  ke `pos-platform/public`.

### 3.2 Upload file

Upload seluruh isi aplikasi `pos-platform`:

- **JANGAN** upload dari local:
  - `.env` (dibuat di hosting, fase 3.4)
  - `vendor/` (sama seperti poslive — `composer install --no-dev` di
    hosting, atau transfer `vendor/` yang sudah di-review)
  - `tests/`
  - `DB/` (folder dump SQL DapurHosting — berisi referensi dKriuk,
    **JANGAN** ikut ter-upload ke IDwebhost; bukan bagian dari aplikasi)
- Upload seluruh file aplikasi lainnya: `app/`, `config/`, `database/`
  (migrations & seeders), `routes/`, `public/`, `bootstrap/`, `artisan`,
  dll.

### 3.3 Install/verifikasi `vendor/`

Sama seperti fase 2.3: `composer install --no-dev` di hosting (terminal
tersedia) atau transfer `vendor/` yang sudah di-review.

### 3.4 Buat `.env` platform di hosting

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=<hasil php artisan key:generate di hosting>
APP_URL=https://resto.nuhafrandesa.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nuhafran_pos_platform
DB_USERNAME=nuhafran_pos
DB_PASSWORD=<password>

PLATFORM_TENANT_DATABASE_DRIVER=mysql
PLATFORM_TENANT_BASE_DOMAIN=nuhafrandesa.com
PLATFORM_TENANT_DB_HOST=127.0.0.1
PLATFORM_TENANT_DB_PORT=3306
PLATFORM_TENANT_DB_ADMIN_DATABASE=<database admin cPanel, mis. mysql>
PLATFORM_TENANT_DB_ADMIN_USERNAME=nuhafran_pos
PLATFORM_TENANT_DB_ADMIN_PASSWORD=<password>
PLATFORM_TENANT_DB_RUNTIME_USERNAME=nuhafran_pos
PLATFORM_TENANT_DB_RUNTIME_PASSWORD=<password>

PLATFORM_TENANT_APP_PATH=/home/nuhafran/poslive
PLATFORM_TENANT_APP_PHP_BINARY=/usr/local/bin/php
PLATFORM_TENANT_BOOTSTRAP_INPUT_DIRECTORY=/home/nuhafran/.tenant-bootstrap-input

DOKU_ENABLED=true
DOKU_BASE_URL=https://api-sandbox.doku.com
DOKU_CLIENT_ID=<client_id DOKU sandbox>
DOKU_SECRET_KEY=<secret_key DOKU sandbox>
DOKU_MERCHANT_NAME="NuhaFrandesa"
DOKU_PAYMENT_METHOD_TYPES=<sesuai sandbox>

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
MAIL_MAILER=smtp
MAIL_HOST=<smtp IDwebhost>
MAIL_PORT=587
MAIL_USERNAME=<...>
MAIL_PASSWORD=<...>
```

> Nilai IDwebhost (final, dikonfirmasi Bos Robert 2026-10-31): home `/home/nuhafran`, PHP CLI `/usr/local/bin/php` (8.2.35), DB platform `nuhafran_pos_platform`, DB central `nuhafran_poslive_central`, user DB `nuhafran_pos`, IP publik `202.52.146.137`.
>
> **PENTING — shared input dir harus identik di kedua aplikasi:**
> - platform `.env`: `PLATFORM_TENANT_BOOTSTRAP_INPUT_DIRECTORY=/home/nuhafran/.tenant-bootstrap-input`
> - poslive `.env`: `TENANT_BOOTSTRAP_INPUT_DIRECTORY=/home/nuhafran/.tenant-bootstrap-input`
>
> **PENTING — `TENANCY_PLATFORM_APP_KEY` di poslive = `APP_KEY` di platform** (satu kunci sama, untuk enkripsi kredensial tenant).

Poin wajib:

- `PLATFORM_TENANT_APP_PATH` = `/home/nuhafran/poslive` (folder fase 2).
- `PLATFORM_TENANT_APP_PHP_BINARY` = path PHP CLI hasil verifikasi fase 1.4.
- `PLATFORM_TENANT_BOOTSTRAP_INPUT_DIRECTORY` **persis sama** dengan
  `TENANT_BOOTSTRAP_INPUT_DIRECTORY` di `.env` POSLive (fase 2.4).
- DOKU = **sandbox** dulu: `DOKU_BASE_URL=https://api-sandbox.doku.com`
  + credential sandbox. Migrasi ke production belakangan.
- `PLATFORM_TENANT_DB_ADMIN_*`: user cPanel yang dipakai onboarding untuk
  membuat database & user tenant baru. Uji privilege-nya (bisa
  `CREATE DATABASE`, `CREATE USER`, `GRANT`) **sebelum** smoke test,
  pakai scenario test non-produksi (lihat `HOSTING_DEPLOYMENT_CHECKLISTS.md`,
  B4).

### 3.5 Verifikasi PHP CLI + artisan platform

```bash
cd /home/nuhafran/pos-platform
/usr/local/bin/php artisan --version
```

- Pastikan tidak ada error PHP version / extension.
- Uji child process: jalankan satu command platform yang memicu
  bootstrap POSLive (mis. dry-run `platform:bootstrap-tenant-app` dengan
  parameter test) untuk memastikan `PLATFORM_TENANT_APP_PHP_BINARY` benar
  dapat membaca aplikasi POSLive di host yang sama.

### 3.6 Jalankan migration database platform

Dari folder aplikasi `pos-platform`:

```bash
php artisan migrate
```

- Ini fresh DB → seluruh migration platform dari awal, termasuk yang
  ditambahkan oleh hardening onboarding commit `981fd18`:
  - `2026_10_23_010000_create_tenant_bootstrap_secrets_table.php`
  - `2026_10_23_020000_create_tenant_onboarding_jobs_table.php`
  - `2026_10_23_030000_backfill_legacy_tenant_onboarding_state.php`
  - `2026_10_23_040000_add_provider_event_fingerprint_to_payment_events.php`
- Tidak perlu backfill legacy di sini (IDwebhost fresh, tidak ada tenant
  lama) — migration backfill aman jalan terhadap data kosong.
- Verifikasi tabel inti: `tenant_bootstrap_secrets`,
  `tenant_onboarding_jobs`, `platform_payment_events` (dengan kolom +
  unique index event provider), `plans`, roles/permissions platform.

### 3.7 Jalankan seeder untuk platform (fresh DB, boleh seed)

Karena database platform benar-benar fresh, seeder aman dijalankan sekali:

```bash
php artisan db:seed
# atau seeder spesifik bila memang diperlukan:
php artisan db:seed --class=<NamaSeederPlatform>
```

- Verifikasi setelah seed:
  - Record `plans` (basic/pro/trial) tersedia.
  - Role/permission platform tersedia
    (`2026_10_12_000200_seed_platform_roles_and_permissions.php`
    sudah termasuk di migration, jadi seeder tambahan hanya untuk data
    non-permission bila ada).
- **JANGAN** menjalankan seeder yang mengandung data fixture lokal/test
  (misal `User::factory()` "Test User") atau master produk/recipe/stock —
  master data diisi lewat menu setelah tenant tersedia, bukan lewat seeder.
  `db:seed` bawaan Laravel (yang hanya membuat "Test User") tidak perlu
  dijalankan bila tidak ada kebutuhan; seeder platform (plan, role,
  permission) sudah tertanam di migration (lihat 3.6), jadi `db:seed`
  mungkin tidak diperlukan sama sekali untuk fresh install ini. Verifikasi
  di lingkungan rilis: cek apakah tabel `plans`, role, dan permission
  sudah terisi pasca-migration; bila iya, **lewati** `db:seed`.

### 3.8 Buat platform administrator pertama

```bash
php artisan platform:create-admin <name> <email> <password>
```

- Ini membuat akun admin untuk `https://resto.nuhafrandesa.com/platform-login`.
- Simpan credential admin di tempat aman (jangan di dokumen ini).
- Verifikasi admin dapat login dashboard platform.

---

## Fase 4 — DNS & SSL

### 4.1 Siapkan catatan nilai yang dibutuhkan

- IP publik server IDwebhost: **`202.52.146.137`** (konfirmasi Bos Robert 2026-10-31).
- Nama registrar DNS yang dipakai `nuhafrandesa.com` (IDwebhost biasanya
  menyediakan DNS manager sendiri; bila domain registrasi di IDwebhost,
  record dibuat langsung di cPanel → Zone Editor).

### 4.2 Tambah A-record / wildcard (di IDwebhost cPanel → Zone Editor,
atau di registrar bila domain tidak di IDwebhost)

| Record | Tipe | Nilai | Catatan |
|---|---|---|---|
| `nuhafrandesa.com` | A | 202.52.146.137 | Landing page induk |
| `www.nuhafrandesa.com` | A/CNAME | 202.52.146.137 (atau CNAME ke `nuhafrandesa.com`) | Alias landing |
| `resto.nuhafrandesa.com` | A | 202.52.146.137 | `pos-platform/public` |
| `mm.nuhafrandesa.com` | A | 202.52.146.137 | reserved, `pos-platform/public` di masa depan |
| `*.nuhafrandesa.com` | A (wildcard) | 202.52.146.137 | `poslive/public` |

Perhatian penting (dari `DEPLOYMENT_PREFLIGHT_WORKSHEET.md`):

- **Host eksplisit `resto` dan `mm` harus mengalahkan wildcard
  `*.nuhafrandesa.com`.** Ini perilaku yang HARUS dibuktikan, bukan
  diasumsikan.
- Urutan record di cPanel: buat A-record eksplisit dulu, baru wildcard
  — dan **uji di browser** sebelum membuka wildcard untuk publik
  (fase 6), agar tidak ada `resto.nuhafrandesa.com` yang salah diarahkan
  ke `poslive/public` (atau sebaliknya).

### 4.3 Setup cPanel Subdomains / Add-On Domains

- `resto.nuhafrandesa.com` → document root `pos-platform/public`
- `*.nuhafrandesa.com` (wildcard) → document root `poslive/public`
- `mm.nuhafrandesa.com` → document root `pos-platform/public`
  (reserved; aplikasi minimarket belum dibangun, boleh sementara
  menampilkan placeholder `pos-platform` atau halaman "coming soon" —
  keputusan di sini tidak memengaruhi resto, selama record eksplisit
   `mm` menang atas wildcard)

### 4.4 SSL

- Aktifkan SSL untuk `nuhafrandesa.com`, `resto.nuhafrandesa.com`,
  `mm.nuhafrandesa.com`, dan wildcard `*.nuhafrandesa.com`
  (AutoSSL/Let's Encrypt di cPanel — wildcard biasanya butuh certificate
  khusus wildcard, konfirmasi ketersediaan di paket IDwebhost).
- `APP_FORCE_HTTPS=true` disarankan di `.env` kedua aplikasi setelah
  SSL terpasang (opsional, sesuai kebutuhan).

### 4.5 Uji browser (sebelum smoke test fungsional fase 6)

- `https://resto.nuhafrandesa.com` → halaman platform pos-platform
  (bukan POSLive).
- `https://mm.nuhafrandesa.com` → halaman yang benar
  (placeholder/pos-platform, **bukan** POSLive).
- `https://<test-tenant>.nuhafrandesa.com` (nama test apa pun, misal
  `testx`) → halaman POSLive (login/404 sesuai routing, **bukan**
  halaman platform). Ini membuktikan wildcard mengarah ke `poslive/public`.
- `https://nuhafrandesa.com` → landing page induk (bila landing page
  sudah disiapkan; bila belum, ini boleh ditunda — landing page adalah
  pekerjaan terpisah, bukan blocker untuk kedua aplikasi).

---

## Fase 5 — Cron

### 5.1 Buat Cron Job cPanel

cPanel → Cron Jobs, satu job per menit:

```cron
* * * * * /usr/local/bin/php /home/nuhafran/pos-platform/artisan schedule:run >> /dev/null 2>&1
```

- Ganti `/usr/local/bin/php` dengan path PHP CLI hasil verifikasi fase 1.4
  (WAJIPAT tidak memakai contoh di atas secara buta).
- Ganti `nuhafran` dengan nama user cPanel IDwebhost bila ternyata beda (ini sudah sesuai user Bos Robert).
- **Jangan** membuat Cron kedua yang memanggil worker onboarding langsung
  (`platform:work-tenant-onboarding`) — satu `schedule:run` adalah
  sumber orkestrasi tunggal.
- Jangan nyalakan Cron sebelum seluruh migration platform (fase 3.6)
  selesai dan sukses.

### 5.2 Verifikasi Cron berjalan

- Tunggu 1–2 siklus Cron (±1–2 menit).
- Cek log/indikator hosting (cPanel biasanya mengirim output cron ke
  email bila diaktifkan) — tidak ada error PHP.
- Cek `log/laravel.log` di `pos-platform` — tidak ada error scheduler.
- Verifikasi di database platform: tidak ada job onboarding yang
  mengikat (fresh, harusnya kosong) dan command `schedule:run`
  tercatat berjalan (mis. via `laravel_scheduled_commands` bila aplikasi
  mencatat, atau cukup tidak ada error yang muncul).

---

## Fase 6 — Smoke Test

Lakukan di data uji, bukan data operasional (IDwebhost fresh, jadi semua
data di sini memang data test).

### 6.1 Pra-test

- [ ] Buka `https://resto.nuhafrandesa.com`, pastikan halaman platform
      ter-render normal.
- [ ] Login dashboard platform dengan admin hasil fase 3.8.
- [ ] Verifikasi konfigurasi DOKU yang terlihat di dashboard
      (endpoint sandbox, client id sandbox).
- [ ] Verifikasi `DOKU_ENABLED=true` dan tidak ada error konfigurasi.

### 6.2 Test reserved code ditolak

Uji validasi reserved tenant code (reserved list final 12 word: `www, resto,
mm, admin, api, app, mail, ftp, cpanel, webmail, support, status`):

1. Cobalah register tenant dengan code `resto` → harus ditolak.
2. Cobalah code `www`, `mm`, `admin` → semua harus ditolak
   dengan pesan error yang jelas, bukan 500.
3. Verifikasi pesan error merujuk pada aturan reserved, bukan generic
   validation error.
4. Opsi A: uji field `tenant_code` opsional — registrasi tanpa mengisi
   `tenant_code` harus auto-generate dari nama tenant (maks. 12 char) dan
   hasil auto-generate yang jatuh ke reserved list harus ditolak juga.

Ini membuktikan `TenantDomainSuggestionService` +
`TenantRegistrationService` benar-benar aktif sebelum wildcard
dipublikasikan untuk tenant nyata.

### 6.3 Test tenant biasa berhasil (alur lengkap)

Pakai email test (bukan email operasional Bos Robert) dan tenant code
yang tidak reserved, misal `testresto1`:

1. Register tenant test via `https://resto.nuhafrandesa.com`
   (formulir pendaftaran publik) dengan code `testresto1`, plan
   berbayar.
2. Verifikasi email masuk ke alamat test → klik tautan verifikasi.
3. Browser harus menuju halaman invoice pembayaran, **bukan** login
   tenant / bootstrap selesai. Status tenant harus `pending_payment`
   sebelum payment valid.
4. Bayar invoice:
   - Opsi A: approval manual dari dashboard admin (paling cepat untuk
     test).
   - Opsi B: alur DOKU sandbox end-to-end (paling representatif;
     gunakan bila DOKU sandbox sedang reachable — cek dulu fase 0.3).
5. Tunggu maksimal 2 siklus Cron (±2 menit).
6. Cek status onboarding: `blocked` → `queued` → `running` →
   `completed`.
7. Verifikasi:
   - Secret bootstrap terbuang setelah completion.
   - Database tenant baru terbentuk di MySQL IDwebhost
     (cek di cPanel → MySQL Databases).
   - Migration POSLive tenant sudah jalan (cek tabel inti di DB
     tenant).
   - Owner, business, dan branch terbentuk.
   - Entry `tenant_domains` terisi di central DB POSLive.
8. Login ke `https://testresto1.nuhafrandesa.com` dengan owner test
   yang didaftarkan → dashboard POSLive terbuka.
9. Akses halaman status onboarding **tanpa** token → harus ditolak.
   Dengan token access dari alur pendaftaran → status tautan tersedia.

### 6.4 DOKU sandbox safety (sesuai riwayat gangguan, fase 0.3)

Bila pembayaran lewat DOKU sandbox (6.3 Opsi B):

1. Verifikasi webhook DOKU sandbox valid diproses **satu kali** (cek
   `platform_payment_events` — tidak ada duplikasi event).
2. Reference checkout lokal dan webhook cocok.
3. Webhook duplikat tidak memperpanjang subscription lagi.
4. Status FAILED/EXPIRED yang terlambat tidak menurunkan invoice yang
   sudah paid.
5. Webhook dengan signature tidak valid ditolak, tidak mengubah
   invoice/subscription.
6. **Bila gagal dengan HTTP 503 dari `api-sandbox.doku.com`** → ini
   sesuai riwayat gangguan upstream DOKU (bukan konfigurasi IDwebhost).
   Tandai di worksheet (fase 0.3), jangan putuskan konfigurasi salah
   sebelum menguji reachability endpoint sandbox langsung.

### 6.5 Admin recovery (terkendali)

Pada satu tenant test yang sengaja dibuat gagal (misal hentikan worker
saat `running`, atau buat job `failed` terkendali):

1. Jalankan recovery admin dari dashboard platform.
2. Verifikasi job menjadi `completed` dan tidak diklaim ulang Cron.
3. Verifikasi status publik merefleksikan completion.

### 6.6 Bersihkan data test

Setelah smoke test berhasil dan tervalidasi:

- Hapus tenant test (`testresto1`) hanya melalui prosedur yang aman
  (jangan hapus DB tenant manual tanpa memahami konsekuensi untuk
  `tenant_domains` / central DB POSLive). Bila tidak tersedia prosedur
  penghapusan resmi, biarkan tenant test tetap ada dan tandai sebagai
  data test di catatan deployment — jangan data operasional.

---

## Fase 7 — Post-deploy

1. **Update `DEPLOYMENT_PREFLIGHT_WORKSHEET.md`** — centang item IDwebhost
   yang sudah terisi (path app, PHP CLI, DB, shared input dir, DOKU
   environment, wildcard policy).
2. **Update `docs/HOSTING_DEPLOYMENT_CHECKLISTS.md`** bagian B
   (IDwebhost fresh install) — centang item B1–B5 yang sudah selesai.
3. **`CHANGELOG_FIXES.md`** — catat bila ada perbaikan/penyesuaian yang
   ditemukan selama deploy (mis. konfigurasi privilege MySQL yang harus
   diubah, path PHP CLI yang ternyata beda dari indikasi, dsb). Sertakan
   angka nyata (jumlah tenant test dibuat, status job, durasi Cron
   siklus pertama).
4. **`handoffs/`** — buat atau perbarui file handoff `idwebhost-resto-
   onboarding-live.md`:
   - Status: LIVE / DITUNDA (sesuai hasil fase 6).
   - Langkah berikutnya: migrasi DOKU ke production (setelah akun
     production siap), setup landing page `nuhafrandesa.com`,
      pembangunan `mm.nuhafrandesa.com`.
   - Jangan lakukan: mencampur DB/`.env`/Cron IDwebhost dengan
     DapurHosting.
5. **Commit** perubahan dokumentasi di atas (separat dari commit code
   aplikasi, bila memang sudah ada commit terpisah).
6. **Laporan ke Bos Robert**:
   - Ringkasan status: tenant test berhasil / tidak, reserved code
     ditolak / tidak, DOKU sandbox reachable / down.
   - Konfirmasi URL pendaftaran yang akan dikirim ke calon tenant:
     `https://resto.nuhafrandesa.com` (IDwebhost) vs
     `https://rwp.jajajuju.my.id` (DapurHosting) — keputusan manual
     sebelum URL diedarkan.
   - Reminder: DOKU masih sandbox; rencana migrasi ke production dan
     kapan credential production siap.

---

## Catatan larangan yang berlaku di seluruh fase

- Jangan upload `.env` local, `vendor/`, `tests/`, atau dump `DB/`
  (dKriuk) ke IDwebhost.
- Jangan menjalankan `RolePermissionSeeder` ulang pada tenant yang
  sudah ada (fresh install ini aman dijalankan sekali di awal, lihat
  fase 3.7).
- Jangan membuka `update-v1.php` untuk inisialisasi fresh — IDwebhost
  punya terminal, `php artisan migrate` menggantikan trigger browser
  (trigger browser tetap dipakai untuk **upgrade skema tenant**
  berikutnya, bukan fresh install).
- Jangan nyalakan Cron sebelum seluruh migration platform selesai
  (fase 3.6 → 5.1).
- Jangan publikasikan wildcard tenant untuk calon tenant nyata sebelum
  validasi reserved code teruji (fase 6.2).
- Jangan mencampur database, `.env`, directory bootstrap input, atau
  Cron antara DapurHosting dan IDwebhost.