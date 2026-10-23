## POS Platform SaaS Deployment Guide

Dokumen ini menjelaskan alur setup, bootstrap tenant, dan checklist go-live untuk dua aplikasi:

- `pos-platform`: aplikasi platform/admin SaaS
- `poslive`: aplikasi tenant POS

### 1. Arsitektur Singkat

- `pos-platform` menyimpan data pusat:
  - tenant
  - domain tenant
  - paket langganan
  - log provisioning
- `poslive` adalah aplikasi tenant yang dipakai user tenant untuk operasional POS
- setiap tenant memiliki database sendiri
- resolusi tenant dilakukan berdasarkan host/domain, misalnya:
  - `tenant-a.poslive.test`
  - `tenant-b.poslive.test`

### 2. Alur Tenant

Urutan yang benar:

1. tenant didaftarkan di `pos-platform`
2. `pos-platform` membuat database tenant
3. `pos-platform` memanggil bootstrap ke `poslive`
4. `poslive` menjalankan migration tenant DB
5. `poslive` membuat data awal:
   - role/permission
   - business
   - branch
   - owner tenant
6. tenant login lewat domain tenant

### 3. Konfigurasi Local

#### 3.1 `pos-platform/.env`

Nilai penting:

```env
APP_URL=http://pos-platform.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_platform
DB_USERNAME=root
DB_PASSWORD=

PLATFORM_TENANT_DATABASE_DRIVER=mysql
PLATFORM_TENANT_BASE_DOMAIN=poslive.test
PLATFORM_TENANT_DB_HOST=127.0.0.1
PLATFORM_TENANT_DB_PORT=3306
PLATFORM_TENANT_DB_ADMIN_DATABASE=mysql
PLATFORM_TENANT_DB_ADMIN_USERNAME=root
PLATFORM_TENANT_DB_ADMIN_PASSWORD=
PLATFORM_TENANT_DB_RUNTIME_USERNAME=root
PLATFORM_TENANT_DB_RUNTIME_PASSWORD=
PLATFORM_TENANT_APP_PATH=../poslive
PLATFORM_TENANT_BOOTSTRAP_INPUT_DIRECTORY=/home/ACCOUNT/.tenant-bootstrap-input
```

#### 3.2 `poslive/.env`

Nilai penting:

```env
APP_URL=http://poslive.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file

PLATFORM_DB_CONNECTION=mysql
PLATFORM_DB_HOST=127.0.0.1
PLATFORM_DB_PORT=3306
PLATFORM_DB_DATABASE=pos_platform
PLATFORM_DB_USERNAME=root
PLATFORM_DB_PASSWORD=

TENANCY_CENTRAL_HOSTS=127.0.0.1,localhost,poslive.test
TENANT_BOOTSTRAP_INPUT_DIRECTORY=/home/ACCOUNT/.tenant-bootstrap-input
```

Catatan:

- `SESSION_DRIVER=file` dipakai di tenant app agar onboarding tenant baru tidak langsung gagal bila tabel `sessions` tenant belum tersedia karena bootstrap gagal di tengah
- untuk single server, pilihan ini aman dan stabil

### 4. Setup Local dari Nol

#### 4.1 Platform App

Jalankan di folder `pos-platform`:

```bash
composer install
npm install
npm run build
php artisan migrate --force
php artisan platform:create-admin "Platform Owner" owner@platform.test secret123
```

#### 4.2 Tenant App

Jalankan di folder `poslive`:

```bash
composer install
npm install
npm run build
php artisan migrate --force
```

### 5. VHost / Domain Local

Di local Windows tanpa wildcard DNS, setiap subdomain tenant perlu entry manual di file `hosts`:

```txt
127.0.0.1    tenant-a.poslive.test
127.0.0.1    tenant-b.poslive.test
```

Untuk local, ini normal. Di production tidak dilakukan manual per-user.

### 6. Perintah Penting

#### Platform app

```bash
php artisan platform:create-admin "Platform Owner" owner@platform.test secret123
php artisan platform:provision-tenant 1
php artisan platform:bootstrap-tenant-app 1 "Owner Tenant" owner@example.com secret12345 --business="Usaha Awal" --branch="Cabang 1" --owner-role=administrator
```

#### Tenant app

