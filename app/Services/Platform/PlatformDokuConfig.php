<?php

namespace App\Services\Platform;

use App\Models\Platform\PlatformSetting;
use Illuminate\Support\Facades\Crypt;

/**
 * Konfigurasi DOKU untuk pembayaran LANGGANAN tenant (renewal/upgrade/downgrade).
 *
 * Berbeda dari kredensial gateway per-tenant (yang dipakai QRIS POS penjualan),
 * konfigurasi ini milik PLATFORM: tenant membayar ke rekening/merchant platform.
 *
 * Disimpan di tabel `platform_settings` (bukan `.env`) agar bisa diatur dari
 * panel admin platform, termasuk di hosting tanpa terminal. Secret key
 * disimpan terenkripsi. Fallback ke `config('services.doku')` bila belum diisi.
 */
class PlatformDokuConfig
{
    public const ENABLED_KEY = 'doku_enabled';

    public const MODE_KEY = 'doku_mode';

    public const CLIENT_ID_KEY = 'doku_client_id';

    public const SECRET_KEY_KEY = 'doku_secret_key_encrypted';

    public const BASE_URL_KEY = 'doku_base_url';

    public const MERCHANT_NAME_KEY = 'doku_merchant_name';

    public const PAYMENT_METHOD_TYPES_KEY = 'doku_payment_method_types';

    /**
     * Nilai mentah untuk form (secret dikembalikan terdekripsi).
     *
     * @return array<string, string>
     */
    public function formValues(): array
    {
        $env = config('services.doku', []);

        return [
            'enabled' => $this->storedBool(self::ENABLED_KEY, (bool) ($env['enabled'] ?? false)),
            'mode' => $this->storedString(self::MODE_KEY, $this->inferModeFromBaseUrl((string) ($env['base_url'] ?? ''))),
            'client_id' => $this->storedString(self::CLIENT_ID_KEY, (string) ($env['client_id'] ?? '')),
            // DB diutamakan; bila kosong, fallback ke .env (agar setup lama tetap jalan).
            'secret_key' => $this->secret() !== '' ? $this->secret() : (string) ($env['secret_key'] ?? ''),
            'base_url' => $this->storedString(self::BASE_URL_KEY, (string) ($env['base_url'] ?? 'https://api-sandbox.doku.com')),
            'merchant_name' => $this->storedString(self::MERCHANT_NAME_KEY, (string) ($env['merchant_name'] ?? '')),
            'payment_method_types' => $this->storedString(self::PAYMENT_METHOD_TYPES_KEY, (string) ($env['payment_method_types'] ?? '')),
        ];
    }

    /**
     * Konfigurasi siap pakai untuk DokuCheckoutService.
     *
     * @return array<string, mixed>
     */
    public function resolve(): array
    {
        $values = $this->formValues();

        return [
            'enabled' => $values['enabled'] === '1',
            'mode' => $values['mode'],
            'client_id' => $values['client_id'],
            'secret_key' => $values['secret_key'],
            'base_url' => $values['base_url'] !== '' ? $values['base_url'] : 'https://api-sandbox.doku.com',
            'merchant_name' => $values['merchant_name'],
            'payment_method_types' => $values['payment_method_types'],
        ];
    }

    public function isEnabled(): bool
    {
        return $this->resolve()['enabled'] === true;
    }

    /**
     * Simpan konfigurasi dari panel admin.
     */
    public function save(
        bool $enabled,
        string $mode,
        string $clientId,
        ?string $secretKey,
        string $baseUrl,
        string $merchantName,
        string $paymentMethodTypes
    ): void {
        $this->put(self::ENABLED_KEY, $enabled ? '1' : '0');
        $this->put(self::MODE_KEY, $mode === 'production' ? 'production' : 'sandbox');
        $this->put(self::CLIENT_ID_KEY, trim($clientId));
        $this->put(self::BASE_URL_KEY, trim($baseUrl));
        $this->put(self::MERCHANT_NAME_KEY, trim($merchantName));
        $this->put(self::PAYMENT_METHOD_TYPES_KEY, trim($paymentMethodTypes));

        // Secret hanya ditimpa bila diisi (kosong = jangan ubah).
        if ($secretKey !== null && trim($secretKey) !== '') {
            $this->put(self::SECRET_KEY_KEY, Crypt::encryptString(trim($secretKey)));
        }
    }

    public function secret(): string
    {
        $stored = $this->storedString(self::SECRET_KEY_KEY, '');

        if ($stored === '') {
            return '';
        }

        try {
            return Crypt::decryptString($stored);
        } catch (\Throwable) {
            return '';
        }
    }

    private function inferModeFromBaseUrl(string $baseUrl): string
    {
        return str_contains(strtolower($baseUrl), 'sandbox') ? 'sandbox' : 'production';
    }

    private function storedString(string $key, string $fallback): string
    {
        $value = PlatformSetting::query()->where('key', $key)->value('value');

        return is_string($value) && $value !== '' ? $value : $fallback;
    }

    private function storedBool(string $key, bool $fallback): string
    {
        $value = PlatformSetting::query()->where('key', $key)->value('value');

        if ($value === null) {
            return $fallback ? '1' : '0';
        }

        return (string) $value === '1' ? '1' : '0';
    }

    private function put(string $key, string $value): void
    {
        PlatformSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
