# Batas Environment Hosting — DapurHosting dan IDwebhost

Dokumen ini menetapkan pemisahan operasional dua environment POSLive. Tujuannya adalah melindungi operasional dKriuk sambil menyiapkan jalur SaaS baru yang bersih.

## Prinsip utama

`pos-platform` yang berada di DapurHosting dan IDwebhost adalah **dua deployment production setara tetapi terpisah**. Masing-masing memiliki:

- database platform sendiri;
- daftar tenant sendiri;
- konfigurasi DOKU sendiri;
- database tenant sendiri;
- Cron dan directory bootstrap input sendiri.

Tenant/database dari satu host tidak boleh diprovision, dibootstrap, atau dikelola oleh platform pada host lainnya.

Pilihan URL pendaftaran dilakukan **manual oleh Bos Robert** saat menerima calon pelanggan. Tidak ada aturan otomatis, redirect otomatis, kuota satu tenant, atau pemindahan calon pendaftar antar-host.

## 1. DapurHosting — Existing/Protected Environment

### Fungsi

- Menjalankan POS dKriuk yang sudah aktif.
- Menjalankan code aplikasi versi terbaru sambil mempertahankan data/operasional dKriuk yang sudah ada.
- Menjalankan `pos-platform` DapurHosting untuk pendaftar yang Bos Robert arahkan secara manual ke URL DapurHosting.
- Menyimpan database platform DapurHosting dan database tenant DapurHosting masing-masing secara lokal pada hosting tersebut.

### Kebijakan

1. dKriuk tetap memakai aplikasi POSLive, domain, dan database yang sekarang di DapurHosting.
2. Update dKriuk tetap menggunakan prosedur `poslive/docs/updatev1.md` dan browser trigger `public/update-v1.php`; jangan mengandalkan terminal hosting.
3. Tidak ada migrasi/pemindahan data dKriuk ke IDwebhost dalam rilis onboarding SaaS ini.
4. Setiap pendaftar yang Bos Robert arahkan ke URL DapurHosting harus memakai:
   - `pos-platform` DapurHosting;
   - aplikasi `poslive` DapurHosting;
   - MySQL/database tenant baru DapurHosting;
   - Cron DapurHosting dan directory input bootstrap DapurHosting.
5. Jangan memasukkan credential/database tenant DapurHosting ke `.env` atau konfigurasi IDwebhost, dan sebaliknya.

## 2. IDwebhost — New SaaS Environment

### Fungsi

- Menjalankan `pos-platform` fresh dari nol.
- Menjalankan aplikasi `poslive` fresh dari nol.
- Menyimpan database platform IDwebhost yang saat ini belum memiliki tenant terdaftar.
- Membuat database tenant baru di MySQL IDwebhost, satu database per tenant.

### Kebijakan

1. Pendaftar yang Bos Robert arahkan ke URL IDwebhost menggunakan platform IDwebhost.
2. Tenant baru IDwebhost hanya memakai domain, POSLive, database, Cron, dan bootstrap input directory IDwebhost.
3. Konfigurasi `PLATFORM_TENANT_APP_PATH` IDwebhost hanya boleh mengarah ke folder POSLive IDwebhost.
4. Directory handoff IDwebhost harus lokal pada account IDwebhost dan tidak boleh di-share melalui web atau dengan DapurHosting.
5. DOKU production untuk IDwebhost harus memakai endpoint webhook IDwebhost dan credential environment IDwebhost yang sesuai. Jangan mengarahkan webhook ke dua host untuk transaksi yang sama.

## 3. Matriks penempatan tenant

| Tenant/kelompok | Platform pengelola | POSLive | Database tenant | Hosting |
|---|---|---|---|---|
| dKriuk existing | Platform DapurHosting (bila dikelola platform) | POSLive existing | DB dKriuk existing | DapurHosting |
| Pendaftar yang diberi URL DapurHosting oleh Bos Robert | Platform DapurHosting | POSLive DapurHosting | DB tenant baru DapurHosting | DapurHosting |
| Pendaftar yang diberi URL IDwebhost oleh Bos Robert | Platform IDwebhost | POSLive IDwebhost | DB tenant baru IDwebhost | IDwebhost |

## 4. Larangan lintas host

- Platform IDwebhost tidak boleh mencoba bootstrap POSLive DapurHosting.
- Platform DapurHosting tidak boleh mencoba bootstrap POSLive IDwebhost.
- Tidak boleh memakai satu shared bootstrap input directory lintas hosting.
- Tidak boleh menyalin `tenant_domains`, `tenant_database_configs`, encrypted secret, atau payment event antara database platform tanpa prosedur migrasi yang dirancang khusus.
- Tidak boleh memakai domain tenant yang sama pada kedua platform.
- Tidak boleh menjalankan DOKU webhook URL yang sama untuk dua database platform.

## 5. Konfigurasi minimum per host

Setiap host perlu memakai path dan directory yang lokal terhadap host tersebut:

```env
# pos-platform pada host tersebut
PLATFORM_TENANT_APP_PATH=<POSLIVE_APP_DI_HOST_SAMA>
PLATFORM_TENANT_APP_PHP_BINARY=<PHP_CLI_DI_HOST_SAMA>
PLATFORM_TENANT_BOOTSTRAP_INPUT_DIRECTORY=<HOME_HOST>/.tenant-bootstrap-input

# poslive pada host tersebut
TENANT_BOOTSTRAP_INPUT_DIRECTORY=<HOME_HOST>/.tenant-bootstrap-input
```

Setiap directory input bootstrap harus berada di luar web root, permission directory `0700`, dan file sementara `0600`.

## 6. Keputusan operasional sebelum calon pelanggan mendaftar

Bos Robert memilih URL pendaftaran yang diberikan kepada calon pelanggan:

1. **DapurHosting:** `https://rwp.jajajuju.my.id/`
2. **IDwebhost:** gunakan URL platform IDwebhost setelah domainnya dikonfirmasi.

URL yang diberikan menentukan environment pendaftaran. Invoice, DOKU callback, onboarding job, domain, dan database tenant tidak dapat dipindahkan antar-host secara otomatis setelah registration/payment dimulai.
