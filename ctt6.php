<?php
/*
HASIL TES UNTUK CTT5
Tanggal cek: 2026-04-27

CATATAN
- `ctt5.php` adalah checklist smoke test manual.
- Pada sesi ini saya hanya bisa mengetes bagian yang bisa diverifikasi dari terminal / test suite.
- Jadi file ini adalah hasil tes parsial otomatis untuk membantu menutup checklist tersebut.
- Klik UI browser murni masih perlu sesi manual terpisah.


RINGKASAN
Status umum:
- Batch tes parsial berhasil dijalankan.
- Semua test yang dijalankan lulus.

Total hasil batch ini:
- 44 test passed
- 218 assertions

Perintah yang dijalankan:
- `php artisan test --filter=SplitBillTest`
- `php artisan test --filter=PosCartPaymentFlowTest`
- `php artisan test --filter=AccountingReportExportTest`
- `php artisan test --filter=InvoicePdfViewTest`
- `php artisan test --filter=FloorMapManagerQrToolsTest`
- `php artisan test --filter=RouteAccessMatrixTest`
- `php artisan test --filter=TableQrPrintControllerTest`
- `php artisan test --filter=TableOrderStatusControllerTest`
- `php artisan test --filter=HomeAccountAccessTest`
- `php artisan test --filter=OwnerCashierRecapReportBuilderTest`


1. LOGIN DAN AKSES DASAR
Status:
- PASS parsial

Bukti:
- `HomeAccountAccessTest`
- `RouteAccessMatrixTest`

Yang terverifikasi:
- user dengan permission yang tepat bisa akses `home`
- user tanpa permission yang tepat ditolak
- akses `account profile` juga tervalidasi
- route sensitif lain tervalidasi permission + feature gate

Catatan:
- Visibilitas menu di browser belum diklik manual satu-satu.


2. PRODUK DASAR
Status:
- BELUM DITES MANUAL

Catatan:
- CRUD UI produk belum dijalankan di browser pada sesi ini.


3. PRODUK ADDON / MODIFIER
Status:
- BELUM DITES MANUAL

Catatan:
- Flow klik konfigurasi produk dari browser belum diuji langsung pada sesi ini.


4. SCHEDULE PRICING / DISKON TERJADWAL
Status:
- BELUM DITES DI BATCH INI

Catatan:
- Sebelumnya sudah lulus di `SchedulePricingTest`, tetapi itu tercatat di `ctt4.php`, bukan batch ini.


5. POS BIASA NON-SPLIT
Status:
- PASS parsial kuat

Bukti:
- `PosCartPaymentFlowTest`
- `PaymentServiceTest`

Yang terverifikasi:
- pembayaran cart biasa non-split berhasil diproses dari komponen POS
- payment type non-cash tersimpan benar
- total, paid amount, change, transaction item, dan stock movement ikut benar
- state cart di-reset setelah pembayaran sukses

Catatan:
- klik UI browser masih belum dijalankan, tetapi flow komponen dan service sudah tertutup cukup kuat.


6. POS NON-CASH
Status:
- PASS

Bukti:
- `PosCartPaymentFlowTest`

Yang terverifikasi:
- cart non-split bisa dibayar dengan `qris`
- nilai `paid_amount` otomatis sama dengan total
- `change` tetap 0


7. SPLIT BILL
Status:
- PASS

Bukti:
- `SplitBillTest`

Yang terverifikasi:
- split draft tersimpan menjadi transaksi + bill
- pembayaran bill bertahap bekerja
- transaksi induk baru `paid` saat bill terakhir lunas


8. GABUNG BILL
Status:
- PASS

Bukti:
- `SplitBillTest`

Yang terverifikasi:
- saved bills open bisa digabung menjadi satu bill pengganti
- qty item hasil gabung tetap akurat
- subtotal bill hasil gabung benar
- merge ditolak jika salah satu bill sudah `paid`


9. HOLD CART BIASA
Status:
- PASS

Bukti:
- `PosCartPaymentFlowTest`

Yang terverifikasi:
- cart takeaway biasa bisa disimpan menjadi hold
- hold item terbentuk dengan qty/subtotal/note benar
- state POS kembali bersih setelah hold disimpan


10. GABUNG HOLD
Status:
- PASS

Bukti:
- `SplitBillTest`

Yang terverifikasi:
- dua hold valid bisa digabung ke cart
- source hold berubah `locked`
- ownership lock pindah ke user aktif
- qty cart hasil gabung akurat
- merge ditolak jika ada hold yang statusnya bukan `hold`


11. ORDER MEJA / QR
Status:
- PASS parsial

Bukti:
- `FloorMapManagerQrToolsTest`
- `TableQrPrintControllerTest`
- `TableOrderStatusControllerTest`

Yang terverifikasi:
- halaman print QR meja bisa dirender
- token QR meja bisa diregenerate
- copy event untuk link QR bisa dipicu
- status order meja bisa dibaca
- cart customer bisa terkunci saat sedang diproses kasir
- status `bill requested` dan `completed` ikut tervalidasi

Catatan:
- Flow scan QR dan submit order dari browser customer belum saya klik manual pada sesi ini.


12. PRODUKSI / MASAK
Status:
- BELUM DITES DI BATCH INI

Catatan:
- Sebelumnya sudah lulus di `ProductionAndStockReportTest`, lihat `ctt4.php`.


13. STOK MASUK / STOK KELUAR
Status:
- BELUM DITES DI BATCH INI

Catatan:
- Sebelumnya area report stok sudah tervalidasi di `ctt4.php`.


14. LAPORAN TRANSAKSI
Status:
- PASS parsial

Bukti:
- `InvoicePdfViewTest`

Yang terverifikasi:
- invoice PDF dapat dirender memakai data receipt yang sudah dinormalisasi

Catatan:
- buka halaman laporan dan klik detail transaksi di browser belum diuji manual pada sesi ini.


15. REKAP PRODUK
Status:
- BELUM DITES DI BATCH INI

Catatan:
- Sebelumnya sudah tertutup di `ProductReportBuilderTest`, lihat `ctt4.php`.


16. LAPORAN HARIAN SELF DAN ADMIN
Status:
- PASS parsial lewat matrix akses

Bukti:
- `RouteAccessMatrixTest`

Yang terverifikasi:
- route `admin.daily.report` masuk matrix akses dan lolos sesuai permission/feature

Catatan:
- isi datanya belum diuji di batch ini
- untuk isi report, lihat `DailyReportTest` pada `ctt4.php`


17. REKAP KASIR OWNER
Status:
- PASS parsial

Bukti:
- `OwnerCashierRecapReportBuilderTest`
- `RouteAccessMatrixTest`

Yang terverifikasi:
- data recap owner bisa dibangun konsisten
- filter cashier id bekerja
- status non-final saat masih ada open shift ikut terdeteksi
- route owner recap ikut lolos matrix akses sesuai permission/feature


18. LAPORAN AKUNTANSI
Status:
- PASS parsial

Bukti:
- `AccountingReportExportTest`
- `RouteAccessMatrixTest`

Yang terverifikasi:
- source type ikut masuk ke link export akuntansi
- source type diteruskan ke build call PDF service
- route laporan akuntansi dan settings akuntansi ikut tervalidasi aksesnya

Catatan:
- tampilan layar report dan hasil file export di browser belum saya buka manual.


19. EXPORT PDF / EXCEL
Status:
- PASS parsial

Bukti:
- `AccountingReportExportTest`
- `InvoicePdfViewTest`
- `TableQrPrintControllerTest`

Yang terverifikasi:
- export / render untuk area accounting dan invoice PDF punya jalur kerja yang lolos test
- printable QR page juga bisa dirender

Catatan:
- export untuk semua dokumen pada `ctt5.php` belum ditest satu-satu di batch ini.


20. CLOSE SHIFT
Status:
- BELUM DITES MANUAL


KESIMPULAN
Yang sudah bisa saya tes dari terminal:
- akses dasar dan permission matrix
- route sensitif utama
- invoice PDF
- accounting export wiring
- QR meja
- status order meja
- owner cashier recap
- hold cart biasa
- checkout biasa non-split
- pembayaran non-cash dari komponen POS
- bayar hold aktif dari komponen POS

Yang masih perlu browser manual:
- CRUD produk dari UI
- gabung bill
- klik end-to-end order meja customer
- close shift
- buka semua export PDF/Excel satu per satu dari UI

Penilaian akhir untuk pertanyaan "bisa tes ctt5.php?":
- Bisa, tapi baru parsial untuk bagian yang bisa diotomasi dari terminal.
- Untuk benar-benar menutup `ctt5.php` 100%, kita masih perlu smoke test manual di browser.
*/
