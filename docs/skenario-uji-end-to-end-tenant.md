# Skenario Uji End-to-End RWP POS

## Tujuan Dokumen

Dokumen ini disusun sebagai panduan uji coba UI dari awal pendaftaran tenant sampai operasional harian dan tutup buku.

Fokusnya adalah memastikan konsep aplikasi yang sedang kita bangun benar-benar terasa utuh:

- multi tenant atau banyak usaha
- multi cabang
- banyak user dengan role berbeda
- master produk per usaha
- operasional kasir
- meja dan floor map
- QR table order
- split bill
- stok, resep, dan produksi
- promo voucher
- laporan, shift, akuntansi, dan tutup buku

## Dasar Penyusunan

Dokumen ini mengikuti menu dan konsep yang memang terlihat di repo platform saat ini, terutama:

- alur pendaftaran, verifikasi, pembayaran, onboarding, renewal, dan upgrade
- menu tenant: `Dashboard / POS`, `Produk`, `Kategori`, `Floor Map & Meja`, `User`, `Usaha & Cabang`, `Resep`, `Stok`, `Shift`, `Promo`, `Akuntansi`, `Langganan`
- konsep produk global usaha dengan pengaturan per cabang

Catatan penting:

- detail teknis transaksi meja, QR order, dan split bill saya tulis sebagai skenario operasional yang harus divalidasi lewat UI tenant
- istilah tombol atau label kecil bisa sedikit berbeda di tenant app Anda, tetapi alur bisnisnya disusun agar cocok dengan modul yang ada

---

## Konsep Aplikasi Yang Diuji

RWP POS diperlakukan sebagai platform SaaS POS untuk banyak tenant.

Setiap tenant mewakili 1 grup usaha. Di dalam tenant:

- bisa ada lebih dari 1 usaha atau brand operasional bila menu organisasi mendukung
- minimal ada banyak cabang
- setiap cabang punya tim user sendiri
- produk tetap dikelola sebagai master level usaha
- perilaku jual produk bisa berbeda per cabang
- transaksi, stok, dan laporan tetap punya konteks cabang

Skenario utama di dokumen ini memakai contoh tenant F&B dine-in karena paling lengkap untuk menguji:

- meja
- QR order
- split bill
- stok bahan
- resep
- produksi
- kasir
- laporan
- akuntansi

Sebagai pembanding, kita juga siapkan tenant retail sederhana untuk memastikan isolasi antar usaha.

---

## Ringkasan Aktor Uji

### Tenant 1

- Nama tenant: `kopi-nusantara`
- Nama usaha: `Kopi Nusantara Group`
- Cabang awal: `Cabang Sudirman`
- Cabang tambahan: `Cabang Bintaro`
- Tipe: F&B dine-in multi cabang

### Tenant 2

- Nama tenant: `mart-serbaada`
- Nama usaha: `Mart Serba Ada`
- Cabang awal: `Cabang Depok`
- Tipe: retail pembanding untuk isolasi data

### User Tenant 1

- `owner@kopinusantara.test` sebagai `owner`
- `admin@kopinusantara.test` sebagai `admin`
- `kasir1.sudirman@kopinusantara.test` sebagai `kasir`
- `kasir2.bintaro@kopinusantara.test` sebagai `kasir`

### User Tenant 2

- `owner@martserbaada.test` sebagai `owner`
- `kasir@martserbaada.test` sebagai `kasir`

---

## Data Uji Master

### Kategori Produk Tenant 1

- Kopi Panas
- Kopi Dingin
- Non Kopi
- Makanan
- Add On

### Produk Tenant 1

- Espresso
- Americano
- Cappuccino
- Es Kopi Susu
- Matcha Latte
- Croissant
- French Fries
- Extra Shot
- Oat Milk

### Contoh Harga Master

- Espresso: 18000
- Americano: 22000
- Cappuccino: 28000
- Es Kopi Susu: 24000
- Matcha Latte: 30000
- Croissant: 20000
- French Fries: 24000
- Extra Shot: 5000
- Oat Milk: 7000

### Override Harga Per Cabang

- `Cabang Sudirman`
  - Cappuccino: tetap 28000
- `Cabang Bintaro`
  - Cappuccino: 30000
  - Croissant: nonaktif

### Bahan Baku

