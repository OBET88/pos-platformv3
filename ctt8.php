<?php
/*
FINAL MANUAL BROWSER QA
Tanggal buat: 2026-04-27

TUJUAN
- Menyisakan hanya test browser manual yang benar-benar belum tertutup oleh test otomatis.
- Dipakai sebagai lembar final QA setelah `ctt7.php`.

POSISI FILE INI
- `ctt7.php` = master hasil test otomatis
- `ctt8.php` = sisa final check browser/UI


RINGKASAN
Status:
- Siap dipakai untuk final pass manual.

Yang SUDAH tertutup otomatis dan tidak perlu diulang penuh dari nol:
- split bill
- merge bill
- hold cart
- merge hold
- pembayaran non-split
- pembayaran non-cash
- QR table service flow
- laporan utama
- close shift
- route access

Yang masih penting dicek langsung di browser:
- tampilan UI
- alur klik nyata user
- file export benar-benar terbuka
- print layout / receipt layout
- notifikasi dan modal terasa benar


FORMAT HASIL
- Status: PASS / FAIL / BLOCKED
- Catatan:
- Bukti:


LEMBAR CEPAT
- [ ] Login role kasir/admin/owner
- [ ] CRUD produk dari UI
- [ ] Addon / modifier dari UI
- [ ] Schedule pricing tampil benar di UI
- [ ] QR table end-to-end dari sisi customer
- [ ] Semua export PDF / Excel dibuka dari UI
- [ ] Receipt / prebill / bill print layout
- [ ] UX modal / alert / notifikasi

Ringkasan:
- PASS:
- FAIL:
- BLOCKED:


1. ROLE DAN SIDEBAR VISUAL
Status:
- Belum diuji.

Langkah:
- Login sebagai `kasir`
- Cek sidebar dan pastikan menu admin sensitif tidak tampil
- Login sebagai `admin`
- Cek menu admin operasional tampil
- Login sebagai `owner`
- Cek menu owner finansial tampil

Ekspektasi:
- Sidebar konsisten dengan role
- Tidak ada menu aneh yang tampil / hilang


2. CRUD PRODUK DARI UI
Status:
- Belum diuji.

Langkah:
- Login `admin`
- Buka `Produk`
- Tambah produk baru
- Edit nama, harga, kategori
- Simpan
- Cari produk itu di `POS`

Ekspektasi:
- Form create/edit bekerja
- Data yang disimpan tampil konsisten di daftar dan POS


3. ADDON / MODIFIER DARI UI
Status:
- Belum diuji.

Langkah:
- Buka produk yang mendukung addon / modifier
- Masuk ke halaman / modal konfigurasi
- Tambah / ubah konfigurasi
- Simpan
- Buka POS
- Tambahkan produk itu

Ekspektasi:
- Konfigurasi tampil benar
- Pilihan addon / modifier muncul saat transaksi
- Harga dan labelnya jelas


4. SCHEDULE PRICING DI UI
Status:
- Belum diuji.

Langkah:
- Buka produk yang punya jadwal harga / diskon
- Pastikan jadwal aktif sesuai waktu tes
- Cek harga di POS
- Cek harga di menu meja jika relevan

Ekspektasi:
- Harga aktif sesuai jadwal
- Label diskon / harga promo tampil masuk akal


5. ORDER MEJA END-TO-END DARI BROWSER CUSTOMER
Status:
- Belum diuji.

Langkah:
- Dari halaman meja / QR print, buka link menu customer
- Tambahkan produk simple
- Tambahkan produk configurable
- Submit order dengan nama customer
- Dari dashboard kasir, cek hold / indikator QR
- Dari browser customer, klik `request bill`
- Dari kasir, buka pre-bill / lanjutkan proses

Ekspektasi:
- Alur customer terasa mulus
- Hold masuk ke kasir tanpa error
- Request bill terlihat jelas
- Tidak ada loop status yang membingungkan


6. EXPORT DARI UI
Status:
- Belum diuji.

Route export/print yang perlu benar-benar dibuka:
- `laporan.produk.pdf`
- `laporan.stok.pdf`
- `laporan.finished-vs-sold.pdf`
- `owner.cashier-recap.pdf`
- `daily.report.print`
- `accounting.report.pdf`
- `accounting.report.excel`
- `receipt.print`
- `bill/{billNumber}/print`
- `prebill.print`

Langkah:
- Buka masing-masing halaman sumber dari UI
- Klik tombol export / print yang tersedia

Ekspektasi:
- File / halaman print benar-benar terbuka
- Tidak ada error 500 / blank page
- Isi dokumen sesuai data layar


7. RECEIPT DAN PRINT LAYOUT
Status:
- Belum diuji.

Langkah:
- Selesaikan satu transaksi biasa
- Buka receipt print
- Buka bill print dari split bill
- Buka pre-bill dari hold / QR meja

Ekspektasi:
- Layout rapi
- Nilai total, qty, metode bayar, invoice, dan item mudah dibaca
- Tidak ada elemen yang kepotong / rusak


8. UX MODAL, ALERT, DAN NOTIFIKASI
Status:
- Belum diuji.

Langkah:
- Coba buka modal hold
- Coba buka modal payment
- Coba split / merge / resume hold
- Coba action yang memunculkan warning

Ekspektasi:
- Modal buka/tutup normal
- Lock body / unlock body terasa benar
- Pesan sukses / warning / error mudah dimengerti
- Tidak ada modal nyangkut atau overlay aneh


9. CHECK VISUAL LAPORAN
Status:
- Belum diuji.

Langkah:
- Buka `Laporan`
- Buka `Rekap Produk`
- Buka `Laporan Harian Self`
- Buka `Laporan Harian [Admin]`
- Buka `Rekap Kasir Owner`
- Buka `Laporan Akuntansi`

Ekspektasi:
- Filter mudah dipakai
- Tabel dan angka terbaca jelas
- Tidak ada kolom kosong aneh / format rusak


10. FINAL SIGN-OFF
Status:
- Belum diuji.

Checklist keputusan akhir:
- [ ] logic backend inti sudah tertutup test otomatis
- [ ] export/print terbuka normal dari UI
- [ ] role dan sidebar terasa benar
- [ ] flow customer QR nyaman dipakai
- [ ] modal/alert tidak mengganggu
- [ ] tidak ada bug visual mayor

Keputusan:
- READY / NOT READY

Catatan akhir:
- Jika semua item di file ini PASS, maka sisa risiko utama tinggal regresi minor UI, bukan logika inti transaksi.
*/
