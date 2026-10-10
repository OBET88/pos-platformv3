# Catatan Progres Integrasi DOKU

Tanggal catatan: 7 Mei 2026  
Project: `pos-platform`  
Fokus tahap ini: pembayaran paket tenant saja

## 1. Scope yang Disepakati

Integrasi DOKU tahap awal hanya untuk:

- pendaftaran tenant berbayar
- perpanjangan subscription tenant
- upgrade paket tenant

Belum masuk ke:

- pembayaran POS kasir di `poslive`
- split bill POS
- payment gateway transaksi toko

Catatan scope awal juga sudah disimpan di:

- [doku-integration-scope-note-2026-05-07.md](D:\laragon\www\pos-platform\docs\doku-integration-scope-note-2026-05-07.md)

## 2. Yang Seharusnya Dilakukan Dari Awal

Kalau nanti proses ini diulang atau dilanjutkan lagi, urutan yang benar seharusnya seperti ini:

1. Tentukan scope integrasi lebih dulu.
2. Pisahkan antara kebutuhan `pos-platform` dan `poslive`.
3. Pastikan flow bisnis mana yang mau dibayar lewat DOKU.
4. Audit kode yang sudah ada sebelum bikin dari nol.
5. Ambil credential DOKU yang benar:
   - `sandbox` untuk testing awal
   - `production` hanya saat siap live
6. Pastikan channel payment yang akan dipakai dulu.
7. Tentukan URL webhook/notifikasi yang akan dipakai DOKU.
8. Baru lakukan testing end-to-end.

## 3. Temuan Awal di Kode

Sebelum perubahan baru, hasil audit menunjukkan:

- `renewal` sudah siap membuat invoice platform
- `upgrade` juga sudah siap membuat invoice platform
- `registration` belum invoice-first, karena sebelumnya setelah verifikasi email tenant langsung dibuat dan trial langsung jalan

Fondasi DOKU yang ternyata sudah ada:

- route payment dan webhook di [routes/web.php](D:\laragon\www\pos-platform\routes\web.php)
- controller payment di [PlatformPaymentController.php](D:\laragon\www\pos-platform\app\Http\Controllers\PlatformPaymentController.php)
- service checkout di [DokuCheckoutService.php](D:\laragon\www\pos-platform\app\Services\Platform\DokuCheckoutService.php)
- config DOKU di [services.php](D:\laragon\www\pos-platform\config\services.php)

## 4. Perubahan yang Sudah Dikerjakan

### A. Registration berbayar sekarang masuk invoice dulu

Perubahan inti:

- plan `trial` tetap mengikuti alur lama
- plan berbayar sekarang setelah verifikasi email akan:
  - buat tenant status `pending_payment`
  - buat subscription status `pending_payment`
  - buat `PlatformPaymentTransaction` type `registration`
  - arahkan user ke halaman invoice payment

File utama:

- [PlatformRegistrationController.php](D:\laragon\www\pos-platform\app\Http\Controllers\PlatformRegistrationController.php)
- [TenantRegistrationService.php](D:\laragon\www\pos-platform\app\Services\Platform\TenantRegistrationService.php)

### B. Setelah payment sukses, onboarding tenant baru berjalan

Perubahan inti:

- saat webhook DOKU sukses untuk invoice `registration`
- payment ditandai `paid`
- subscription diaktifkan
- onboarding tenant dilanjutkan dari payload yang sudah disimpan

File utama:

- [PaymentActivationService.php](D:\laragon\www\pos-platform\app\Services\Platform\PaymentActivationService.php)
- [TenantRegistrationService.php](D:\laragon\www\pos-platform\app\Services\Platform\TenantRegistrationService.php)

### C. Provisioning tidak lagi menimpa tenant aktif hasil payment

Perubahan inti:

- provisioning tenant sebelumnya bisa mengembalikan status ke `trial`
- sekarang status `active` dipertahankan saat onboarding setelah payment registration

File utama:

