# DOKU Integration Scope Note

Tanggal: 2026-05-07

## Keputusan Scope

- Fokus integrasi DOKU saat ini hanya untuk pembayaran pembelian paket tenant / renewal / upgrade.
- Integrasi DOKU untuk POS kasir ditunda sampai flow platform payment stabil.
- Alasan penundaan POS:
  - flow POS saat ini masih menganggap non-cash langsung paid
  - split bill + gateway asynchronous lebih kompleks
  - risiko stok, receipt, dan status transaksi dobel proses lebih tinggi

## Scope Aktif Sekarang

- Project utama: `pos-platform`
- Entry point payment:
  - `GET /payment/{invoiceNumber}`
  - `POST /payment/{invoiceNumber}/doku`
  - `POST /webhooks/doku/checkout`
- Objek utama:
  - `App\Models\Platform\PlatformPaymentTransaction`
  - `App\Models\Platform\PlatformPaymentEvent`
  - `App\Services\Platform\DokuCheckoutService`
  - `App\Services\Platform\PaymentActivationService`

## Temuan Audit Awal

- Fondasi DOKU untuk platform payment sudah ada.
- `PlatformPaymentController` sudah memiliki:
  - redirect ke DOKU checkout
  - webhook handler DOKU
  - fallback manual proof upload
- `DokuCheckoutService` sudah memiliki:
  - create checkout request
  - signature request generator
  - webhook signature verification
  - DOKU status mapping ke status lokal
- Konfigurasi service DOKU sudah tersedia di `config/services.php`:
  - `DOKU_ENABLED`
  - `DOKU_BASE_URL`
  - `DOKU_CLIENT_ID`
  - `DOKU_SECRET_KEY`
  - `DOKU_PAYMENT_METHOD_TYPES`

## Status Rencana

### Fase 1

- Verifikasi flow DOKU untuk pembayaran paket tenant di sandbox.
- Pastikan transaksi platform berubah:
  - `pending_payment`
  - `paid`
  - `failed`
  - `expired`
- Pastikan webhook memicu aktivasi subscription dengan aman.

### Fase 2

- Rapikan UI payment platform bila perlu.
- Tambahkan validasi, logging, dan testing untuk webhook + activation flow.
- Siapkan checklist pindah dari sandbox ke production.

### Fase 3

- Setelah platform payment stabil, baru audit ulang implementasi DOKU untuk POS.
- Scope POS awal nanti sebaiknya:
  - cart biasa full payment
  - tanpa split bill
  - sandbox terlebih dahulu

## Catatan Penting

- Migrasi sandbox ke production seharusnya ringan jika config dipisahkan lewat `.env`.
- Yang lebih berisiko bukan pindah environment, tetapi asynchronous payment pada POS.
- Jangan mulai dari DOKU POS sebelum platform payment benar-benar lolos end-to-end test.
