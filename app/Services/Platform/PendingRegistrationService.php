<?php

namespace App\Services\Platform;

use App\Mail\PlatformRegistrationVerification;
use App\Models\Platform\PendingRegistration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Status & pengiriman ulang verifikasi pendaftaran tenant.
 *
 * Dipakai bersama controller (rute POST, fallback tanpa JavaScript) dan
 * komponen Livewire agar jaminan anti email-bombing tetap satu tempat:
 * throttle per email + IP, cooldown 60 detik per permintaan verifikasi.
 */
class PendingRegistrationService
{
    public const VERIFICATION_COOLDOWN_SECONDS = 60;

    public function hasPendingRegistrationsTable(): bool
    {
        return Schema::hasTable('platform_pending_registrations');
    }

    public function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public function findActiveByEmail(string $ownerEmail): ?PendingRegistration
    {
        return PendingRegistration::query()
            ->where('owner_email', $this->normalizeEmail($ownerEmail))
            ->whereNull('tenant_id')
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();
    }

    public function cooldownSecondsRemaining(?PendingRegistration $pendingRegistration): int
    {
        if (! $pendingRegistration || $pendingRegistration->verification_sent_at === null) {
            return 0;
        }

        return max(
            1,
            self::VERIFICATION_COOLDOWN_SECONDS - $pendingRegistration->verification_sent_at->diffInSeconds(now())
        );
    }

    /**
     * Kirim ulang email verifikasi.
     *
     * @return array{sent:bool, status:?string, warning:?string, throttled:bool, missing:bool, retry_after:?int}
     */
    public function resend(string $ownerEmail, ?string $ip = null): array
    {
        $ownerEmail = $this->normalizeEmail($ownerEmail);
        $key = 'registration-resend:'.($ownerEmail !== '' ? $ownerEmail.'|' : '').$ip;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = max(RateLimiter::availableIn($key), 1);

            return [
                'sent' => false,
                'status' => null,
                'warning' => null,
                'throttled' => true,
                'missing' => false,
                'retry_after' => $seconds,
            ];
        }

        RateLimiter::hit($key, 600);

        $pendingRegistration = $this->findActiveByEmail($ownerEmail);

        if (! $pendingRegistration || ! filled($pendingRegistration->verification_token)) {
            return [
                'sent' => false,
                'status' => null,
                'warning' => null,
                'throttled' => false,
                'missing' => true,
                'retry_after' => null,
            ];
        }

        $payload = (array) ($pendingRegistration->payload ?? []);
        $ownerName = (string) ($payload['owner_name'] ?? 'Owner');
        $companyName = (string) ($payload['company_name'] ?? 'Tenant Baru');

        $pendingRegistration->forceFill([
            'expires_at' => now()->addHours(24),
        ])->save();

        $result = $this->dispatchVerification($pendingRegistration, $ownerName, $companyName);

        return [
            'sent' => (bool) $result['sent'],
            'status' => $result['status'],
            'warning' => $result['warning'],
            'throttled' => false,
            'missing' => false,
            'retry_after' => null,
        ];
    }

    /**
     * @return array{sent:bool, status:?string, warning:?string}
     */
    public function dispatchVerification(PendingRegistration $pendingRegistration, string $ownerName, string $companyName): array
    {
        $ownerEmail = $this->normalizeEmail((string) $pendingRegistration->owner_email);

        if ($this->hasActiveCooldown($pendingRegistration)) {
            $secondsRemaining = $this->cooldownSecondsRemaining($pendingRegistration);

            return [
                'sent' => false,
                'status' => null,
                'warning' => 'Link verifikasi untuk '.$ownerEmail.' baru saja dikirim. Tunggu sekitar '.$secondsRemaining.' detik sebelum mencoba lagi.',
            ];
        }

        if (! $this->shouldSendMailNow()) {
            return [
                'sent' => false,
                'status' => null,
                'warning' => 'Pengiriman email belum terkonfirmasi dari aplikasi. Coba lagi atau hubungi admin untuk verifikasi manual.',
            ];
        }

        try {
            Mail::to($ownerEmail)->send(new PlatformRegistrationVerification(
                ownerName: $ownerName,
                companyName: $companyName,
                verificationUrl: route('platform.register.verify', ['token' => $pendingRegistration->verification_token]),
                expiresAtLabel: $pendingRegistration->expires_at?->timezone(config('app.timezone'))->format('d M Y H:i'),
            ));

            $pendingRegistration->forceFill([
                'verification_sent_at' => now(),
                'expires_at' => now()->addHours(24),
            ])->save();

            return [
                'sent' => true,
                'status' => 'Link verifikasi sudah dikirim ke '.$ownerEmail.'.',
                'warning' => null,
            ];
        } catch (Throwable $exception) {
            Log::warning('Platform registration verification email failed to send.', [
                'owner_email' => $ownerEmail,
                'error' => $exception->getMessage(),
            ]);

            return [
                'sent' => false,
                'status' => null,
                'warning' => 'Email verifikasi gagal dikirim: '.$exception->getMessage(),
            ];
        }
    }

    public function hasActiveCooldown(PendingRegistration $pendingRegistration): bool
    {
        return $pendingRegistration->verification_sent_at !== null
            && $pendingRegistration->verification_sent_at->gt(now()->subSeconds(self::VERIFICATION_COOLDOWN_SECONDS));
    }

    private function shouldSendMailNow(): bool
    {
        if (app()->runningUnitTests()) {
            return true;
        }

        if (! app()->environment('local')) {
            return true;
        }

        $defaultMailer = (string) config('mail.default', 'log');
        $transport = (string) config("mail.mailers.{$defaultMailer}.transport", $defaultMailer);

        if (in_array($transport, ['array', 'log'], true)) {
            return false;
        }

        return filled(config('mail.from.address'))
            && filled(config("mail.mailers.{$defaultMailer}.host"));
    }
}
