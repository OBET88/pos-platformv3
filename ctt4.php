<?php
/*
CATATAN CEK FITUR POS / APK
Tanggal cek: 2026-04-27

PENTING
- Repo yang sedang aktif adalah `pos-platform`.
- Fitur POS operasional yang Anda minta cek ternyata berada di repo tenant app `poslive`.
- Jadi pengecekan ini saya lakukan dengan membaca kode dan menjalankan test di `D:\laragon\www\poslive`.
- File `ctt3.php` saya biarkan apa adanya karena saat ini sudah ada perubahan lokal di file itu.


RINGKASAN HASIL
Status umum:
- Fitur inti POS yang diminta terdeteksi ada di kode.
- Sejumlah fitur utama berhasil diverifikasi lewat test otomatis dan hasilnya lulus.
- Ada beberapa fitur yang jelas ada di kode, tetapi saya belum menemukan test spesifiknya, jadi statusnya saya tandai perlu smoke test manual.

Total verifikasi otomatis yang dijalankan:
- 25 test lulus
- 212 assertions lulus

Perintah yang dijalankan:
- `php artisan test --filter=SplitBillTest`
- `php artisan test --filter=TableOrderServiceTest`
- `php artisan test --filter=ProductReportBuilderTest`
- `php artisan test --filter=ProductionAndStockReportTest`
- `php artisan test --filter=DailyReportTest`
- `php artisan test --filter=AccountingReportServiceTest`
- `php artisan test --filter=ReportReceiptReprintTest`
- `php artisan test --filter=SchedulePricingTest`
- `php artisan test --filter=PromoVoucherServiceTest`


1. PRODUK
Status:
- Ada di kode.
- Sebagian tervalidasi otomatis.

Bukti:
- Route/menu produk ada di `poslive`:
  - `products`
  - `products.bundle`
  - `products.addons-modifiers`
  - `products.schedule-pricing`
- Test lulus:
  - `SchedulePricingTest`
  - `ProductReportBuilderTest`
  - `TableOrderServiceTest`

Yang berhasil tervalidasi:
- produk tampil di menu order meja
- produk bisa punya jadwal jual
- diskon produk terjadwal bisa dihitung
- laporan produk hanya menghitung transaksi paid
- bundle, addon, modifier ikut dihitung dengan aturan report yang benar

Catatan:
- CRUD lengkap produk di halaman admin belum saya klik manual dari UI pada sesi ini.
- Tetapi route, komponen, dan test pendukungnya ada.


2. POS / TRANSAKSI KASIR
Status:
- Ada di kode.
- Tervalidasi sebagian lewat test.

Bukti:
- POS utama ada di komponen `App\Livewire\Pos`.
- Ada state dan flow untuk:
  - cart
  - payment
  - shift
  - bill
  - hold
  - split bill
  - merge hold / gabung bill
  - promo voucher

Yang tervalidasi:
- pembayaran split bill bertahap
- transaksi split baru selesai saat bill terakhir dibayar
- receipt bill dari report bisa dibuka ulang

Catatan:
- Flow POS normal non-split tidak saya jalankan dengan test terpisah pada sesi ini.
- Namun struktur service dan report pendukung menunjukkan flow itu memang menjadi jalur utama aplikasi.


3. SPLIT BILL
Status:
- Sudah dicek.
- Lulus test otomatis.

Test:
- `SplitBillTest`

Yang terbukti bekerja:
- draft split bisa disimpan menjadi `transaction` + `bills`
- kuantitas item per bill tersimpan benar
- pembayaran bill bisa dicicil
- transaksi induk baru berubah `paid` setelah semua bill selesai dibayar

Hasil:
- 2 test passed
- 36 assertions


4. GABUNG BILL / GABUNG HOLD
Status:
- Fitur ada di kode.
- Belum saya temukan test spesifiknya.

Bukti kode:
- Di `App\Livewire\Pos` ada flow:
  - pilih minimal 2 bill untuk digabung
  - blokir gabung jika bill sudah dibayar
  - gabung hold terpisah
  - pesan sukses:
    - `Bill berhasil digabung.`
    - `Hold berhasil digabung. Silakan lanjutkan pembayaran.`

Kesimpulan:
- Secara implementasi fitur gabung bill/gabung hold memang ada.
- Tetapi untuk sesi ini status paling jujur adalah:
  - terdeteksi kuat di kode
  - perlu smoke test manual tambahan agar benar-benar end-to-end


5. ORDER MEJA / QR TABLE
Status:
- Sudah dicek.
- Lulus test otomatis.

Test:
- `TableOrderServiceTest`

Yang terbukti bekerja:
- menu publik meja hanya menampilkan produk yang sellable
- harga diskon aktif pada menu publik bisa ter-resolve
- produk simple bisa masuk cart meja
- produk yang butuh konfigurasi ditolak jika belum dikonfigurasi
- cart meja bisa disubmit jadi hold
- customer bisa request bill
- pengajuan ulang customer pada hold yang sama tetap tercatat dengan benar

Hasil:
- 8 test passed
- 41 assertions


6. PROMO / VOUCHER
Status:
- Sudah dicek.
- Lulus test otomatis.

Test:
- `PromoVoucherServiceTest`

Yang terbukti bekerja:
- voucher dipilih dari campaign prioritas tertinggi yang cocok
- voucher diskon hanya bisa dipakai sekali
- transaksi pemakai voucher tercatat

Hasil:
- 2 test passed
- 14 assertions


7. STOK MASUK / STOK KELUAR / STOK MENTAH
Status:
- Sudah dicek.
- Lulus test otomatis untuk area report dan pemakaian stok.

Test:
- `ProductionAndStockReportTest`

Yang terbukti bekerja:
- stok bahan baku berkurang saat dipakai produksi
- sistem menolak produksi jika stok bahan baku tidak cukup
- laporan stok tetap akurat walau ada nama produk yang sama

Hasil:
- 3 test passed
- 29 assertions


8. PRODUKSI / MASAK / RESEP
Status:
- Sudah dicek.
- Lulus test otomatis.

Test:
- `ProductionAndStockReportTest`

Yang terbukti bekerja:
- bahan baku keluar sebagai `StockMovement OUT`
- hasil produksi masuk sebagai `FinishedMovement IN`
- qty hasil mengikuti resep

Kesimpulan:
- Flow produksi dasar dan hubungan bahan baku ke barang jadi terverifikasi.


9. LAPORAN PENJUALAN
Status:
- Sudah dicek.
- Lulus test otomatis.

Test:
- `ProductReportBuilderTest`
- `ReportReceiptReprintTest`

Yang terbukti bekerja:
- report produk hanya menghitung transaksi paid
- transaksi di luar range tanggal tidak ikut
- item bundle/addon/modifier masuk ke report sesuai aturan yang diharapkan
- preview/reprint receipt dari report tetap bisa dibuka

Hasil:
- `ProductReportBuilderTest`: 3 test passed, 21 assertions
- `ReportReceiptReprintTest`: 1 test passed, 7 assertions


10. LAPORAN HARIAN
Status:
- Sudah dicek.
- Lulus test otomatis.

Test:
- `DailyReportTest`

Yang terbukti bekerja:
- laporan harian kasir hanya memakai transaksi paid milik kasir aktif
- laporan harian admin bisa difilter per kasir
- transaksi unpaid tidak ikut

Hasil:
- 2 test passed
- 33 assertions


11. LAPORAN AKUNTANSI
Status:
- Sudah dicek.
- Lulus test otomatis dasar.

Test:
- `AccountingReportServiceTest`

Yang terbukti bekerja:
- filter sumber jurnal konsisten terhadap trial balance dan ledger balance

Catatan:
- Area akuntansi di route juga cukup lengkap:
  - report
  - pdf
  - excel
  - setting accounting
  - base mapping
  - cash source
  - custom mapping
  - SOP rules

Hasil:
- 1 test passed
- 12 assertions


12. MENU / CAKUPAN FITUR YANG TERDETEKSI DI APLIKASI TENANT
Status:
- Ada di kode tenant.

Menu utama yang terdeteksi:
- Home
- POS
- Langganan
- Akun

Menu operasional yang terdeteksi:
- Laporan
- Rekap Produk
- Stok Masuk
- Stok Keluar
- Laporan Stok Mentah
- Produksi / Masak
- Shift History Self
- Laporan Harian Self

Menu admin yang terdeteksi:
- Produk
- Kategori
- Floor Map Manager
- User
- Usaha & Cabang
- Produksi / Masak vs Jual
- Setting Resep
- Role Permission
- Rekap Kasir Owner
- Shift History [Admin]
- Laporan Harian [Admin]
- Audit Aktivitas
- Laporan Akuntansi
- Entry Keuangan Admin
- Jurnal Manual
- Setting Akuntansi
- Branding
- Promo Voucher
- Daftar Voucher


KESIMPULAN AKHIR
Yang sudah kuat / tervalidasi:
- produk
- schedule pricing
- promo voucher
- split bill
- order meja QR
- request bill
- stok
- produksi / resep
- laporan produk
- laporan harian
- laporan akuntansi dasar
- receipt reprint

Yang ada di kode tetapi masih saya sarankan dites manual lagi:
- gabung bill
- gabung hold
- CRUD UI produk penuh
- POS normal end-to-end dari klik item sampai checkout non-split

Penilaian umum:
- Secara kode dan test, aplikasi tenant POS ini sudah punya cakupan fitur yang cukup luas dan fondasinya terlihat serius.
- Untuk permintaan Anda "cek semua fitur sampai laporan akhir", status paling jujur saat ini adalah:
  - mayoritas fitur inti terdeteksi dan sebagian besar area penting sudah lulus verifikasi otomatis
  - beberapa flow tertentu masih lebih aman jika ditutup dengan smoke test manual di browser

Saran langkah berikutnya:
- lanjut buat `ctt5.php` khusus hasil smoke test manual UI
- fokus manual test di:
  - checkout biasa non-split
  - gabung bill
  - gabung hold
  - export pdf/excel laporan
*/
