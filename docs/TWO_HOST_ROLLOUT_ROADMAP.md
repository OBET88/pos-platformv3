# Roadmap Rilis Dua Hosting — dKriuk Existing dan Resto SaaS Baru

## Keputusan arsitektur

Rilis dibagi menjadi dua jalur independen:

1. **DapurHosting:** upgrade aplikasi existing tanpa memindahkan atau mengubah domain dKriuk.
2. **IDwebhost:** instalasi fresh untuk produk Resto/Cafe dengan URL tenant pendek.

Jalur pertama tidak menunggu jalur kedua. Kegagalan atau penundaan IDwebhost tidak boleh mengganggu dKriuk.

## Tahap 1 — Upgrade aman DapurHosting

### Target domain yang tidak berubah

| Fungsi | URL |
|---|---|
| Platform Resto DapurHosting | `https://rwp.jajajuju.my.id/` |
| Login admin platform DapurHosting | `https://rwp.jajajuju.my.id/platform-login` |
| Tenant dKriuk existing | `https://dkriuk.jajajuju.my.id/login` |

### Scope

- Upgrade code POSLive dKriuk ke versi aplikasi saat ini, memakai prosedur hosting existing.
- Upgrade `pos-platform` DapurHosting ke hardening onboarding/payment yang sudah direview.
- Pertahankan seluruh database, domain, tenant code, dan credential dKriuk existing.
- Siapkan onboarding baru DapurHosting hanya jika Bos Robert secara manual memberikan URL `rwp.jajajuju.my.id` kepada calon pelanggan.

### Urutan eksekusi

1. Backup penuh file aplikasi dan export database platform + database dKriuk.
2. Pilih jendela maintenance dKriuk; jangan melakukan perubahan besar saat kasir aktif.
3. Upload semua file POSLive sesuai `poslive/docs/updatev1.md`.
4. Jalankan browser trigger `update-v1.php` lewat domain tenant dKriuk bila perubahan schema POSLive ikut dirilis.
5. Hapus trigger browser setelah semua check sukses.
6. Upload semua file `pos-platform` yang disetujui.
7. Jalankan migration platform melalui metode aman yang disetujui untuk DapurHosting. Jangan membuat endpoint migration publik spontan.
8. Konfigurasi directory handoff lokal DapurHosting dan Cron scheduler DapurHosting.
9. Jalankan smoke test dKriuk dan platform DapurHosting.
10. Jika ada kegagalan, nonaktifkan Cron onboarding lebih dulu lalu lakukan recovery berdasar backup; prioritasnya pemulihan dKriuk.

### Kriteria selesai

- dKriuk dapat login, POS dan modul operasional inti tetap berjalan.
- Platform DapurHosting dapat diakses di URL existing.
- Tidak ada perubahan ke domain dKriuk.
- Cron DapurHosting, bila sudah dikonfirmasi capability-nya, hanya mengelola job/database DapurHosting.

## Tahap 2 — Fresh Resto SaaS di IDwebhost

### Domain target

| Fungsi | URL/DNS |
|---|---|
| Landing induk | `https://nuhafrandesa.com` |
| Platform Resto/Cafe | `https://resto.nuhafrandesa.com` |
| Login admin platform Resto | `https://resto.nuhafrandesa.com/platform-login` |
| Tenant Resto/Cafe | `https://<kode-tenant>.nuhafrandesa.com/login` |
| Platform Minimarket masa depan | `https://minimarket.nuhafrandesa.com` |

Contoh tenant Resto: `https://ayamku.nuhafrandesa.com/login`.

### DNS dan web-root

1. `nuhafrandesa.com` diarahkan ke landing page induk.
2. `resto.nuhafrandesa.com` diarahkan ke `pos-platform/public` IDwebhost.
3. `*.nuhafrandesa.com` diarahkan ke `poslive/public` IDwebhost.
4. `minimarket.nuhafrandesa.com` dicadangkan untuk platform minimarket dan harus dibuat sebagai host eksplisit, sehingga mengalahkan wildcard ketika aplikasi minimarket siap.
5. Sertifikat SSL harus mencakup root domain, `resto`, `minimarket`, dan wildcard `*.nuhafrandesa.com`.

