# Flutter Migration Blueprint

Tanggal acuan: `2026-05-25`

Dokumen ini merangkum saran arsitektur dan prioritas jika aplikasi `poslive` ingin dibuatkan versi `Flutter`.

Target dokumen ini bukan full rewrite langsung, tetapi blueprint yang realistis agar migrasi tidak terlalu mahal, tidak terlalu lama, dan tetap sesuai dengan karakter aplikasi saat ini.

## 1. Ringkasan Keputusan

Keputusan awal yang direkomendasikan:

1. `Flutter` sangat cocok untuk `operasional mobile` dan `tablet kasir`.
2. Tidak disarankan memindahkan seluruh menu ke Flutter dalam tahap pertama.
3. Tahap pertama harus fokus ke modul yang paling sering dipakai di lapangan:
   - `POS`
   - `pesanan meja / QR order`
   - `running orders`
   - `shift`
   - `stok ringan`
   - `laporan ringkas owner`
4. Modul admin berat tetap lebih aman dan lebih cepat dikelola lewat web Laravel.
5. Sebelum membuat UI Flutter, backend perlu disiapkan menjadi `API-first`.

## 2. Kondisi Repo Saat Ini

Repo saat ini masih sangat berbasis web:

- route utama masih ada di `routes/web.php`
- banyak fitur memakai `Livewire`
- alur POS sangat besar dan stateful
- route API khusus mobile belum terlihat tersedia

Contoh modul penting yang sekarang masih web-centric:

- `POS` di `app/Livewire/Pos.php`
- `Marketplace` di `app/Livewire/Marketplace/MarketplaceCenter.php`
- `Accounting Report` di `app/Livewire/Accounting/AccountingReport.php`
- navigasi menu utama di `config/sidebar.php`

Konsekuensinya:

- Flutter tidak bisa langsung dipasang tipis di atas kode sekarang
- backend perlu dipisah menjadi service dan API yang lebih stabil
- validasi permission, context tenant, business, dan branch harus tetap konsisten

## 3. Tujuan Flutter Yang Paling Masuk Akal

Tujuan bisnis yang realistis:

- kasir bisa bekerja lebih nyaman di tablet Android
- owner bisa memantau usaha dari HP
- flow operasional inti lebih cepat daripada web
- sebagian fitur bisa disiapkan untuk offline di masa depan

Tujuan yang tidak disarankan untuk tahap pertama:

- semua menu admin ikut pindah ke Flutter
- akuntansi penuh di mobile
- seluruh konfigurasi tenant dan organisasi di mobile
- full parity semua fitur web dalam satu fase

## 4. Prinsip Migrasi

Prinsip yang sebaiknya dipakai:

1. `Web tetap hidup`
   - Laravel web tetap menjadi pusat admin dan fallback operasional.

2. `Flutter fokus ke use case yang mobile-native`
   - layar sentuh
   - kamera
   - printer bluetooth / network
   - notifikasi
   - pemakaian cepat di outlet

3. `Backend dibuat API-first`
   - Flutter tidak boleh bergantung pada pola Livewire.

4. `Permission dan scope tetap sama`
   - tenant
   - usaha
   - cabang
   - role user

5. `Migrasi bertahap`
   - mulai dari modul yang memberi dampak bisnis terbesar.

## 5. Menu Yang Cocok Masuk Flutter

Berikut modul yang paling cocok dipindahkan lebih dulu.

### 5.1. POS / Kasir

Alasan:

- ini modul inti bisnis
- paling sering dipakai
- cocok di tablet
- interaction model-nya natural untuk touch screen

Fitur yang sebaiknya masuk:

- pilih produk
- cari produk
- kategori
- cart
- pembayaran
- split bill
- hold bill
- prebill
- print receipt
- member lookup dasar
- promo dasar

Catatan:

`POS` saat ini sangat besar dan kompleks, jadi perlu dipisah menjadi service API yang lebih kecil dan terfokus.

### 5.2. Pesanan Meja / QR Order

Alasan:

