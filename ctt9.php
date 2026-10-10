<?php
/*
SKENARIO UJI UI SEMUA FITUR POS
Tanggal buat: 2026-04-27

TUJUAN
- Menyediakan skenario test UI yang bisa langsung dijalankan oleh tester.
- Fokus ke alur nyata user dari awal sampai akhir.
- Dipakai bersama `ctt8.php` saat final QA browser.


CARA PAKAI
- Jalankan skenario berurutan.
- Catat hasil per skenario:
  - PASS
  - FAIL
  - BLOCKED
- Jika FAIL, tulis:
  - langkah ke berapa
  - hasil aktual
  - screenshot / pesan error


AKUN YANG DISIAPKAN
- `kasir`
- `admin`
- `owner`

DATA YANG DISIAPKAN
- minimal 4 produk aktif:
  - 2 produk biasa
  - 1 produk dengan addon / modifier
  - 1 produk dengan schedule pricing / diskon aktif
- minimal 1 kategori
- minimal 2 meja aktif
- minimal 1 bahan baku
- minimal 1 resep produksi


SKENARIO 1. LOGIN DAN SIDEBAR
Tujuan:
- memastikan role dan menu tampil benar

Langkah:
1. Login sebagai `kasir`
2. Cek menu `Home`, `POS`, `Akun`, `Laporan Harian Self`
3. Pastikan menu seperti `Produk`, `User`, `Laporan Akuntansi` tidak tampil
4. Logout
5. Login sebagai `admin`
6. Cek menu `Produk`, `Kategori`, `Floor Map Manager`, `User`, `Laporan Harian [Admin]`
7. Logout
8. Login sebagai `owner`
9. Cek menu `Rekap Kasir Owner`, `Laporan Akuntansi`, `Langganan`

Hasil yang diharapkan:
- menu sesuai role
- tidak ada error 403/500


SKENARIO 2. CRUD PRODUK DASAR
Tujuan:
- memastikan produk bisa dibuat dan diedit dari UI

Langkah:
1. Login sebagai `admin`
2. Buka menu `Produk`
3. Tambah produk baru: nama, harga, kategori
4. Simpan
5. Edit produk yang baru dibuat
6. Ubah nama dan harga
7. Simpan
8. Cari produk tersebut di daftar produk
9. Buka `POS`
10. Cari produk tersebut di POS

Hasil yang diharapkan:
- produk tersimpan
- perubahan produk tersimpan
- produk muncul di POS


SKENARIO 3. KATEGORI
Tujuan:
- memastikan kategori mendukung produk di UI

Langkah:
1. Buka menu `Kategori`
2. Tambah kategori baru
3. Simpan
4. Edit kategori
5. Kembalikan ke daftar produk
6. Pastikan kategori bisa dipakai pada produk

Hasil yang diharapkan:
- kategori tersimpan
- kategori bisa dipakai di produk


SKENARIO 4. ADDON / MODIFIER
Tujuan:
- memastikan konfigurasi produk kompleks berjalan

Langkah:
1. Buka produk yang mendukung addon / modifier
2. Masuk ke pengaturan addon / modifier
3. Tambah / cek opsi addon
4. Simpan
5. Buka `POS`
6. Tambahkan produk tersebut
7. Pilih addon / modifier
8. Masukkan ke cart

Hasil yang diharapkan:
- opsi addon / modifier tampil
- item masuk ke cart dengan konfigurasi yang dipilih
- harga item sesuai


SKENARIO 5. SCHEDULE PRICING / DISKON
Tujuan:
- memastikan harga aktif mengikuti jadwal

Langkah:
1. Buka produk yang punya jadwal harga
2. Cek jadwal aktif sesuai waktu sekarang
3. Buka `POS`
4. Cari produk itu
5. Catat harga yang tampil
6. Jika ada menu QR meja, buka juga produk itu di menu customer

Hasil yang diharapkan:
- harga di UI mengikuti jadwal
- label diskon / harga promo sesuai


SKENARIO 6. POS BIASA CASH
Tujuan:
- memastikan transaksi normal cash berjalan

Langkah:
1. Login sebagai `kasir`
2. Buka shift jika belum terbuka
3. Tambahkan 2 produk biasa ke cart
4. Ubah qty salah satu produk
5. Jika perlu, tambahkan diskon manual
6. Klik pembayaran
7. Pilih `cash`
8. Masukkan uang bayar lebih besar dari total
9. Simpan transaksi
10. Buka receipt

Hasil yang diharapkan:
- subtotal dan total benar
- transaksi berhasil
- change benar
- receipt tampil normal


SKENARIO 7. POS NON-CASH
Tujuan:
- memastikan pembayaran non-cash dari UI berjalan

Langkah:
1. Buat transaksi baru
2. Tambahkan produk ke cart
3. Klik pembayaran
4. Pilih `qris` atau metode non-cash lain
5. Simpan transaksi
6. Buka laporan transaksi
7. Cek transaksi tersebut

Hasil yang diharapkan:
- transaksi tersimpan
- payment type benar
- laporan menampilkan tipe pembayaran yang benar


SKENARIO 8. HOLD CART BIASA
Tujuan:
- memastikan hold normal dari UI berjalan

Langkah:
1. Tambahkan item ke cart
2. Klik `Hold`
3. Simpan hold
4. Buka `Hold List`
5. Resume hold
6. Cek isi cart
7. Lanjutkan pembayaran

Hasil yang diharapkan:
- hold tersimpan
- hold bisa dibuka ulang
- isi cart tetap sama
- transaksi bisa diselesaikan


SKENARIO 9. SPLIT BILL
Tujuan:
- memastikan split bill berjalan end-to-end

Langkah:
1. Tambahkan minimal 2 item ke cart
2. Klik `Split Bill`
3. Bagi item ke 2 bill
4. Simpan split
5. Bayar bill pertama
6. Cek apakah transaksi induk masih terbuka
7. Bayar bill kedua
8. Cek apakah transaksi induk selesai
9. Buka print bill

Hasil yang diharapkan:
- pembagian bill benar
- bill pertama tidak langsung menutup transaksi induk
- bill terakhir menutup transaksi induk


SKENARIO 10. GABUNG BILL
Tujuan:
- memastikan merge bill bekerja di UI

Langkah:
1. Buat transaksi split dengan minimal 2 bill open
2. Pilih 2 bill tersebut
3. Klik `Gabung Bill`
4. Cek bill hasil gabung
5. Lanjutkan bayar bill gabungan

Negatif test:
1. Coba pilih hanya 1 bill
2. Coba gabung bill yang sudah paid

Hasil yang diharapkan:
- bill open bisa digabung
- total bill gabungan benar
- warning muncul pada kondisi tidak valid


SKENARIO 11. GABUNG HOLD
Tujuan:
- memastikan merge hold dari UI berjalan

Langkah:
1. Siapkan 2 hold aktif
2. Buka `Hold List`
3. Pilih 2 hold
4. Klik `Gabung Hold`
5. Cek isi cart hasil gabung
6. Lanjutkan pembayaran

Negatif test:
1. Coba pilih 1 hold saja
2. Coba hold yang statusnya tidak valid

Hasil yang diharapkan:
- item tergabung dengan benar
- pembayaran sesudah merge tetap normal
- warning muncul saat kondisi invalid


SKENARIO 12. FLOOR MAP DAN MEJA
Tujuan:
- memastikan manajemen meja dan QR meja bekerja

Langkah:
1. Login `admin`
2. Buka `Floor Map Manager`
3. Pastikan meja tampil
4. Buka print QR salah satu meja
5. Pastikan link QR / halaman print muncul

Hasil yang diharapkan:
- daftar meja tampil
- QR print bisa dibuka


SKENARIO 13. ORDER MEJA DARI CUSTOMER
Tujuan:
- memastikan flow customer QR end-to-end

Langkah:
1. Dari halaman QR meja, buka link menu customer
2. Tambahkan produk simple
3. Tambahkan produk configurable
4. Submit order dengan nama customer
5. Di sisi kasir, cek hold / indikator QR
6. Dari sisi customer, klik `request bill`
7. Di sisi kasir, buka pre-bill atau resume hold

Hasil yang diharapkan:
- order customer masuk
- kasir menerima notifikasi / indikator
- request bill tercatat


SKENARIO 14. STOK MASUK DAN STOK KELUAR
Tujuan:
- memastikan stok manual berjalan

Langkah:
1. Buka `Stok Masuk`
2. Tambah stok bahan baku
3. Simpan
4. Buka `Stok Keluar`
5. Kurangi stok bahan baku
6. Simpan
7. Buka `Laporan Stok Mentah`

Hasil yang diharapkan:
- pergerakan stok tercatat
- saldo di laporan sesuai


SKENARIO 15. PRODUKSI / MASAK
Tujuan:
- memastikan flow produksi dari UI berjalan

Langkah:
1. Buka `Produksi / Masak`
2. Pilih bahan baku yang punya resep
3. Input qty valid
4. Simpan produksi
5. Cek hasil produksi
6. Ulangi dengan qty melebihi stok

Hasil yang diharapkan:
- produksi valid berhasil
- stok bahan baku turun
- hasil barang jadi naik
- produksi invalid ditolak


SKENARIO 16. LAPORAN TRANSAKSI
Tujuan:
- memastikan laporan transaksi bisa dipakai dari UI

Langkah:
1. Buka menu `Laporan`
2. Filter tanggal hari ini
3. Buka detail salah satu transaksi
4. Buka receipt / reprint

Hasil yang diharapkan:
- transaksi tampil benar
- detail bisa dibuka
- receipt bisa dipreview


SKENARIO 17. REKAP PRODUK
Tujuan:
- memastikan rekap penjualan produk tampil benar

Langkah:
1. Buka `Rekap Produk`
2. Filter tanggal transaksi uji
3. Cocokkan qty dan omzet dengan transaksi yang tadi dibuat

Hasil yang diharapkan:
- qty dan omzet akurat


SKENARIO 18. LAPORAN HARIAN SELF DAN ADMIN
Tujuan:
- memastikan laporan harian cocok dengan role

Langkah:
1. Login `kasir`
2. Buka `Laporan Harian Self`
3. Catat hasilnya
4. Login `admin`
5. Buka `Laporan Harian [Admin]`
6. Filter berdasarkan kasir yang tadi

Hasil yang diharapkan:
- self report hanya menampilkan data kasir sendiri
- admin report bisa filter kasir


SKENARIO 19. REKAP KASIR OWNER
Tujuan:
- memastikan owner recap tampil dan sesuai

Langkah:
1. Login `owner`
2. Buka `Rekap Kasir Owner`
3. Filter tanggal hari ini
4. Cocokkan total dengan transaksi yang dibuat

Hasil yang diharapkan:
- recap owner tampil
- angka mendekati / sesuai data transaksi


SKENARIO 20. LAPORAN AKUNTANSI
Tujuan:
- memastikan accounting report tampil dari UI

Langkah:
1. Login `owner`
2. Buka `Laporan Akuntansi`
3. Ubah filter tanggal
4. Jika ada filter source type, coba ubah
5. Cek tabel / hasil report

Hasil yang diharapkan:
- report tampil
- filter bekerja


SKENARIO 21. EXPORT DAN PRINT
Tujuan:
- memastikan file print/export benar-benar bisa dibuka

Item yang wajib diklik dari UI:
1. `Laporan Produk PDF`
2. `Laporan Stok PDF`
3. `Finished vs Sold PDF`
4. `Owner Cashier Recap PDF`
5. `Daily Report Print`
6. `Accounting Report PDF`
7. `Accounting Report Excel`
8. `Receipt Print`
9. `Bill Print`
10. `Pre-Bill Print`

Hasil yang diharapkan:
- file terbuka / terdownload
- tidak ada blank page
- isi dokumen sesuai data layar


SKENARIO 22. CLOSE SHIFT
Tujuan:
- memastikan close shift final dari UI berjalan

Langkah:
1. Pastikan ada transaksi cash dan non-cash
2. Buka modal `Close Shift`
3. Cek summary
4. Tutup shift dengan nominal sesuai expected cash
5. Ulangi skenario selisih cash tanpa alasan jika environment memungkinkan

Hasil yang diharapkan:
- summary benar
- close shift sukses saat nominal benar
- close shift ditolak saat ada selisih tanpa alasan


SKENARIO 23. UX MODAL DAN ALERT
Tujuan:
- memastikan pengalaman penggunaan tidak rusak

Langkah:
1. Buka modal hold
2. Tutup modal
3. Buka modal payment
4. Trigger warning / error validasi
5. Ulangi di split / hold / merge

Hasil yang diharapkan:
- modal stabil
- overlay tidak nyangkut
- alert mudah dipahami


FINAL CHECK
Jika semua skenario inti PASS, maka status aplikasi secara UI bisa dianggap:
- SIAP UJI OPERASIONAL

Jika ada FAIL, catat:
- nama skenario
- langkah
- hasil aktual
- severity
*/