Pada shared hosting, poin 2-4 harus diuji di cPanel/IDwebhost. Wildcard subdomain hanya boleh menuju satu tenant application pada fase ini: **POSLive Resto**.

### Environment Resto IDwebhost

Konfigurasi target utama:

```env
# pos-platform IDwebhost
APP_URL=https://resto.nuhafrandesa.com
PLATFORM_TENANT_BASE_DOMAIN=nuhafrandesa.com
PLATFORM_TENANT_APP_PATH=<PATH_POSLIVE_IDWEBHOST>
PLATFORM_TENANT_BOOTSTRAP_INPUT_DIRECTORY=<HOME_IDWEBHOST>/.tenant-bootstrap-input

# poslive IDwebhost
APP_URL=https://nuhafrandesa.com
TENANCY_CENTRAL_HOSTS=nuhafrandesa.com,www.nuhafrandesa.com,resto.nuhafrandesa.com,minimarket.nuhafrandesa.com
TENANT_BOOTSTRAP_INPUT_DIRECTORY=<HOME_IDWEBHOST>/.tenant-bootstrap-input
```

`PLATFORM_TENANT_BASE_DOMAIN=nuhafrandesa.com` membuat domain onboarding tenant baru menjadi `<kode>.nuhafrandesa.com`, bukan `<kode>.resto.nuhafrandesa.com`.

### Urutan eksekusi

1. Buat database platform fresh IDwebhost; jangan gunakan DB DapurHosting.
2. Upload application platform dan POSLive yang kompatibel ke folder berbeda pada account IDwebhost yang sama.
3. Konfigurasikan environment IDwebhost terpisah; jangan menyalin `.env` DapurHosting.
4. Buat directory secure handoff di luar web root dengan permission `0700`.
5. Jalankan migration platform dan central POSLive melalui metode deployment yang disetujui.
6. Buat administrator platform pertama.
7. Buat Cron `schedule:run` IDwebhost setelah migration sukses.
8. Konfigurasikan DOKU khusus IDwebhost dan webhook endpoint `resto.nuhafrandesa.com`.
9. Uji satu tenant non-produksi dari verification sampai login menggunakan hostname pendek test.
10. Baru setelah test lulus, Bos Robert dapat memberikan `resto.nuhafrandesa.com` kepada calon pelanggan Resto/Cafe.

## Reserved names pada root wildcard

Nama berikut tidak boleh dipakai sebagai tenant code/domain di IDwebhost karena dipakai layanan pusat atau disiapkan untuk produk lain:

```text
www
resto
minimarket
admin
api
app
mail
ftp
cpanel
webmail
support
status
```

Sebelum membuka registration Resto IDwebhost, aplikasi perlu memiliki validasi tenant-code/domain yang menolak daftar reserved tersebut. Ini adalah requirement implementasi sebelum go-live wildcard root.

## Tahap 3 — Minimarket dan domain router (belum dikerjakan)

Saat platform minimarket siap, `minimarket.nuhafrandesa.com` menjadi landing/registration minimarket. Namun wildcard root saat itu telah dipakai tenant Resto.

Sebelum tenant minimarket dapat memakai URL pendek seperti `tokoberkah.nuhafrandesa.com`, perlu dibuat dan diuji **domain router pusat** yang:

1. menerima request root wildcard;
2. mencari hostname pada registry domain pusat;
3. menentukan produk tenant (`resto` atau `minimarket`);
4. meneruskan request secara aman ke aplikasi tenant yang tepat;
5. menolak host tak dikenal secara fail-closed.

Router tersebut tidak diperlukan untuk Tahap 1 maupun Tahap 2, karena pada Tahap 2 seluruh root wildcard tenant adalah tenant Resto.

## Keputusan manual Bos Robert

Bos Robert tetap menentukan URL pendaftaran untuk setiap calon pelanggan:

- memberi `https://rwp.jajajuju.my.id/` → registration masuk DapurHosting;
- memberi `https://resto.nuhafrandesa.com/` → registration masuk IDwebhost Resto.

Setelah calon pelanggan memulai registration/payment pada satu host, tenant tersebut tetap pada host itu kecuali ada migrasi khusus yang direncanakan.