- sudah ada alur publik yang jelas
- sangat cocok untuk mobile dan tablet
- bisa berkembang ke waiter app atau self-order app

Fitur yang cocok:

- lihat menu meja
- tambah ke cart
- submit order
- request bill
- status order

### 5.3. Running Orders dan Shift

Alasan:

- operasional harian butuh kecepatan
- cocok untuk supervisor outlet
- user sering hanya butuh ringkasan dan aksi cepat

Fitur yang cocok:

- running orders
- buka shift
- tutup shift
- cash in / cash out
- riwayat shift singkat

### 5.4. Stok Ringan

Yang cocok untuk Flutter:

- stok masuk sederhana
- stok keluar sederhana
- opname cepat
- lihat stok menipis

Yang sebaiknya belum diprioritaskan:

- flow produksi yang kompleks
- setup resep penuh
- konfigurasi konversi yang rumit

### 5.5. Laporan Ringkas Owner

Yang cocok:

- omzet harian
- transaksi hari ini
- top produk
- ringkasan kas
- ringkasan marketplace
- notifikasi stok kritis

Ini cocok dijadikan `owner mobile dashboard`.

## 6. Menu Yang Sebaiknya Tetap Web Dulu

Berikut modul yang lebih baik tetap di Laravel web pada fase awal.

### 6.1. Akuntansi Penuh

Tetap web karena:

- tabel dan filter kompleks
- lebih nyaman di desktop
- butuh ruang layar besar

Cakupan:

- laporan akuntansi
- jurnal manual
- entry keuangan admin
- setting akuntansi
- close month

### 6.2. Admin Master Data Lengkap

Tetap web karena:

- form banyak
- relasi data kompleks
- lebih aman dikerjakan admin backoffice

Cakupan:

- produk full management
- kategori
- resep
- role permission
- user management
- usaha dan cabang

### 6.3. Marketplace Full Backoffice

Tetap web dulu karena:

- sekarang modulnya campuran setup, mapping, order, dan report
- lebih cocok dipilah dulu sebelum dibuat mobile

### 6.4. Pengaturan Berat

Tetap web:

- branding
- membership settings
- promo settings
- voucher batch management
- subscription center

## 7. Usulan Struktur App Flutter

Struktur terbaik bukan satu app generik yang memaksa semua peran masuk ke satu layar.

Rekomendasi:

### Opsi A. Satu App, Dua Mode

Mode:

- `Kasir Mode`
- `Owner Mode`

Kelebihan:

- satu codebase app
- onboarding lebih sederhana

Kekurangan:

- navigasi dan permission lebih kompleks

### Opsi B. Dua App Terpisah

App:

- `POSLive Kasir`
- `POSLive Owner`

Kelebihan:

- lebih fokus
- UI lebih rapi per role
- distribusi fitur lebih jelas

Kekurangan:

- maintenance app store / release lebih banyak

Keputusan praktis yang direkomendasikan:

- mulai dari `satu app`
- tetapi struktur modul internal dipisah jelas antara `kasir` dan `owner`

## 8. Usulan Navigasi Flutter

### 8.1. Kasir Mode

Bottom navigation yang disarankan:

- `Kasir`
- `Pesanan`
- `Stok`
- `Laporan`
- `Akun`

Isi utama:

- `Kasir`
  - produk
  - cart
  - pembayaran
- `Pesanan`
  - running orders
  - meja
  - QR order monitor
- `Stok`
  - stok masuk
  - stok keluar
  - opname cepat
- `Laporan`
  - transaksi hari ini
  - shift saya
- `Akun`
  - profil
  - printer
  - logout

### 8.2. Owner Mode

Bottom navigation yang disarankan:

- `Dashboard`
- `Cabang`
- `Laporan`
- `Marketplace`
- `Akun`

Isi utama:

- `Dashboard`
  - omzet
  - transaksi
  - alert
- `Cabang`
  - pilih usaha/cabang aktif
  - performa ringkas cabang
- `Laporan`
  - omzet
  - produk
  - kas
- `Marketplace`
  - order summary
  - settlement summary
- `Akun`
  - profil
  - context switch
  - logout

