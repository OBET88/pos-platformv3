# Local Setup

Gunakan satu sumber Git untuk kode. Setiap laptop tetap punya `.env`, database, dan hosts lokal sendiri.

## Setelah Clone

1. Copy `.env.example` menjadi `.env`.
2. Jalankan `composer install`.
3. Jalankan `npm install` jika asset frontend perlu dibuild.
4. Jalankan `php artisan key:generate` jika `APP_KEY` masih kosong.
5. Set database platform di `.env`, contoh Laragon:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_platform
DB_USERNAME=root
DB_PASSWORD=
```

6. Pastikan folder tenant app ada di sibling path:

```env
PLATFORM_TENANT_APP_PATH=../poslive
```

7. Di Windows/Laragon, jika background onboarding butuh binary PHP khusus, isi:

```env
PLATFORM_TENANT_APP_PHP_BINARY=G:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe
```

Kosongkan nilai ini di laptop yang tidak membutuhkannya.

## Alur Daftar Tenant Di Local

Saat `APP_ENV=local`, proses bootstrap tenant dijalankan sinkron lewat `resumeStoredAutoOnboarding`. Ini lebih lama, tetapi lebih stabil untuk laptop lokal.

Environment non-local tetap memakai background onboarding.

## Yang Tidak Dipush

Jangan commit file atau data lokal berikut:

- `.env`
- database lokal
- `storage/logs`
- `vendor`
- `node_modules`
- entry `hosts` Windows
- hasil testing tenant lokal

## Saat Pindah Laptop

Sebelum pull atau setelah pull:

1. Pastikan `.env` laptop itu masih sesuai.
2. Jalankan `php artisan config:clear`.
3. Restart Laragon bila ada perubahan `.env`, vhost, atau PHP path.
4. Jika tenant lokal dibuat, tambahkan domain tenant ke hosts Windows sesuai output halaman sukses atau command host entry.