- Biji Kopi Arabica
- Susu Fresh
- Oat Milk
- Matcha Powder
- Kentang Beku
- Minyak Goreng
- Cup
- Sedotan

### Meja Tenant 1

- Area Indoor: A1, A2, A3, A4
- Area Outdoor: B1, B2, B3
- Area Meeting: VIP1

### Voucher Promo

- `NGOPI10`
  Diskon 10 persen minimal belanja 50000
- `LUNCH25`
  Potongan 25000 minimal belanja 150000

---

## Skenario Besar Yang Harus Lolos

- tenant bisa daftar dari halaman publik
- verifikasi email owner berjalan
- tenant aktif dan siap login
- owner bisa melengkapi setup awal
- tenant bisa tambah cabang dan user
- master produk bisa dipakai lintas cabang
- cabang bisa punya perilaku jual berbeda
- meja dan QR order bisa dipakai untuk dine-in
- order dari QR masuk ke alur kasir
- split bill bisa dilakukan pada meja yang sama
- shift kasir bisa dibuka dan ditutup
- laporan harian dan rekap kasir muncul
- akuntansi dan entry admin bisa dipakai owner
- tutup buku harian bisa diverifikasi
- data tenant 1 tidak bocor ke tenant 2

---

## Skenario 1: Pendaftaran Tenant Baru

### Tujuan

Menguji flow dari halaman publik sampai tenant siap diproses.

### Langkah

1. Buka halaman `Daftar Tenant`.
2. Isi data tenant `kopi-nusantara`.
3. Isi owner utama.
4. Isi nama usaha `Kopi Nusantara Group`.
5. Isi cabang awal `Cabang Sudirman`.
6. Pilih paket yang mendukung POS, stok, floor map, promo, dan akuntansi.
7. Simpan form.

### Hasil yang diharapkan

- request pendaftaran tersimpan
- sistem mengarahkan ke status menunggu verifikasi
- email verifikasi owner dibuat
- tenant belum aktif penuh sebelum verifikasi selesai

### Checklist

- [ ] form validasi berjalan
- [ ] kode tenant unik
- [ ] cabang awal tersimpan
- [ ] paket awal tercatat
- [ ] status pendaftaran terbaca jelas di UI

---

## Skenario 2: Verifikasi, Aktivasi, dan Provisioning

### Tujuan

Menguji tenant benar-benar menjadi workspace yang siap dipakai.

### Langkah

1. Klik link verifikasi email owner.
2. Cek status pendaftaran tenant.
3. Jalankan flow aktivasi sampai tenant siap login.
4. Bila ada halaman sukses onboarding, pastikan owner menerima informasi tenant, usaha, dan cabang awal.

### Hasil yang diharapkan

- tenant berpindah dari draft atau pending menjadi aktif
- owner pertama bisa login
- database atau ruang kerja tenant siap
- tabel dasar operasional tersedia, termasuk cabang dan shift

### Checklist

- [ ] owner bisa login pertama kali
- [ ] tenant memiliki cabang awal
- [ ] tenant memiliki usaha awal
- [ ] menu tenant tampil sesuai paket

---

## Skenario 3: Login Owner dan Audit Menu

### Tujuan

Memastikan owner melihat menu lengkap sesuai paket.

### Langkah

1. Login sebagai `owner`.
2. Catat semua menu di sidebar tenant.
3. Buka satu per satu halaman inti.

### Menu yang minimal harus diuji

- Home
- POS
- Langganan
- Akun
- Laporan
- Rekap Produk
- Stok Masuk
- Stok Keluar
- Laporan Stok Bahan
- Produksi atau Masak
- Produk
- Kategori
- Floor Map dan Meja
- User
- Usaha dan Cabang
- Resep
- Role Permission
- Rekap Kasir Owner
- Shift History Admin
- Laporan Harian Admin
- Audit Aktivitas
- Laporan Akuntansi
- Entry Keuangan Admin
- Jurnal Manual
- Setting Akuntansi
- Branding
- Promo Voucher

### Hasil yang diharapkan

- owner melihat menu premium yang relevan
- tidak ada broken route
- setiap menu membuka halaman yang benar

---

## Skenario 4: Setup Organisasi, Banyak Cabang, dan Banyak User

### Tujuan

Menguji struktur tenant dengan banyak user dan banyak cabang.

### Langkah

