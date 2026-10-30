# Checklist Deployment Terpisah — DapurHosting dan IDwebhost

Gunakan checklist ini untuk mencegah file, database, domain, Cron, dan tenant tercampur antara dua hosting.

## Aturan sebelum mulai

- Bos Robert menentukan URL pendaftaran yang diberikan kepada calon tenant secara manual.
- URL `https://rwp.jajajuju.my.id/` berarti calon tenant masuk environment DapurHosting.
- URL platform IDwebhost berarti calon tenant masuk environment IDwebhost.
- Jangan pernah mencampur database, `.env`, directory bootstrap input, atau Cron antara kedua hosting.
- Jangan unggah `.env`, `vendor/`, atau `index.php` aktif dari local ke hosting.

---

## A. DapurHosting — Upgrade Existing dKriuk + Platform Existing

### Tujuan

Memperbarui code aplikasi yang sudah berjalan menjadi versi aplikasi saat ini tanpa memindahkan atau merusak dKriuk existing.

### A1. Backup dan freeze

- [ ] Catat waktu mulai dan versi/commit code yang sedang berjalan.
- [ ] Export database platform DapurHosting.
- [ ] Export database tenant dKriuk.
- [ ] Backup file aplikasi platform dan POSLive melalui File Manager.
- [ ] Pastikan tidak ada transaksi POS dKriuk, payment platform, atau onboarding tenant yang sedang diproses saat penggantian file sensitif dilakukan.
- [ ] Pastikan URL tenant dKriuk dan domain platform DapurHosting tidak akan diubah.

### A2. Upgrade POSLive dKriuk

- [ ] Ikuti `poslive/docs/updatev1.md` sebagai sumber daftar file replace.
- [ ] Upload semua file aplikasi yang tercantum sebelum membuka trigger update.
- [ ] Jangan upload `tests/`, `.env`, `vendor/`, atau mengganti `index.php` aktif.
- [ ] Untuk perubahan schema tenant, upload migration dan `app/Support/Updates/UpdateV1Runner.php` terlebih dahulu.
- [ ] Buka `public/update-v1.php` melalui **domain tenant dKriuk**, bukan domain pusat.
- [ ] Pastikan hasil halaman menyatakan update selesai dan seluruh check schema berstatus ada.
- [ ] Hapus `public/update-v1.php` setelah update tenant selesai.

### A3. Upgrade pos-platform DapurHosting

- [ ] Upload file platform versi yang sama dengan commit hardening onboarding `981fd18` atau versi penerusnya yang disetujui.
- [ ] Upload seluruh file sebelum menjalankan migration platform.
- [ ] Pastikan `PLATFORM_TENANT_APP_PATH` menunjuk ke POSLive **DapurHosting**, bukan folder IDwebhost.
- [ ] Pastikan `PLATFORM_TENANT_BOOTSTRAP_INPUT_DIRECTORY` adalah folder lokal DapurHosting di luar web root.
- [ ] Pastikan POSLive DapurHosting memakai `TENANT_BOOTSTRAP_INPUT_DIRECTORY` yang identik.
- [ ] Buat/cek permission directory handoff `0700` dan file input sementara `0600`.
- [ ] Jalankan migration database platform hanya melalui metode aman yang sudah tersedia/disetujui untuk DapurHosting.
- [ ] Pastikan tabel onboarding, secret, dan index payment event selesai dibuat sebelum Cron diaktifkan.

### A4. Cron dan smoke test DapurHosting

- [ ] Konfirmasi PHP CLI dan path `artisan` platform DapurHosting.
- [ ] Pasang/cek satu Cron `schedule:run` per menit pada platform DapurHosting.
- [ ] Login dashboard platform DapurHosting.
- [ ] Login dKriuk dan lakukan smoke test aman: login, menu inti, transaksi non-produksi bila tersedia, serta laporan dasar.
- [ ] Jika Bos Robert memberikan URL DapurHosting kepada calon tenant, uji registration → payment → worker → login tenant pada data uji yang aman.
- [ ] Pastikan DOKU endpoint DapurHosting hanya mencatat transaksi DapurHosting.

### A5. Recovery

- [ ] Jika upload gagal sebelum migration: rollback file platform/POSLive dari backup; Cron tetap dimatikan.
- [ ] Jika migration platform gagal: jangan nyalakan Cron atau menghapus tabel baru; catat error dan lakukan recovery terarah.
- [ ] Jika dKriuk bermasalah: prioritaskan restore code/data dKriuk, bukan provisioning tenant baru.

---

## B. IDwebhost — Instalasi Fresh dari Nol

### Tujuan