- [TenantProvisioningService.php](D:\laragon\www\pos-platform\app\Services\Platform\TenantProvisioningService.php)

### D. Halaman invoice dibuat lebih jelas untuk registration

Perubahan inti:

- copy halaman payment dibedakan untuk `registration`
- ada penjelasan bahwa tenant belum disiapkan sebelum invoice lunas
- setelah lunas, user bisa lanjut ke status setup tenant

File utama:

- [payment-manual.blade.php](D:\laragon\www\pos-platform\resources\views\platform\payment-manual.blade.php)

## 5. Test yang Sudah Berhasil

Sudah dijalankan dan lolos:

- `php artisan test --filter=PlatformRegistrationTest`
- `php artisan test --filter=PlatformDokuWebhookTest`

Artinya saat ini sudah terverifikasi:

- registration trial lama tetap aman
- registration berbayar sekarang membuat invoice
- webhook sukses bisa mengaktifkan registration berbayar
- renewal dan upgrade webhook tidak rusak

## 6. Riset Dashboard DOKU yang Sudah Dilakukan

Kita sudah cek dashboard DOKU dan menyimpulkan:

- `API Keys` adalah tempat mengambil `Client ID` dan `Secret Key`
- `HTTP Notifications` bukan tempat mengambil credential
- `HTTP Notifications` lebih cocok untuk log/notifikasi yang terkirim
- webhook/payment notification biasanya diatur per channel payment

Link docs resmi yang sudah dipakai:

- [Set Up Integration](https://docs.doku.com/get-started/manage-business/set-up-integration)
- [Webhook / Payment Notification](https://docs.doku.com/get-started/manage-business/set-up-integration/webhook-payment-notification)
- [Configure Notifications](https://docs.doku.com/accept-payments/integration-tools/doku-checkout/configure-notifications)

## 7. Hal yang Masih Belum Tuntas

Bagian yang masih harus dipastikan sebelum testing sandbox sungguhan:

- apakah akun DOKU yang sedang dibuka benar `sandbox` atau `production`
- secret key yang terlanjur pernah terlihat sebaiknya di-rotate
- channel payment yang mau dipakai dulu:
  - `QRIS`
  - `Virtual Account`
- URL webhook untuk channel yang dipakai harus diisi

## 8. Nilai `.env` yang Nanti Dibutuhkan

Saat lanjut nanti, yang perlu diisi di `.env` `pos-platform`:

```env
DOKU_ENABLED=true
DOKU_BASE_URL=https://api-sandbox.doku.com
DOKU_CLIENT_ID=
DOKU_SECRET_KEY=
DOKU_PAYMENT_METHOD_TYPES=QRIS,VIRTUAL_ACCOUNT_BCA,VIRTUAL_ACCOUNT_BANK_MANDIRI,VIRTUAL_ACCOUNT_BRI,VIRTUAL_ACCOUNT_BNI
```

Catatan:

- `DOKU_BASE_URL` harus sandbox saat testing
- jangan isi secret key lama yang sudah pernah terekspos

## 9. Langkah Lanjut Nanti

Saat ingin lanjut lagi, buka dokumen ini dulu lalu lanjut dengan urutan:

1. pastikan akun DOKU itu sandbox atau production
2. ambil `Client ID` dan `Secret Key` yang benar
3. rotate secret key bila perlu
4. isi `.env` `pos-platform`
5. tentukan channel payment awal
6. isi webhook notification URL di dashboard DOKU
7. test flow:
   - registration berbayar
   - renewal
   - upgrade
8. validasi status invoice, subscription, dan onboarding tenant

## 10. Ringkasan Singkat

Status terakhir:

- kode `pos-platform` untuk DOKU paket tenant sudah siap dasar
- registration berbayar sudah berubah ke invoice-first
- activation after payment sudah berjalan
- test utama sudah hijau
- bagian yang tersisa sekarang lebih banyak di konfigurasi akun DOKU dan sandbox testing nyata