1. Buka menu `Usaha & Cabang`.
2. Pastikan `Cabang Sudirman` sudah ada.
3. Tambahkan `Cabang Bintaro`.
4. Buka menu `User`.
5. Tambahkan user `admin`, `kasir` Sudirman, dan `kasir` Bintaro.
6. Buka menu `Role Permission` bila tersedia.
7. Pastikan role owner, admin, dan kasir sesuai pembagian akses.

### Hasil yang diharapkan

- owner dapat menambah cabang baru
- user terhubung ke tenant yang benar
- user cabang Bintaro tidak masuk tenant lain
- role kasir tidak melihat menu owner sensitif

### Checklist

- [ ] cabang baru tersimpan
- [ ] user bisa login
- [ ] role membatasi menu
- [ ] tenant 2 tidak melihat user tenant 1

---

## Skenario 5: Master Produk, Kategori, dan Pengaturan Cabang

### Tujuan

Menguji konsep produk global usaha, tanpa duplikasi per cabang.

### Langkah

1. Login sebagai owner atau admin tenant 1.
2. Tambahkan kategori produk.
3. Tambahkan semua produk master.
4. Tambahkan addon seperti `Extra Shot` dan `Oat Milk` bila UI mendukung.
5. Buka pengaturan produk per cabang.
6. Untuk `Cabang Bintaro`, ubah harga `Cappuccino` menjadi 30000.
7. Untuk `Cabang Bintaro`, nonaktifkan `Croissant`.
8. Simpan perubahan.

### Hasil yang diharapkan

- produk tetap satu master data
- cabang bisa punya harga berbeda
- cabang bisa menyembunyikan produk tertentu
- POS Sudirman dan Bintaro menampilkan hasil berbeda sesuai branch aktif

### Checklist

- [ ] produk tidak perlu dibuat ulang per cabang
- [ ] override harga terbaca di POS cabang terkait
- [ ] produk nonaktif di cabang tidak muncul di POS cabang itu
- [ ] laporan tetap memakai nama produk yang sama

---

## Skenario 6: Stok Awal, Resep, dan Produksi

### Tujuan

Menguji hubungan stok bahan, resep, dan hasil jual.

### Langkah

1. Buka `Stok Masuk`, input bahan baku awal untuk tiap cabang.
2. Buka `Resep Produk`.
3. Buat resep untuk:
   - Espresso
   - Cappuccino
   - Es Kopi Susu
   - Matcha Latte
   - French Fries
4. Buka `Produksi / Masak` bila ada item yang butuh produksi.
5. Jalankan 1 transaksi produksi contoh.
6. Cek `Laporan Stok Bahan`.

### Hasil yang diharapkan

- stok awal masuk per cabang
- resep tersimpan
- produksi mengurangi bahan atau menambah barang jadi sesuai konsep UI
- laporan stok berubah setelah produksi

### Checklist

- [ ] stok Sudirman dan Bintaro bisa dibedakan
- [ ] resep bisa dikaitkan ke produk
- [ ] produksi tercatat
- [ ] laporan stok mengikuti transaksi

---

## Skenario 7: Setup Floor Map, Meja, dan QR

### Tujuan

Menguji operasional dine-in.

### Langkah

1. Buka `Floor Map & Meja`.
2. Buat area `Indoor`, `Outdoor`, dan `Meeting`.
3. Tambahkan meja A1 sampai VIP1.
4. Buat atau tampilkan QR untuk masing-masing meja.
5. Cetak atau screenshot QR meja A1 dan B2 untuk uji.

### Hasil yang diharapkan

- meja bisa dibagi per area
- status meja terlihat di UI
- QR tiap meja unik
- QR membawa pelanggan ke order meja yang benar

### Checklist

- [ ] meja kosong tampil sebagai available
- [ ] QR meja tidak tertukar
- [ ] order dari QR tetap masuk ke cabang aktif yang benar

---

## Skenario 8: Promo Voucher

### Tujuan

Menguji promo sebelum transaksi nyata dilakukan.

### Langkah

1. Buka menu `Promo Voucher`.
2. Buat voucher `NGOPI10`.
3. Buat voucher `LUNCH25`.
4. Cek apakah voucher punya batas:
   - minimal belanja
   - masa aktif
   - cabang tertentu bila fitur tersedia

### Hasil yang diharapkan

- voucher tersimpan
- voucher tampil di daftar
- voucher hanya berlaku saat syarat terpenuhi

---

