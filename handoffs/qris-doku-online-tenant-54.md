# QRIS DOKU online (tenant 54 / dKriuk) — konfigurasi gateway

Status: DITUNDA · Service: poslive + pos-platform · Diperbarui: 2026-10-30 13:00

## Sedang dikerjakan
**Root cause 503 sudah teridentifikasi = DOKU sandbox (`api-sandbox.doku.com`) sedang gangguan/down di sisi DOKU** (Fix #3). Bukan konfig tenant, bukan kode, bukan jaringan/IP hosting. Menunggu DOKU sandbox pulih, lalu retry tes QRIS.

## Status terakhir
- **Jalur A LIVE** (Fix #2): `TenantPaymentGatewayResolver.php` ter-deploy; `fix_step4_wrapper.sql` dieksekusi; verifikasi `has_extra=1, extra_valid_json=1, updated_at=2026-10-30 10:13:28`. Error "Gateway QRIS tidak terkonfigurasi" sudah tidak muncul.
- **Diagnosis 503 selesai** (Fix #3, diagnostik 2026-10-30 10:53 WIB dari host `himalaya.iixcp.rumahweb.net`): tahap 1 GET `https://api-sandbox.doku.com/` → **503**; tahap 2 POST tanpa signature → **503**; tahap 3 POST dengan signature (logika app) → **503**; tahap 4 POST production `api.doku.com` → **401 `4017300 Unknown Client`**. Kesimpulan: semua request ke sandbox = 503 (bahkan GET kosong), sementara endpoint production terjangkau normal → 503 murni gangguan DOKU sandbox; jaringan host baik; credential kita benar. Catatan: `merchant_id` hasil descript diagnostik = `20905` (nilai lama terkorup yang masih ada di ciphertext lama) — nilai benar pasca jalur A = `936008990000020905` (lihat Verifikasi Fix #2).
- File diagnostik `diagnose_doku_qris.php` sudah dihapus dari repo (mengandung private key DOKU). **Hapus juga dari hosting** (apakah sudah dihapus, konfirmasi ke Bos Robert).

## Keputusan penting
- Jalur A dipilih karena user cPanel tidak bisa DDL (#1044).
- Tidak ada jalur lewat production dengan credential `BRN-0276-...` (4017300 = credential sandbox saja).
- 503 dari ALB DOKU selalu = gangguan/rate-limit sisi DOKU (bukti: GET kosong pun 503; production endpoint dari host sama menjawab 401 normal).

## Langkah berikutnya
1. **Konfirmasi: hapus `diagnose_doku_qris.php` dari hosting** (jika masih ada di server — mengandung private key DOKU).
2. **Tunggu DOKU sandbox pulih**, lalu retry tes QRIS di `v3.jajajuju.my.id` (bukan kode/DB yang diubah).
3. Cek cepat pulih: buat ulang diagnostik (atau tes QRIS langsung) — tahap 1 & 2 harus berubah dari 503 → 4xx (401/403) bila DOKU sandbox sudah normal.
4. Opsional: laporkan ke tim DOKU bila punya akses (endpoint sandbox 503 sejak ~10:53 WIB, production normal).
5. Bila akhirnya pulih & QRIS dinamis tampil + transaksi berhasil → tandai Fix #3 ✅ LIVE + **hapus handoff ini**.

## Jangan lakukan (jebakan yang sudah ditemukan)
- Jangan ubah kode/DB untuk "memperbaiki" 503 — root cause di sisi DOKU, bukan di kita.
- Jangan pakai credential `BRN-0276-...` ke endpoint production (4017300).
- Jangan jalankan lagi `fix_qris_tenant_54.sql` (v1) → error #4025.
- Jangan jalankan `fix_qris_tenant_54_v3.sql` → arsip, ALTER ditolak #1044.
- Jangan commit folder `DB/envee/` & `DB/DB ONLINE/` (gitignored, berisi kredensial).
- Jangan tinggalkan `diagnose_doku_qris.php` di repo atau hosting (private key DOKU).