```bash
php artisan tenant:bootstrap-database tenant_demo "Owner Tenant" owner@example.com secret12345 --driver=mysql --db-host=127.0.0.1 --db-port=3306 --db-username=root --db-password= --business="Usaha Awal" --branch="Cabang 1" --owner-role=administrator
```

### 7. Troubleshooting Umum

#### 7.1 `Path tenant app belum valid`

Penyebab:

- `PLATFORM_TENANT_APP_PATH` salah
- config cache lama

Solusi:

- pastikan path tenant app benar
- jalankan `php artisan config:clear` di `pos-platform`

#### 7.2 `PDOException SQLSTATE[HY000] [2002] Unknown error while connecting`

Kemungkinan:

- database tenant baru dibuat tapi belum siap dipakai stabil
- bootstrap tenant app gagal di migration tenant app
- path/command tenant app gagal dijalankan

Catatan:

- preflight koneksi + retry sudah ditambahkan
- jika error ini masih muncul, cek `Provision Log` tenant di panel admin

#### 7.3 `sessions table doesn't exist`

Penyebab:

- tenant DB belum selesai dibootstrap
- migration tenant app belum selesai

Mitigasi saat ini:

- tenant app memakai `SESSION_DRIVER=file`, jadi halaman login tidak lagi bergantung pada tabel `sessions` saat onboarding baru

#### 7.4 Domain tenant salah arah

Pastikan:

- `PLATFORM_TENANT_BASE_DOMAIN=poslive.test`
- tenant domain primary mengarah ke `*.poslive.test`, bukan `*.pos-platform.test`

### 8. Checklist Staging / Production

#### Infrastruktur

- siapkan 2 app:
  - `pos-platform`
  - `poslive`
- siapkan MySQL yang stabil
- buat satu direktori handoff bersama yang tidak dapat diakses web (permission `0700`), misalnya `/home/ACCOUNT/.tenant-bootstrap-input`; nilai environment platform dan POSLive harus identik
- atur Cron setiap menit untuk worker onboarding durable: `/usr/local/bin/php /home/ACCOUNT/PATH-PLATFORM/artisan schedule:run >> /dev/null 2>&1`
- pastikan PHP CLI yang menjalankan Cron dapat membaca dan menghapus file handoff mode `0600`, serta dapat menjalankan command POSLive dari path aplikasi platform
- siapkan SSL/TLS

#### DNS

- domain platform, contoh:
  - `platform.domainanda.com`
- domain tenant app, contoh:
  - `app.domainanda.com`
  - `*.app.domainanda.com`

Untuk SaaS subdomain tenant, gunakan wildcard DNS:

- `A record` atau `CNAME` untuk `*.app.domainanda.com`

#### Web Server

- arahkan domain platform ke `pos-platform/public`
- arahkan wildcard domain tenant ke `poslive/public`
- pastikan semua subdomain tenant diteruskan ke tenant app

#### Environment

##### `pos-platform`

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://platform.domainanda.com`
- `PLATFORM_TENANT_BASE_DOMAIN=app.domainanda.com`
- `PLATFORM_TENANT_APP_PATH=/absolute/path/to/poslive`
- isi DB admin + runtime tenant dengan credential production yang benar

##### `poslive`

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://app.domainanda.com`
- `TENANCY_CENTRAL_HOSTS=app.domainanda.com`
- `SESSION_DRIVER=file` untuk single server

#### Build & Cache

Di kedua app:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 9. Rekomendasi Produksi

#### Aman dipakai sekarang jika:

- satu server / satu VPS
- tenant app dan platform app ada di server yang sama atau path tenant app jelas
- session tenant app memakai `file`
- wildcard DNS sudah benar

#### Sebelum scale-up:

- pindahkan queue ke worker aktif
- pertimbangkan Redis untuk cache / queue / session
- audit semua migration tenant app agar tetap aman untuk fresh bootstrap
- tambahkan smoke test onboarding tenant di CI

### 10. Smoke Test Setelah Deploy

Lakukan test ini setelah deploy:

1. login ke panel platform admin
2. buat tenant baru
3. klik `Provision`
4. klik `Bootstrap Tenant App`
5. pastikan `Provision Log` menunjukkan `completed`
6. buka URL tenant `/login`
7. login dengan owner tenant

Jika semua lolos, flow onboarding tenant bisa dianggap sehat.