## Skenario 9: Buka Shift Kasir

### Tujuan

Menguji operasional awal kasir.

### Langkah

1. Login sebagai `kasir1.sudirman`.
2. Buka `POS`.
3. Jalankan `Buka Shift`.
4. Isi kas awal contoh 300000.
5. Ulangi di akun `kasir2.bintaro` dengan kas awal 250000.

### Hasil yang diharapkan

- shift terbuka per user atau per cabang sesuai konsep tenant app
- saldo awal kas tercatat
- kasir tidak bisa menjalankan transaksi jika sistem mensyaratkan shift aktif

### Checklist

- [ ] status shift terlihat
- [ ] riwayat shift self bertambah
- [ ] admin atau owner bisa memonitor shift terbuka

---

## Skenario 10: Order Dine-In Dari QR Table Order

### Tujuan

Menguji order pelanggan dari meja tanpa kasir input manual awal.

### Langkah

1. Scan QR meja A1 dari perangkat pelanggan.
2. Pesan:
   - 2 Cappuccino
   - 1 Croissant
   - 1 Extra Shot
3. Kirim order.
4. Dari kasir Sudirman, cek order masuk.

### Hasil yang diharapkan

- order terhubung ke meja A1
- item masuk ke antrian atau daftar order aktif
- harga mengikuti aturan cabang Sudirman
- kasir bisa mengambil alih order dari QR ke alur pembayaran

### Checklist

- [ ] nama meja tampil
- [ ] cabang order benar
- [ ] total item benar
- [ ] addon atau modifier tersimpan bila didukung

---

## Skenario 11: Order Manual POS dan Penggabungan Di Meja

### Tujuan

Menguji meja yang menerima tambahan pesanan dari kasir.

### Langkah

1. Pada meja A1 yang sudah punya order QR, tambahkan dari POS:
   - 1 Matcha Latte
   - 1 French Fries
2. Pastikan order tetap satu meja.
3. Simpan perubahan.

### Hasil yang diharapkan

- order QR dan input POS bisa hidup dalam konteks meja yang sama
- total tagihan meja bertambah
- kitchen atau daftar item aktif ikut terbarui bila ada

---

## Skenario 12: Split Bill Pada Satu Meja

### Tujuan

Menguji skenario paling penting untuk dine-in ramai.

### Skenario pelanggan

- Pelanggan 1 membayar:
  - 1 Cappuccino
  - 1 Croissant
- Pelanggan 2 membayar:
  - 1 Cappuccino
  - 1 Matcha Latte
  - 1 French Fries
  - Extra Shot

### Langkah

1. Buka transaksi meja A1.
2. Jalankan aksi `Split Bill` atau `Pisah Bill`.
3. Pindahkan item sesuai pembagian pelanggan.
4. Pastikan subtotal per bill benar.
5. Terapkan voucher `NGOPI10` hanya pada bill yang memenuhi syarat.

### Hasil yang diharapkan

- item bisa dipindah antar bill
- jumlah item asli tidak berubah
- bill 1 dan bill 2 punya total yang benar
- voucher hanya mempengaruhi bill yang sah

### Checklist

- [ ] tidak ada item hilang
- [ ] pajak dan service tetap konsisten
- [ ] bill yang sudah dibayar tidak ikut berubah saat bill lain diedit

---

## Skenario 13: Pembayaran Split Bill

### Tujuan

Menguji penyelesaian meja secara bertahap.

### Langkah

1. Bayar bill 1 dengan tunai.
2. Bayar bill 2 dengan metode non tunai atau QRIS bila tersedia.
3. Cetak struk masing-masing.
4. Pastikan status meja hanya menjadi selesai bila seluruh bill lunas.

### Hasil yang diharapkan

- masing-masing bill punya metode pembayaran sendiri
- kas masuk tercatat sesuai metode
- meja belum benar-benar kosong sebelum semua bill selesai
- setelah bill terakhir lunas, meja kembali available

---

## Skenario 14: Uji Cabang Kedua Dengan Harga Berbeda

### Tujuan

Menguji override produk per cabang.

### Langkah

1. Login sebagai `kasir2.bintaro`.
2. Buka POS cabang Bintaro.
3. Cari `Cappuccino`.
4. Pastikan harga 30000.
5. Cari `Croissant`.
6. Pastikan produk tidak muncul atau tidak bisa dijual.

