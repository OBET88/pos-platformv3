# Runbook Rilis IDwebhost — Platform Onboarding Tenant

Dokumen ini adalah prosedur rilis **khusus IDwebhost** untuk hardening onboarding tenant pada dua aplikasi yang berada pada satu shared-hosting account:

- `pos-platform`: control plane, registration, subscription, invoice, DOKU, dan worker onboarding;
- `poslive`: aplikasi POS tenant yang dibootstrap oleh platform.

Dokumen ini tidak memuat password, token, credential database, atau URL cPanel bersesi.

> **Batas environment:** Bos Robert memilih URL pendaftaran secara manual untuk setiap calon pelanggan. DapurHosting dan IDwebhost masing-masing memiliki platform, database, POSLive, Cron, dan directory handoff sendiri. Tidak ada redirect otomatis atau perpindahan tenant antar-host. Baca `docs/HOSTING_ENVIRONMENT_BOUNDARIES.md` sebelum rilis.

## 1. Kondisi wajib sebelum rilis

Jangan mulai upload sebelum seluruh poin berikut sudah dikonfirmasi.

1. Ada backup file aplikasi dan export database **platform** serta database tenant dKriuk.
2. Kedua aplikasi berada di account hosting yang sama dan PHP CLI yang sama dapat membaca keduanya.
3. File Manager dapat membuat direktori di luar web root.
4. cPanel Cron Jobs tersedia.
5. Ada cara yang telah disetujui untuk menjalankan **migration database platform**. Rilis ini menambah tabel dan index di database platform.
6. DOKU webhook production masih menunjuk ke HTTPS endpoint platform yang benar.
7. Tidak ada onboarding tenant baru yang sedang `running` ketika rilis dimulai. Tenant yang `pending_payment` boleh ada karena migration backfill merekonsiliasi state-nya.

### Release gate: migration platform

Hosting POSLive tidak memiliki terminal, sehingga `php artisan migrate` **tidak boleh diasumsikan tersedia**.

Commit onboarding ini menambah migration platform berikut:

- `2026_10_23_010000_create_tenant_bootstrap_secrets_table.php`
- `2026_10_23_020000_create_tenant_onboarding_jobs_table.php`
- `2026_10_23_030000_backfill_legacy_tenant_onboarding_state.php`
- `2026_10_23_040000_add_provider_event_fingerprint_to_payment_events.php`

**Jangan mengaktifkan Cron worker dan jangan menerima onboarding baru sebelum keempat migration platform ini benar-benar berhasil.**

Jika IDwebhost tidak menyediakan terminal aplikasi atau mekanisme migration platform yang sudah disetujui, rilis berhenti pada tahap pre-flight. Jangan membuat endpoint `migrate` publik secara darurat. Solusi yang aman harus ditinjau terpisah: trigger one-time yang dibatasi secret, IP/administrator, status migration idempoten, dan dihapus segera setelah berhasil.

## 2. Nilai/path yang harus diputuskan di cPanel

Ganti placeholder di bawah dengan path production sebenarnya; jangan simpan nilai sensitif di dokumen ini.

| Placeholder | Keterangan |
|---|---|
| `<HOME>` | Home directory account cPanel, misalnya `/home/ACCOUNT` |
| `<PLATFORM_APP>` | Folder aplikasi `pos-platform` di luar atau terpisah dari web root |
| `<POSLIVE_APP>` | Folder aplikasi `poslive` |
| `<PHP_CLI>` | Binary PHP CLI yang dikonfirmasi hosting, misalnya `/usr/local/bin/php` |
| `<SHARED_INPUT_DIR>` | `<HOME>/.tenant-bootstrap-input` |

`<SHARED_INPUT_DIR>` harus:

- berada di luar `public_html` dan bukan di bawah `public/` aplikasi;
- dimiliki oleh user cPanel yang sama;
- permission directory `0700`;
- berisi file sementara yang dibuat dengan permission `0600`;
- tidak digunakan sebagai tempat upload atau penyimpanan permanen.

Konfigurasi environment berikut harus menunjuk path yang **sama persis**:

```env
# pos-platform/.env
PLATFORM_TENANT_APP_PATH=<POSLIVE_APP>
PLATFORM_TENANT_APP_PHP_BINARY=<PHP_CLI>
PLATFORM_TENANT_BOOTSTRAP_INPUT_DIRECTORY=<SHARED_INPUT_DIR>

# poslive/.env
TENANT_BOOTSTRAP_INPUT_DIRECTORY=<SHARED_INPUT_DIR>
```

