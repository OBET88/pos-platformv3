# POS Platform SaaS

Repo ini adalah aplikasi `pos-platform`, yaitu panel pusat untuk mengelola tenant SaaS POS. Aplikasi tenant POS berada di repo/folder terpisah, yaitu `poslive`.

## Gambaran Singkat

- `pos-platform`:
  - menerima registrasi tenant
  - menyimpan data tenant pusat
  - membuat database tenant
  - memanggil bootstrap tenant app
  - menyediakan panel admin platform
- `poslive`:
  - aplikasi operasional POS untuk tenant
  - memakai resolusi tenant berbasis domain/subdomain
  - setiap tenant memakai database sendiri

## Status Implementasi Saat Ini

Flow yang sudah tersedia:

1. registrasi tenant di platform
2. provisioning database tenant
3. bootstrap tenant app
4. pembuatan owner tenant awal
5. login tenant lewat domain tenant

Hardening yang sudah diterapkan:

- domain suggestion tenant disatukan agar konsisten ke base domain tenant app
- provisioning menunggu database tenant benar-benar siap diakses
- bootstrap tenant app punya preflight koneksi + retry
- tenant app memakai `SESSION_DRIVER=file` agar onboarding tenant baru tidak langsung patah bila migration tenant belum selesai

## Dokumen Utama

Lihat panduan lengkap di:

- [docs/SAAS_DEPLOYMENT_GUIDE.md](docs/SAAS_DEPLOYMENT_GUIDE.md)

Dokumen itu mencakup:

- arsitektur singkat
- env wajib untuk `pos-platform` dan `poslive`
- setup local
- alur onboarding tenant
- troubleshooting umum
- checklist staging / production
- smoke test setelah deploy

## Perintah Penting

### Platform app

```bash
php artisan platform:create-admin "Platform Owner" owner@platform.test secret123
php artisan platform:provision-tenant 1
php artisan platform:bootstrap-tenant-app 1 "Owner Tenant" owner@example.com secret12345 --business="Usaha Awal" --branch="Cabang 1" --owner-role=administrator
```

### Tenant app

```bash
php artisan tenant:bootstrap-database tenant_demo "Owner Tenant" owner@example.com secret12345 --driver=mysql --db-host=127.0.0.1 --db-port=3306 --db-username=root --db-password= --business="Usaha Awal" --branch="Cabang 1" --owner-role=administrator
```

## Catatan Operasional

- di local Windows, subdomain tenant perlu entry manual di file `hosts`
- di production, gunakan wildcard DNS dan wildcard vhost/server block
- untuk single server, `SESSION_DRIVER=file` pada tenant app adalah pilihan stabil

## Next Step yang Disarankan

- siapkan DNS + wildcard subdomain untuk staging/production
- siapkan vhost/platform app dan tenant app terpisah
- tambahkan smoke test onboarding tenant ke pipeline deploy/CI