### Hasil yang diharapkan

- POS Bintaro membaca setting cabang
- produk nonaktif tidak ikut tampil
- data Sudirman tidak terpengaruh

---

## Skenario 15: Stok Bergerak Setelah Penjualan

### Tujuan

Menguji apakah transaksi memengaruhi stok dan laporan bahan.

### Langkah

1. Jalankan beberapa transaksi tambahan di Sudirman dan Bintaro.
2. Buka `Laporan Stok Bahan`.
3. Buka `Produksi vs Jual` bila tersedia.
4. Bandingkan bahan yang terpakai terhadap jumlah item terjual.

### Hasil yang diharapkan

- stok bahan menurun mengikuti transaksi
- cabang Sudirman dan Bintaro punya angka berbeda
- laporan produksi vs jual membantu mendeteksi selisih

---

## Skenario 16: Laporan Operasional Harian

### Tujuan

Menguji owner dan admin melihat hasil penjualan.

### Langkah

1. Login sebagai `admin`.
2. Buka `Laporan`.
3. Buka `Rekap Produk`.
4. Buka `Laporan Harian Admin`.
5. Login sebagai kasir dan cek `Laporan Harian Self`.
6. Login sebagai owner dan cek `Rekap Kasir Owner`.

### Hasil yang diharapkan

- laporan self hanya menampilkan transaksi user terkait sesuai rule
- admin bisa lihat operasional lebih luas
- owner bisa lihat rekap lintas kasir dan lintas cabang

### Checklist

- [ ] total transaksi sesuai penjualan uji
- [ ] produk terlaris terbaca
- [ ] shift dan kasir penyumbang penjualan terlihat

---

## Skenario 17: Entry Keuangan, Jurnal Manual, dan Tutup Buku

### Tujuan

Menguji bahwa tenant bukan hanya kasir, tetapi juga pembukuan.

### Langkah

1. Login sebagai `owner`.
2. Buka `Setting Akuntansi / COA`.
3. Pastikan akun dasar tersedia atau buat akun uji bila dibutuhkan.
4. Buka `Entry Keuangan Admin`.
5. Input contoh biaya operasional:
   - pembelian gas: 150000
   - transport kurir internal: 50000
6. Buka `Jurnal Manual`.
7. Input jurnal koreksi kecil bila fitur mendukung.
8. Buka `Laporan Akuntansi`.
9. Verifikasi transaksi penjualan dan biaya sudah terbaca.
10. Jalankan proses tutup buku harian sesuai UI tenant:
    - cek saldo kas
    - cek selisih kas
    - tutup shift
    - tutup rekap hari itu

### Hasil yang diharapkan

- biaya operasional masuk ke laporan akuntansi
- penjualan dan biaya bisa dibaca dalam satu hari buku
- owner bisa menutup hari operasional dengan dasar yang jelas

### Checklist

- [ ] COA bisa diakses
- [ ] entry admin tersimpan
- [ ] jurnal manual tercatat
- [ ] laporan akuntansi muncul
- [ ] tutup buku tidak meninggalkan shift terbuka

---

## Skenario 18: Penutupan Shift

### Tujuan

Menguji alur akhir kasir.

### Langkah

1. Login sebagai kasir Sudirman.
2. Buka `Riwayat Shift Sendiri` atau POS.
3. Lakukan `Tutup Shift`.
4. Isi kas akhir fisik.
5. Simpan.
6. Ulangi untuk kasir Bintaro.

### Hasil yang diharapkan

- sistem menghitung ringkasan transaksi shift
- ada perbandingan kas awal, penjualan, dan kas akhir
- shift tertutup dan masuk riwayat

---

## Skenario 19: Guard Saat Masih Ada Shift Terbuka

### Tujuan

Menguji proteksi operasional tenant.

### Langkah

1. Sisakan 1 akun kasir dengan shift masih terbuka.
2. Sebagai admin platform atau owner tenant sesuai UI yang tersedia, coba nonaktifkan tenant atau lakukan aksi sensitif yang mensyaratkan tidak ada shift terbuka.

### Hasil yang diharapkan

- sistem menolak aksi tersebut
- muncul pesan bahwa shift harus ditutup terlebih dahulu

Ini penting karena di kode platform ada guard untuk tenant yang masih punya shift terbuka.

---

## Skenario 20: Renewal, Upgrade, dan Subscription Center