Tambahkan juga domain production yang benar, `APP_ENV=production`, `APP_DEBUG=false`, serta konfigurasi platform/tenant database sesuai hosting. Jangan mengunggah `.env`; edit nilai environment hanya lewat mekanisme cPanel yang disetujui.

## 3. Pre-flight operasional

Sebelum mode maintenance atau upload:

1. Catat versi/commit aplikasi yang sedang berjalan dan waktu rilis.
2. Pastikan dashboard platform masih dapat dibuka oleh admin.
3. Catat tenant yang status onboarding-nya `queued`, `running`, `retry_wait`, `failed`, atau `blocked`.
4. Pastikan tidak ada pembayaran DOKU atau approval manual yang sedang diproses oleh operator.
5. Verifikasi webhook URL DOKU memakai HTTPS dan bukan URL development.
6. Siapkan salinan file lama untuk rollback aplikasi. Jangan mengganti `.env`, `vendor/`, atau web entrypoint yang sedang berjalan.
7. Pastikan build frontend telah dikerjakan sebelum upload bila ada aset frontend yang berubah. Rilis hardening ini tidak memerlukan build baru secara khusus, tetapi file build existing tidak boleh dihapus.

## 4. Urutan upload aman

### 4.1 Upload POSLive compatibility terlebih dahulu

Upload hanya file POSLive yang menjadi kontrak bootstrap aman:

- `config/tenancy.php`
- `routes/console.php`

Jika file tersebut ikut rilis ke hosting dKriuk, tambahkan ke daftar replace di `docs/updatev1.md` sesuai aturan aplikasi POSLive. Jangan upload `tests/`, `.env`, `vendor/`, atau mengganti `index.php` yang berjalan.

Tidak ada perubahan schema tenant baru yang khusus diperlukan oleh kontrak `--bootstrap-input`. Jangan menjalankan `update-v1.php` hanya untuk dua file ini.

### 4.2 Upload file platform

Upload seluruh file dari commit hardening platform, termasuk model, service, controller, view, config, route, test-excluded production files, dan empat migration pada bagian release gate.

Penting:

- upload semua file aplikasi lebih dahulu;
- jangan menjalankan migration di tengah upload;
- jangan menjalankan Cron worker sebelum migration selesai;
- jangan menimpa `.env`, `vendor/`, atau konfigurasi domain/cPanel yang sudah aktif.

### 4.3 Jalankan migration platform melalui metode yang disetujui

Jalankan migration platform hanya setelah seluruh file aplikasi tersedia.

Validasi minimum setelah migration:

1. tabel `tenant_bootstrap_secrets` ada;
2. tabel `tenant_onboarding_jobs` ada;
3. kolom `provider_event_key` dan unique index event provider ada pada `platform_payment_events`;
4. record onboarding legacy yang dapat dimigrasikan memiliki secret + job;
5. record legacy yang tidak memiliki secret terlihat sebagai job `failed`/log recovery, bukan hilang diam-diam.

Jika langkah migration gagal sebagian, **jangan nyalakan Cron**, jangan hapus file lama, dan eskalasi ke operator. Migration backfill mengubah state onboarding; rollback database tidak boleh dilakukan sembarangan.

## 5. Konfigurasi Cron

Setelah migration lulus, buat satu Cron Job cPanel setiap menit:

```cron
* * * * * <PHP_CLI> <PLATFORM_APP>/artisan schedule:run >> /dev/null 2>&1
```

Contoh bentuk yang sudah dikonfirmasi untuk account ini:

```cron
* * * * * /usr/local/bin/php /home/nuhafran/<path-pos-platform>/artisan schedule:run >> /dev/null 2>&1
```

Jangan memakai path contoh sebelum `artisan` platform dan PHP CLI diverifikasi dari cPanel.

Scheduler akan menjalankan satu onboarding job per menit melalui:

```bash
platform:work-tenant-onboarding --once --max=1
```

Jangan membuat Cron kedua yang memanggil worker tersebut langsung; satu `schedule:run` adalah sumber orkestrasi.

## 6. Smoke test setelah rilis

Lakukan di tenant/uji yang aman, bukan data operasional dKriuk.

### A. Capability dan payment gate

