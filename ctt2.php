<?php
/*
Resume Checklist Aplikasi - Siap Produksi

1. Struktur Proyek
- Pastikan folder dan file terorganisir dengan baik
- Gunakan konvensi penamaan yang konsisten
- Pisahkan logic, view, dan konfigurasi

2. Kode dan Standar
- Terapkan standar coding (PSR-12 atau standar tim)
- Hapus kode tidak terpakai dan komentar debugging
- Gunakan linting / static analysis (PHPStan, Psalm, phpcs)

3. Keamanan
- Validasi dan sanitasi input semua form / query
- Hindari SQL injection dengan query parameterized / ORM
- Lindungi dari XSS di output HTML
- Tambahkan proteksi CSRF untuk semua form
- Enkripsi password dengan bcrypt / Argon2
- Jangan simpan kredensial langsung di repo
- Pastikan HTTPS dipaksa di produksi

4. Konfigurasi dan Lingkungan
- Gunakan file .env atau konfigurasi terpisah untuk lingkungan
- Jangan commit data sensitif atau API key
- Konfigurasi error reporting hanya verbose di development
- Siapkan konfigurasi database, cache, mail, dll per lingkungan

5. Testing
- Buat unit test untuk logic penting
- Buat integration test untuk alur utama
- Uji pada beberapa browser dan perangkat
- Uji alur login, transaksi, validasi, dan error handling

6. Performa
- Optimalkan query database
- Gunakan caching untuk data statis / query berat
- Minify dan gabungkan asset CSS/JS
- Buat monitoring waktu respon dan beban server

7. User Experience
- Pastikan tampilan responsif di mobile / desktop
- Berikan feedback yang jelas pada aksi pengguna
- Sediakan pesan error yang informatif
- Cek aksesibilitas dasar (label form, fokus, kontras)

8. Logging dan Monitoring
- Aktifkan logging error / exception
- Catat aktivitas penting aplikasi
- Siapkan alert untuk error kritis

9. Backup dan Recovery
- Siapkan mekanisme backup database
- Uji restore dari backup
- Pastikan data penting tersimpan dengan aman

10. Dokumentasi
- Buat README dengan cara instalasi dan setup
- Dokumentasikan variabel lingkungan penting
- Buat catatan deployment dan dependensi
- Tuliskan flow bisnis utama dan fitur

11. Produksi
- Pastikan versi PHP dan ekstensi sesuai
- Gunakan server web / konfigurasi yang stabil
- Lakukan deployment terkontrol
- Jalankan sanity check setelah release

Gunakan checklist ini sebagai acuan final sebelum aplikasi diterapkan ke produksi.
*/

$customerSelfServiceCycle = [
    'pendaftaran' => [
        'title' => 'Pendaftaran',
        'steps' => [
            'Isi formulir pendaftaran dengan data lengkap',
            'Verifikasi alamat email atau nomor telepon',
            'Pilih paket layanan yang sesuai',
            'Setujui syarat dan ketentuan',
        ],
    ],
    'setup_awal' => [
        'title' => 'Setup Awal',
        'steps' => [
            'Lengkapi profil dan informasi akun',
            'Atur preferensi pembayaran dan notifikasi',
            'Hubungkan metode pembayaran',
            'Pelajari fitur utama dan dashboard',
        ],
    ],
    'penggunaan' => [
        'title' => 'Penggunaan Layanan',
        'steps' => [
            'Akses dashboard self service',
            'Kelola data, transaksi, atau layanan sendiri',
            'Pantau status layanan dan laporan',
            'Gunakan pusat bantuan atau FAQ bila perlu',
        ],
    ],
    'penagihan' => [
        'title' => 'Penagihan dan Pembayaran',
        'steps' => [
            'Terima notifikasi tagihan otomatis',
            'Bayar melalui metode yang sudah disimpan',
            'Lihat riwayat dan detail pembayaran',
            'Perbarui metode pembayaran jika diperlukan',
        ],
    ],
    'perpanjangan' => [
        'title' => 'Perpanjangan dan Upgrade',
        'steps' => [
            'Terima pengingat perpanjangan sebelum jatuh tempo',
            'Pilih perpanjangan otomatis atau manual',
            'Upgrade atau downgrade paket jika dibutuhkan',
            'Konfirmasi perpanjangan dan simpan bukti transaksi',
        ],
    ],
    'dukungan' => [
        'title' => 'Dukungan dan Umpan Balik',
        'steps' => [
            'Akses bantuan mandiri atau knowledge base',
            'Ajukan tiket atau chat bila ada masalah',
            'Pantau status tiket dan respon dukungan',
            'Berikan umpan balik setelah layanan selesai',
        ],
    ],
];

function renderSelfServiceCycle(array $cycle): string
{
    $output = '<h1>Siklus Ideal Self Service Pelanggan</h1>';
    foreach ($cycle as $stage) {
        $output .= '<h2>' . htmlspecialchars($stage['title']) . '</h2><ol>';
        foreach ($stage['steps'] as $step) {
            $output .= '<li>' . htmlspecialchars($step) . '</li>';
        }
        $output .= '</ol>';
    }
    return $output;
}

echo renderSelfServiceCycle($customerSelfServiceCycle);