### Tujuan

Menguji siklus hidup tenant setelah operasional berjalan.

### Langkah

1. Login sebagai owner tenant 1.
2. Buka `Langganan`.
3. Cek status paket, masa aktif, dan opsi upgrade.
4. Ajukan upgrade bila tersedia.
5. Buka flow renewal atau halaman pesanan publik.
6. Cek invoice, pembayaran, dan status validasi.

### Hasil yang diharapkan

- owner bisa memantau status langganan
- invoice renewal atau upgrade terbentuk
- pembayaran bisa ditindaklanjuti

---

## Skenario 21: Isolasi Antar Tenant

### Tujuan

Membuktikan konsep multi usaha benar-benar aman.

### Langkah

1. Login ke tenant 2 `mart-serbaada`.
2. Pastikan tenant 2 tidak melihat:
   - cabang tenant 1
   - produk tenant 1
   - meja tenant 1
   - voucher tenant 1
   - laporan tenant 1
3. Buat 1 produk retail sederhana di tenant 2.
4. Kembali ke tenant 1 dan pastikan produk tenant 2 tidak muncul.

### Hasil yang diharapkan

- isolasi data antar tenant bersih
- user tenant 1 dan tenant 2 tidak bisa saling melihat data

---

## Urutan Uji Yang Disarankan Saat Eksekusi

1. Pendaftaran tenant 1
2. Aktivasi tenant 1
3. Setup cabang dan user tenant 1
4. Setup produk, stok, resep, meja, QR, promo
5. Buka shift
6. Uji order QR
7. Uji order POS
8. Uji split bill
9. Uji pembayaran
10. Uji laporan
11. Uji akuntansi dan tutup buku
12. Uji subscription center
13. Uji tenant 2 untuk isolasi data

---

## Template Catatan Eksekusi

Gunakan format ini saat testing:

| No | Skenario | User | Cabang | Status | Catatan |
| --- | --- | --- | --- | --- | --- |
| 1 | Daftar tenant | owner | - | PASS/FAIL | |
| 2 | Aktivasi tenant | owner | - | PASS/FAIL | |
| 3 | Tambah cabang | owner | pusat | PASS/FAIL | |
| 4 | Tambah user | owner | pusat | PASS/FAIL | |
| 5 | Produk master | admin | semua | PASS/FAIL | |
| 6 | Override cabang | admin | Bintaro | PASS/FAIL | |
| 7 | Floor map | admin | Sudirman | PASS/FAIL | |
| 8 | QR order | pelanggan | Sudirman | PASS/FAIL | |
| 9 | Split bill | kasir | Sudirman | PASS/FAIL | |
| 10 | Tutup shift | kasir | Sudirman | PASS/FAIL | |
| 11 | Laporan harian | owner | semua | PASS/FAIL | |
| 12 | Tutup buku | owner | semua | PASS/FAIL | |

---

## Kriteria Sukses Akhir

Uji dianggap kuat bila hasil akhirnya seperti ini:

- tenant baru bisa lahir dari flow publik tanpa setup manual berantakan
- owner bisa masuk dan langsung paham struktur tenant
- banyak cabang bisa dikelola dalam 1 usaha
- banyak user bisa kerja sesuai role
- produk tetap satu sumber data tetapi fleksibel per cabang
- meja dan QR order benar-benar membantu dine-in
- split bill berjalan tanpa merusak total transaksi
- stok, resep, dan produksi saling terhubung
- laporan operasional dan laporan akuntansi bisa dipakai owner
- tutup shift dan tutup buku memberi kontrol akhir hari
- tenant lain tetap terisolasi

---

## Catatan Penutup

Kalau Anda mau, dokumen ini paling cocok dipakai untuk 3 sesi uji:

1. `Sesi Platform`
   Fokus pendaftaran, aktivasi, invoice, renewal, upgrade.
2. `Sesi Tenant Operasional`
   Fokus produk, cabang, user, meja, QR, split bill, shift, laporan.
3. `Sesi Owner & Finance`
   Fokus rekap kasir, akuntansi, jurnal, dan tutup buku.

Jika nanti Anda ingin, saya bisa lanjutkan dokumen ini menjadi versi yang lebih formal dalam bentuk:

- SOP test script per menu
- checklist UAT bertanda tangan
- versi tabel Excel-friendly
- versi HTML print-friendly