## 9. API Yang Dibutuhkan

Sebelum Flutter dibuat serius, backend perlu menyediakan API untuk domain berikut.

### 9.1. Auth dan Context

- login
- logout
- profile
- daftar tenant context
- pilih usaha
- pilih cabang
- permission aktif user

### 9.2. POS

- daftar produk
- kategori
- detail produk
- addon dan modifier
- cart validation
- submit transaksi
- hold bill
- resume hold
- split bill
- running order
- print payload

### 9.3. Table Order

- detail meja berdasarkan token
- menu publik
- add cart
- update qty
- submit order
- request bill
- status order

### 9.4. Shift

- buka shift
- tutup shift
- cash movement
- ringkasan shift aktif
- history singkat

### 9.5. Laporan Mobile

- summary omzet
- transaksi harian
- top produk
- stok kritis
- ringkasan kas

## 10. Prioritas Fase Implementasi

### Fase 1. Stabilkan Backend

Target:

- rapikan service layer
- pisahkan logic dari Livewire bila masih tercampur
- definisikan contract API
- siapkan auth mobile

Output:

- backend siap dipakai Flutter

### Fase 2. Flutter POS MVP

Target:

- login
- pilih context
- POS dasar
- pembayaran dasar
- running orders
- receipt sederhana

Scope sempit yang disarankan:

- `cash`
- `takeaway`
- `dine in` dasar
- tanpa semua fitur admin

### Fase 3. Table Order dan Shift

Target:

- QR order support
- request bill
- shift opening / closing
- cash in / cash out

### Fase 4. Owner Dashboard

Target:

- omzet
- transaksi
- stok kritis
- marketplace summary

### Fase 5. Stok Mobile

Target:

- stok masuk
- stok keluar
- opname cepat

## 11. Risiko Yang Harus Diantisipasi

### 11.1. Risiko Terbesar

- backend sekarang masih dekat dengan pola Livewire
- POS flow sangat besar dan bercampur
- permission dan context multi-usaha/cabang bisa mudah bocor kalau API tidak disiplin
- printer mobile bisa berbeda perilaku per device

### 11.2. Risiko Scope

Kalau semua menu dipindah sekaligus:

- proyek jadi lama
- biaya naik
- QA makin sulit
- parity bug akan banyak

Karena itu, strategi terbaik adalah:

- `mobile-first untuk operasional`
- `web-first untuk administrasi berat`

## 12. Rekomendasi Final

Rekomendasi paling masuk akal untuk repo ini:

1. Jadikan Flutter sebagai `app operasional`.
2. Fokus tahap awal ke:
   - `POS`
   - `QR / table order`
   - `running orders`
   - `shift`
   - `owner dashboard`
3. Biarkan modul berikut tetap web dulu:
   - `akuntansi`
   - `master data berat`
   - `role permission`
   - `promo setting`
   - `subscription`
4. Kerjakan `API layer` terlebih dahulu sebelum UI Flutter penuh.
5. Hindari full rewrite semua menu dalam satu fase.

## 13. Checklist Keputusan Sebelum Mulai

Sebelum implementasi Flutter dimulai, putuskan dulu:

- apakah target utama `tablet kasir`, `HP owner`, atau keduanya
- apakah mau `satu app dua mode` atau `dua app terpisah`
- apakah POS tahap awal butuh offline atau online-only dulu
- apakah printer target `bluetooth`, `USB`, atau `LAN`
- apakah Flutter hanya untuk tenant app atau nanti juga untuk platform app

## 14. Kesimpulan Singkat

`Flutter` cocok untuk aplikasi ini jika diposisikan sebagai lapisan operasional modern di atas backend Laravel yang tetap kuat.

Yang paling bagus bukan memindahkan semua menu, tetapi memilih modul yang:

- paling sering dipakai
- paling cocok untuk touch device
- paling memberi dampak bisnis

Untuk repo `poslive` saat ini, modul tersebut adalah:

- `POS`
- `table order`
- `running orders`
- `shift`
- `laporan owner ringkas`