1. Daftar memakai plan berbayar dengan email uji.
2. Verifikasi email.
3. Pastikan browser menuju invoice pembayaran, bukan login tenant atau bootstrap selesai.
4. Pastikan status tenant/subscription tetap `pending_payment` sebelum payment valid.
5. Pastikan URL status onboarding tanpa parameter access ditolak.
6. Buka invoice dengan access token dari alur pendaftaran lalu pastikan tautan status setup tersedia.

### B. Manual payment dan worker

1. Kirim bukti payment lalu approve dari admin, atau gunakan payment DOKU sandbox yang telah disetujui.
2. Pastikan invoice menjadi paid satu kali saja.
3. Tunggu maksimal dua siklus Cron.
4. Pastikan job berubah `blocked` → `queued` → `running` → `completed`.
5. Pastikan secret bootstrap dibuang setelah completion.
6. Pastikan tenant DB, migration POSLive, owner, business, dan branch terbentuk.
7. Login lewat domain tenant menggunakan owner test.

### C. DOKU safety

1. Kirim/observasi satu webhook valid sesuai checkout yang dibuat.
2. Pastikan reference checkout lokal dan webhook cocok.
3. Pastikan webhook duplikat tidak memperpanjang subscription lagi.
4. Pastikan status FAILED/EXPIRED yang terlambat tidak menurunkan invoice yang sudah paid.
5. Pastikan invalid signature tidak mengubah invoice/subscription.

### D. Admin recovery

1. Pada tenant test yang dibuat gagal secara terkendali, jalankan recovery admin.
2. Pastikan job menjadi completed dan tidak diklaim ulang Cron.
3. Pastikan status publik merefleksikan completion.

## 7. Observability dan tindakan bila gagal

| Kondisi | Tindakan |
|---|---|
| Job `retry_wait` | Biarkan Cron melakukan retry sesuai backoff; halaman status tetap polling. |
| Job `failed` | Jangan membagikan error mentah ke pelanggan. Admin tinjau provision log, perbaiki penyebab, lalu recovery/requeue melalui jalur yang disetujui. |
| Job `blocked` setelah paid | Verifikasi activation payment dan job durable; jangan bootstrap manual memakai credential dari log. |
| Input file tertinggal | Periksa permission dan path aplikasi; hapus hanya setelah memastikan tidak ada worker running. File harus berada di directory handoff, bukan web root. |
| Webhook DOKU ditolak | Cek konfigurasi DOKU Client ID, timestamp, raw body/signature, dan provider reference. Jangan tandai invoice paid secara manual tanpa validasi payment evidence. |
| Cron tidak berjalan | Jangan mengandalkan browser status page untuk bootstrap. Perbaiki Cron cPanel lebih dahulu, kemudian requeue/recover job yang aman. |

## 8. Rollback aplikasi

1. Hentikan/disable Cron `schedule:run` terlebih dahulu agar tidak ada child bootstrap baru.
2. Jangan menghapus tabel atau data onboarding/payment baru untuk rollback cepat.
3. Kembalikan file aplikasi platform dan POSLive ke versi backup secara konsisten.
4. Jangan memulihkan database backup setelah payment baru masuk tanpa keputusan insiden, karena dapat menghilangkan audit/event pembayaran.
5. Catat tenant/job yang sedang `running` atau `queued` lalu lakukan recovery setelah versi stabil kembali.
6. Aktifkan Cron hanya setelah health check aplikasi dan database selesai.

## 9. Konfirmasi yang masih diperlukan dari Bos Robert / cPanel

Sebelum rilis production, konfirmasi eksplisit:

1. Path absolut `<PLATFORM_APP>` dan `<POSLIVE_APP>`.
2. Apakah File Manager dapat membuat `<HOME>/.tenant-bootstrap-input` dengan permission `0700`.
3. Path PHP CLI yang tersedia dan apakah Cron cPanel dapat menjalankan `artisan schedule:run`.
4. Mekanisme aman untuk menjalankan migration **platform** tanpa terminal.
5. Apakah PHP CLI dapat menjalankan child process POSLive dari platform path.
6. Apakah cPanel database API/privilege MySQL yang dipakai provisioning tenant tersedia; uji dengan tenant non-produksi terlebih dahulu.
7. Domain platform, wildcard tenant, SSL, dan DNS production yang digunakan.

Rilis hanya dapat direkomendasikan setelah tujuh poin tersebut terjawab dan smoke test berhasil.
