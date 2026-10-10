# Blueprint Produk Global Usaha + Setting Per Cabang

## Tujuan

Membuat arsitektur produk multi-cabang yang tetap rapi:

- `products` tetap menjadi master data level `usaha`
- perilaku jual bisa berbeda per `cabang`
- tidak perlu duplikasi produk per cabang
- tetap aman untuk laporan, stok, promo, resep, dan POS

## Prinsip Desain

Model yang dipakai:

- `produk` = identitas utama item jual
- `setting cabang` = override perilaku produk di cabang tertentu

Yang tetap di master produk:

- nama produk
- kategori
- tipe produk
- resep
- addon / modifier
- gambar
- accounting group
- jadwal global bila memang mau tetap global

Yang boleh berbeda per cabang:

- status aktif dijual
- harga override
- urutan tampil
- label internal opsional
- visibilitas di POS

## Struktur Data

Tabel baru yang disarankan:

`product_branch_settings`

Kolom inti:

- `id`
- `product_id`
- `branch_id`
- `is_active`
- `is_available`
- `price_override`
- `sort_order`
- `created_at`
- `updated_at`

Index/constraint:

- unique `product_id + branch_id`
- index `branch_id, is_active`
- foreign key ke `products`
- foreign key ke `branches`

Arti kolom:

- `is_active`: produk boleh muncul di cabang ini atau tidak
- `is_available`: produk aktif tapi sedang disembunyikan sementara dari penjualan
- `price_override`: jika null, pakai harga master produk
- `sort_order`: urutan tampil khusus cabang

## Aturan Bisnis

Fallback rule:

- jika tidak ada row pada `product_branch_settings`, maka produk mengikuti default master
- default aman yang saya sarankan:
  - `is_active = true`
  - `is_available = true`
  - `price_override = null`

Harga final:

- pakai `price_override` bila ada
- jika tidak ada, pakai `products.price`

Status jual final:

- produk harus `products.is_active = true`
- lalu bila ada setting cabang:
  - `product_branch_settings.is_active = true`
  - `product_branch_settings.is_available = true`

## Dampak Ke Modul Existing

### Produk

Tetap dikelola di level usaha.

Menu `Produk` tetap menjadi master data utama.

Tambahan baru:

- panel `Pengaturan Cabang`
- per produk tampil daftar cabang
- admin bisa atur:
  - aktif / tidak
  - tersedia / tidak
  - harga override
  - urutan tampil

### POS

POS harus membaca:

- master produk dari `products`
- context `branch_id` aktif
- override dari `product_branch_settings`

Hasil akhir yang dipakai POS:

- `effective_price`
- `effective_is_active`
- `effective_is_available`
- `effective_sort_order`

### Laporan

Laporan penjualan tetap aman karena transaksi sudah punya `branch_id`.

Yang perlu dipastikan:

- nama produk tetap satu sumber dari master produk
- jika harga override dipakai, snapshot harga transaksi tetap diambil saat transaksi dibuat

### Stok

Stok tetap per cabang seperti sekarang.

Tidak perlu mengubah konsep produk menjadi per cabang.

### Promo

Promo tetap refer ke `product_id` master.

Jika nanti promo perlu dibatasi per cabang:

- cukup tambah scoping promo per branch
- tidak perlu duplikasi produk

## UI Yang Disarankan

### Di Halaman Produk

Tambahkan aksi:

- `Pengaturan Cabang`

Atau tab di modal/edit produk:

- `Info Produk`
- `Jadwal`
- `Addon / Modifier`
- `Pengaturan Cabang`

Isi `Pengaturan Cabang`:

| Cabang | Aktif | Tersedia | Harga Override | Urutan |
| --- | --- | --- | --- | --- |
| Cabang A | Ya | Ya | kosong | 1 |
| Cabang B | Ya | Tidak | 18000 | 2 |

UX yang disarankan:

- cabang yang tidak punya override tetap tampil
- row override dibuat auto-create saat disimpan
- jika semua nilai sama dengan default, row boleh dihapus agar data tetap bersih

### Di POS

Tidak perlu UI baru besar.

Cukup pastikan daftar produk:

- hanya tampil jika valid untuk branch aktif
- harga yang tampil adalah harga hasil override cabang

## Service Layer Yang Disarankan

Supaya tidak tercecer di banyak query, sebaiknya buat helper/service khusus.

Contoh:

- `App\Support\ProductBranchResolver`
- `App\Models\ProductBranchSetting`

Tanggung jawab resolver:

- merge master product + setting branch
- hasilkan nilai efektif untuk POS
- hasilkan query scope reusable

Contoh method:

- `forBranch(int $branchId)`
- `effectivePrice(Product $product, int $branchId)`
- `isSellable(Product $product, int $branchId)`

## Tahap Implementasi Paling Aman

### Tahap 1

Database foundation

- buat migration `product_branch_settings`
- buat model `ProductBranchSetting`
- buat relation pada `Product` dan `Branch`

### Tahap 2

Read path dulu, tanpa ubah flow create/edit produk

- buat resolver/helper untuk merge data master + branch setting
- ubah query POS agar membaca override cabang
- ubah daftar produk di POS untuk hormati `is_active` dan `is_available`

### Tahap 3

UI admin

- tambahkan panel `Pengaturan Cabang` pada produk
- simpan override cabang dari halaman admin produk

### Tahap 4

Laporan dan modul lain

- audit modul yang membaca produk langsung
- pastikan modul yang sensitif harga mengambil harga transaksi/snapshot
- modul promo dan finance cukup dicek kompatibilitasnya

## Query Strategy

Strategi paling aman:

- jangan ubah `products` menjadi punya `branch_id`
- jangan gandakan row produk per cabang
- gunakan `leftJoin` ke `product_branch_settings` berdasarkan `branch_id` aktif

Contoh konsep query:

- ambil semua produk usaha aktif
- join `product_branch_settings` untuk branch aktif
- hitung:
  - `effective_price = coalesce(price_override, products.price)`
  - `effective_is_active`
  - `effective_sort_order`

## Risiko Yang Harus Dijaga

### Risiko 1

Harga laporan berubah saat harga override berubah.

Mitigasi:

- transaksi harus tetap simpan snapshot harga item saat checkout

### Risiko 2

Produk nonaktif di cabang masih muncul di POS.

Mitigasi:

- satu pintu query POS lewat resolver/helper

### Risiko 3

Admin bingung antara `aktif global` dan `aktif per cabang`.

Mitigasi:

- copy UI yang jelas:
  - `Aktif Global`
  - `Aktif di Cabang Ini`

### Risiko 4

Row setting cabang membengkak.

Mitigasi:

- simpan row hanya saat ada override atau perubahan dari default

## Rekomendasi Final

Arsitektur yang saya rekomendasikan:

- `products` tetap level `usaha`
- buat `product_branch_settings`
- POS baca effective setting per branch
- UI admin produk punya panel `Pengaturan Cabang`

Ini adalah model yang lebih profesional karena:

- master data tetap tunggal
- cabang tetap fleksibel
- tidak menimbulkan duplikasi produk
- lebih mudah di-scale untuk multi-cabang

## Next Step Yang Paling Disarankan

Urutan kerja implementasi:

1. tambah tabel `product_branch_settings`
2. tambah model + relation
3. buat resolver effective product per branch
4. ubah query POS
5. tambahkan UI admin untuk setting cabang
6. tambahkan test untuk fallback dan override cabang
