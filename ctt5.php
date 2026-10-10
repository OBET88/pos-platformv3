<?php
/*
SMOKE TEST MANUAL POS
Tanggal buat: 2026-04-27

TUJUAN
- Menutup gap dari `ctt4.php`.
- Fokus ke uji manual browser untuk flow yang paling penting dan paling rawan regressi.
- File ini adalah checklist kerja. Isi hasilnya saat testing manual berlangsung.

STATUS DOKUMEN
- `ctt5.php` = checklist manual lengkap / versi kerja penuh
- `ctt8.php` = checklist final manual browser yang sudah dipangkas berdasarkan coverage test otomatis terbaru
- Untuk final QA pass, pakai `ctt8.php`
- `ctt5.php` tetap disimpan sebagai referensi lengkap

CATATAN PENTING
- POS inti berada di repo/app `poslive`.
- Smoke test ini diasumsikan dijalankan pada tenant app yang aktif.
- Test otomatis sebelumnya sudah lulus untuk banyak area inti.
- Di file ini fokusnya adalah validasi end-to-end dari sisi UI dan perilaku nyata.


STATUS RINGKAS
- Belum dijalankan manual pada sesi ini.
- Checklist siap dipakai.


LEMBAR EKSEKUSI CEPAT
Isi singkat saat test berjalan:
- [ ] Login role `kasir`
- [ ] Login role `admin`
- [ ] Login role `owner`
- [ ] Produk basic create/edit
- [ ] Produk addon / modifier
- [ ] Schedule pricing aktif
- [ ] POS biasa cash
- [ ] POS biasa non-cash
- [ ] Split bill
- [ ] Gabung bill
- [ ] Hold cart
- [ ] Gabung hold
- [ ] QR meja submit order
- [ ] QR meja request bill
- [ ] Produksi / masak
- [ ] Stok masuk / stok keluar
- [ ] Laporan transaksi
- [ ] Rekap produk
- [ ] Laporan harian self
- [ ] Laporan harian admin
- [ ] Rekap kasir owner
- [ ] Laporan akuntansi
- [ ] Export PDF / Excel
- [ ] Close shift

Ringkasan hasil cepat:
- PASS:
- FAIL:
- BLOCKED:
- Catatan blocker utama:


PERSIAPAN DATA
Pastikan sebelum test:
- ada minimal 1 akun `owner`
- ada minimal 1 akun `admin`
- ada minimal 1 akun `kasir`
- ada shift kasir bisa dibuka
- ada minimal 4 produk aktif:
  - 2 produk biasa
  - 1 produk dengan addon / modifier
  - 1 produk dengan jadwal harga / diskon aktif
- ada minimal 2 meja aktif untuk uji QR / hold
- ada stok bahan baku untuk uji produksi
- ada minimal 1 resep aktif
- jika mau uji export, pastikan driver PDF/Excel di local berjalan normal


FORMAT HASIL
Gunakan format ini saat mengisi hasil:
- Status: PASS / FAIL / BLOCKED
- Catatan:
- Bukti:


1. LOGIN DAN AKSES DASAR
Status:
- Belum diuji.

Langkah:
- Login sebagai `kasir`
- Buka `Home`
- Buka `POS`
- Pastikan menu admin sensitif tidak tampil untuk kasir
- Logout
- Login sebagai `admin`
- Pastikan menu produk, kategori, floor map, user, laporan admin tampil
- Logout
- Login sebagai `owner`
- Pastikan menu owner seperti `Langganan`, `Rekap Kasir Owner`, `Laporan Akuntansi` tampil

Ekspektasi:
- Role hanya melihat menu sesuai hak akses
- Tidak ada error 403/500 saat buka halaman yang memang boleh diakses


2. PRODUK DASAR
Status:
- Belum diuji.

Langkah:
- Login sebagai `admin`
- Buka halaman `Produk`
- Tambah 1 produk biasa baru
- Edit nama dan harga produk itu
- Simpan
- Cari produk tersebut di halaman POS

Ekspektasi:
- Produk berhasil dibuat
- Produk berhasil diedit
- Produk muncul di POS sesuai kategori / pencarian


3. PRODUK ADDON / MODIFIER
Status:
- Belum diuji.

Langkah:
- Pilih 1 produk yang punya addon / modifier
- Buka flow konfigurasi addon / modifier
- Simpan konfigurasi
- Buka POS
- Tambahkan produk itu ke cart
- Pilih addon / modifier

Ekspektasi:
- Modal / halaman konfigurasi muncul normal
- Item masuk cart dengan konfigurasi yang dipilih
- Harga akhir sesuai konfigurasi


4. SCHEDULE PRICING / DISKON TERJADWAL
Status:
- Belum diuji.

Langkah:
- Login admin
- Buka produk yang punya schedule pricing
- Pastikan jadwal aktif sesuai waktu test
- Buka POS atau menu order meja
- Lihat harga produk tersebut

Ekspektasi:
- Harga aktif mengikuti schedule
- Jika ada label diskon, nilainya sesuai


5. POS BIASA NON-SPLIT
Status:
- Belum diuji.

Langkah:
- Login kasir
- Buka shift jika belum terbuka
- Tambahkan 2-3 produk biasa ke cart
- Ubah qty salah satu item
- Tambahkan diskon manual bila perlu
- Lanjut ke pembayaran cash
- Simpan transaksi

Ekspektasi:
- Cart menghitung subtotal dan total dengan benar
- Pembayaran sukses
- Transaksi tercatat sebagai `paid`
- Receipt / struk bisa dibuka


6. POS NON-CASH
Status:
- Belum diuji.

Langkah:
- Buat transaksi baru
- Tambahkan item ke cart
- Pilih pembayaran non-cash
- Simpan transaksi

Ekspektasi:
- Total transaksi tersimpan benar
- Payment type tercatat benar
- Report transaksi menampilkan tipe pembayaran yang sesuai


7. SPLIT BILL
Status:
- Belum diuji manual pada sesi ini.

Langkah:
- Buat cart berisi minimal 2 item
- Aktifkan `Split Bill`
- Pisahkan item ke minimal 2 bill
- Simpan split
- Bayar bill pertama
- Cek status transaksi induk
- Bayar bill kedua

Ekspektasi:
- Bill terbuat sesuai pembagian
- Setelah bill pertama dibayar, transaksi induk belum selesai penuh
- Setelah bill terakhir dibayar, transaksi induk berubah `paid`
- Receipt bill bisa dibuka


8. GABUNG BILL
Status:
- Prioritas tinggi untuk diuji.

Langkah:
- Buat transaksi split dengan minimal 2 bill masih open
- Pilih minimal 2 bill
- Jalankan aksi gabung bill

Ekspektasi:
- Sistem mengizinkan gabung untuk bill open
- Total bill hasil gabung benar
- Tidak ada duplikasi qty / subtotal
- Pembayaran setelah gabung tetap bisa lanjut normal

Negatif test:
- Coba gabung hanya 1 bill
- Coba gabung bill yang sudah paid

Ekspektasi negatif:
- Muncul warning jika bill kurang dari 2
- Bill paid tidak boleh digabung


9. HOLD CART BIASA
Status:
- Belum diuji.

Langkah:
- Tambahkan item ke cart
- Simpan sebagai hold
- Kembali ke daftar hold
- Buka lagi hold tersebut
- Lanjutkan ke pembayaran

Ekspektasi:
- Hold tersimpan
- Hold bisa dibuka ulang
- Isi cart tetap konsisten
- Setelah dibayar, status hold / transaksi berubah sesuai flow


10. GABUNG HOLD
Status:
- Prioritas tinggi untuk diuji.

Langkah:
- Siapkan minimal 2 hold dengan status `hold`
- Pilih keduanya
- Jalankan gabung hold
- Cek hasil cart gabungan
- Lanjutkan ke pembayaran

Ekspektasi:
- Hold berhasil digabung
- Semua item dari hold sumber masuk ke cart gabungan
- Nama / meja asal tidak hilang bila memang ditampilkan
- Pembayaran setelah gabung berhasil

Negatif test:
- Coba gabung 1 hold saja
- Coba gabung hold yang bukan status `hold`

Ekspektasi negatif:
- Warning tampil sesuai kondisi
- Sistem menolak hold yang tidak valid


11. ORDER MEJA / QR
Status:
- Belum diuji manual pada sesi ini.

Langkah:
- Buka halaman QR print untuk satu meja
- Scan / buka URL menu meja
- Tambahkan produk simple
- Tambahkan produk yang butuh konfigurasi
- Submit order dengan nama customer
- Dari sisi kasir, cek notifikasi / hold yang masuk
- Dari sisi customer, klik request bill

Ekspektasi:
- Menu meja terbuka
- Produk tampil sesuai yang sellable
- Cart meja bisa submit
- Hold customer masuk ke kasir
- Request bill tercatat


12. PRODUKSI / MASAK
Status:
- Belum diuji manual pada sesi ini.

Langkah:
- Login user yang punya akses produksi
- Buka `Produksi / Masak`
- Pilih bahan baku yang punya resep
- Produksi qty valid
- Simpan

Ekspektasi:
- Bahan baku berkurang
- Barang jadi bertambah
- Tidak ada error validasi untuk qty yang valid

Negatif test:
- Ulangi dengan qty melebihi stok

Ekspektasi negatif:
- Sistem menolak proses
- Tidak boleh ada movement palsu terbentuk


13. STOK MASUK / STOK KELUAR
Status:
- Belum diuji.

Langkah:
- Input stok masuk untuk satu bahan baku
- Input stok keluar untuk bahan baku lain
- Buka `Laporan Stok Mentah`

Ekspektasi:
- Angka stok masuk dan keluar tercermin di laporan
- Saldo stok terhitung benar


14. LAPORAN TRANSAKSI
Status:
- Belum diuji manual pada sesi ini.

Langkah:
- Buka `Laporan`
- Filter tanggal hari ini
- Buka detail salah satu transaksi
- Buka receipt / reprint

Ekspektasi:
- Transaksi paid tampil
- Detail transaksi bisa dibuka
- Receipt bisa dipreview


15. REKAP PRODUK
Status:
- Belum diuji manual pada sesi ini.

Langkah:
- Buka `Rekap Produk`
- Filter tanggal sesuai transaksi yang baru dibuat
- Cocokkan qty dan omzet produk

Ekspektasi:
- Produk paid masuk ke rekap
- Qty dan omzet cocok dengan transaksi nyata


16. LAPORAN HARIAN SELF DAN ADMIN
Status:
- Belum diuji manual pada sesi ini.

Langkah:
- Login kasir
- Buka `Laporan Harian Self`
- Login admin
- Buka `Laporan Harian [Admin]`
- Filter per kasir

Ekspektasi:
- Self report hanya menampilkan data kasir itu sendiri
- Admin report bisa memfilter per kasir
- Transaksi unpaid tidak ikut


17. REKAP KASIR OWNER
Status:
- Belum diuji.

Langkah:
- Login owner
- Buka `Rekap Kasir Owner`
- Cek data transaksi kasir hari ini
- Coba buka versi PDF bila ada

Ekspektasi:
- Data recap tampil
- PDF terbuka tanpa error


18. LAPORAN AKUNTANSI
Status:
- Belum diuji manual pada sesi ini.

Langkah:
- Login owner
- Buka `Laporan Akuntansi`
- Filter tanggal
- Uji export PDF / Excel jika tombol tersedia

Ekspektasi:
- Report tampil
- Filter bekerja
- Export berjalan tanpa error


19. EXPORT PDF / EXCEL
Status:
- Prioritas sedang.

Area yang perlu dicek:
- `laporan.produk.pdf`
- `laporan.stok.pdf`
- `laporan.finished-vs-sold.pdf`
- `owner.cashier-recap.pdf`
- `daily.report.print`
- `accounting.report.pdf`
- `accounting.report.excel`

Ekspektasi:
- File terbuka / terunduh
- Tidak error 500
- Data utama di dokumen sesuai layar


20. CLOSE SHIFT
Status:
- Belum diuji.

Langkah:
- Selesaikan beberapa transaksi
- Buka flow close shift
- Isi kas aktual
- Simpan

Ekspektasi:
- Shift bisa ditutup normal
- Selisih kas dan alasan tercatat bila ada
- Shift history menampilkan hasil close


PRIORITAS UJI MANUAL
Urutan paling penting:
1. POS biasa non-split
2. Split bill
3. Gabung bill
4. Hold cart
5. Gabung hold
6. Order meja / QR
7. Rekap laporan transaksi + receipt
8. Laporan harian
9. Laporan akuntansi
10. Export PDF / Excel


KESIMPULAN SEMENTARA
- `ctt4.php` sudah menutup verifikasi otomatis.
- `ctt5.php` ini disiapkan untuk menutup verifikasi manual end-to-end.
- Setelah smoke test browser dijalankan, update tiap bagian menjadi:
  - PASS
  - FAIL
  - BLOCKED
*/
