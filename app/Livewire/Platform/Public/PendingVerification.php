<?php

namespace App\Livewire\Platform\Public;

use App\Services\Platform\PendingRegistrationService;
use Livewire\Component;

/**
 * Halaman status verifikasi pendaftaran (Livewire).
 *
 * Menampilkan email tujuan, status pengiriman, dan tombol "Kirim Ulang
 * Verifikasi" dengan hitung mundur. Jaminan anti email-bombing (throttle per
 * email + IP dan cooldown 60 detik) tetap dipegang server lewat
 * `PendingRegistrationService`.
 */
class PendingVerification extends Component
{
    public string $ownerEmail = '';

    public int $cooldownSecondsRemaining = 0;

    public bool $emailSent = false;

    public ?string $status = null;

    public ?string $warning = null;

    public function mount(PendingRegistrationService $pending): void
    {
        $this->ownerEmail = (string) session('pending_registration_email', '');

        $this->refreshCooldown($pending);

        // Pesan hasil kirim ulang (dari sesi) agar tetap muncul saat redirect.
        $this->status = session('status');
        $this->warning = session('warning');
        $this->emailSent = (bool) session('registration_email_sent', false);
    }

    public function resend(PendingRegistrationService $pending): void
    {
        if (! $pending->hasPendingRegistrationsTable()) {
            $this->warning = 'Fitur verifikasi email pendaftaran belum aktif karena migration terbaru belum dijalankan.';

            return;
        }

        $result = $pending->resend($this->ownerEmail, request()->ip());

        if ($result['throttled']) {
            $minutes = (int) ceil(((int) $result['retry_after']) / 60);
            $this->warning = 'Terlalu banyak permintaan dalam waktu singkat. Silakan coba lagi dalam '.$minutes.' menit.';
            $this->status = null;
            $this->emailSent = false;

            return;
        }

        if ($result['missing']) {
            $this->warning = 'Request verifikasi aktif untuk email ini tidak ditemukan. Silakan isi ulang form pendaftaran.';
            $this->status = null;
            $this->emailSent = false;

            return;
        }

        $this->emailSent = (bool) $result['sent'];
        $this->status = $result['status'];
        $this->warning = $result['warning'];

        $this->refreshCooldown($pending);
    }

    private function refreshCooldown(PendingRegistrationService $pending): void
    {
        $registration = $this->ownerEmail !== ''
            ? $pending->findActiveByEmail($this->ownerEmail)
            : null;

        $this->cooldownSecondsRemaining = $pending->cooldownSecondsRemaining($registration);
    }

    public function render()
    {
        return view('livewire.platform.public.pending-verification');
    }
}