Membuat environment SaaS baru yang bersih untuk tenant yang nanti Bos Robert arahkan ke URL IDwebhost.

### B1. Siapkan struktur hosting

- [ ] Tentukan folder aplikasi `pos-platform` IDwebhost.
- [ ] Tentukan folder aplikasi `poslive` IDwebhost.
- [ ] Pastikan kedua folder berada pada account hosting IDwebhost yang sama.
- [ ] Arahkan domain platform ke `pos-platform/public`.
- [ ] Arahkan domain/wildcard tenant ke `poslive/public`.
- [ ] Siapkan SSL untuk domain platform dan wildcard/subdomain tenant.
- [ ] Buat database platform fresh IDwebhost beserta user khususnya.
- [ ] Jangan menggunakan database atau credential DapurHosting.

### B2. Upload dan konfigurasi fresh

- [ ] Upload code `pos-platform` dan `poslive` yang kompatibel.
- [ ] Jangan upload `.env` local, `vendor/`, atau `public/build` yang tidak sesuai hasil build release.
- [ ] Buat `.env` masing-masing aplikasi dari template production melalui mekanisme hosting yang aman.
- [ ] Set `APP_ENV=production` dan `APP_DEBUG=false` di kedua aplikasi.
- [ ] Set `PLATFORM_TENANT_APP_PATH` ke POSLive **IDwebhost**.
- [ ] Set path PHP CLI IDwebhost pada `PLATFORM_TENANT_APP_PHP_BINARY`.
- [ ] Buat directory input bootstrap lokal di luar web root, permission `0700`.
- [ ] Set `PLATFORM_TENANT_BOOTSTRAP_INPUT_DIRECTORY` dan `TENANT_BOOTSTRAP_INPUT_DIRECTORY` ke path identik.
- [ ] Isi konfigurasi database platform, database tenant, central hosts, domain tenant, dan DOKU hanya dengan nilai IDwebhost.

### B3. Inisialisasi database

- [ ] Jalankan seluruh migration database platform melalui mekanisme aman yang disetujui.
- [ ] Jalankan migration POSLive central/fresh database sesuai prosedur deployment yang tersedia.
- [ ] Verifikasi tabel platform inti, tabel onboarding, tabel payment events, plan, role/permission, dan platform admin tersedia.
- [ ] Buat platform administrator pertama melalui jalur aman yang disetujui.
- [ ] Jangan memasukkan master produk, recipe, stock, atau data tenant dengan seeder; data master diisi lewat menu setelah tenant tersedia.

### B4. Cron dan capability check

- [ ] Konfirmasi binary PHP CLI IDwebhost.
- [ ] Konfirmasi path absolut file `pos-platform/artisan`.
- [ ] Pasang Cron per menit:

```cron
* * * * * <PHP_CLI_IDWEBHOST> <POS_PLATFORM_IDWEBHOST>/artisan schedule:run >> /dev/null 2>&1
```

- [ ] Verifikasi Cron berjalan dari log/indikator hosting yang aman.
- [ ] Verifikasi platform CLI dapat menjalankan child command POSLive pada host yang sama.
- [ ] Verifikasi MySQL/cPanel privilege dapat membuat database tenant, user, dan grant yang dibutuhkan, menggunakan data test non-produksi.

### B5. Go-live smoke test IDwebhost

- [ ] Buka URL platform IDwebhost via HTTPS.
- [ ] Login administrator platform.
- [ ] Pastikan public registration, invoice, dan DOKU configuration menampilkan environment IDwebhost.
- [ ] Buat satu tenant test dengan domain test.
- [ ] Verifikasi flow: verification email → invoice/payment → onboarding job → Cron → owner/business/branch → tenant login.
- [ ] Pastikan database tenant test dibuat di MySQL IDwebhost.
- [ ] Pastikan access token status tanpa token ditolak.
- [ ] Pastikan webhook DOKU valid diproses satu kali saja dan reference harus cocok.
- [ ] Hapus/cleanup tenant test hanya melalui prosedur yang aman setelah smoke test jika diperlukan.

---

## C. Keputusan Manual Pemberian URL

Sebelum mengirim URL kepada calon pelanggan, Bos Robert menentukan environmentnya:

| Pilihan Bos Robert | URL yang dikirim | Tempat tenant dibuat |
|---|---|---|
| Gunakan DapurHosting | `https://rwp.jajajuju.my.id/` | DapurHosting |
| Gunakan IDwebhost | URL platform IDwebhost | IDwebhost |

Setelah calon tenant mulai registration atau payment, jangan mengganti host untuk tenant tersebut tanpa project migrasi khusus.
