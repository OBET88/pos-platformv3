<?php

namespace App\Livewire\Platform\Admin;

use App\Livewire\Platform\Admin\Concerns\AuthorizesPlatformAdmin;
use App\Services\Platform\PlatformDokuConfig;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Pengaturan gateway pembayaran LANGGANAN (milik platform), dipakai tenant
 * untuk membayar renewal/upgrade/downgrade. Berbeda dari kredensial gateway
 * per-tenant yang dipakai QRIS POS penjualan.
 */
#[Layout('layouts.platform-admin')]
class SubscriptionGatewaySettingsPage extends Component
{
    use AuthorizesPlatformAdmin;

    public bool $enabled = false;

    public string $mode = 'sandbox';

    public string $clientId = '';

    public string $secretKey = '';

    public string $baseUrl = 'https://api-sandbox.doku.com';

    public string $merchantName = '';

    public string $paymentMethodTypes = '';

    public bool $secretConfigured = false;

    public function mount(PlatformDokuConfig $config): void
    {
        $this->authorizePlatformPermission('manage_settings');

        $values = $config->formValues();

        $this->enabled = $values['enabled'] === '1';
        $this->mode = $values['mode'];
        $this->clientId = $values['client_id'];
        $this->secretKey = '';
        $this->baseUrl = $values['base_url'];
        $this->merchantName = $values['merchant_name'];
        $this->paymentMethodTypes = $values['payment_method_types'];
        $this->secretConfigured = $values['secret_key'] !== '';
    }

    public function save(PlatformDokuConfig $config): void
    {
        $this->authorizePlatformPermission('manage_settings');

        $validated = $this->validate([
            'enabled' => ['boolean'],
            'mode' => ['required', 'in:sandbox,production'],
            'clientId' => ['nullable', 'string', 'max:255'],
            'secretKey' => ['nullable', 'string', 'max:255'],
            'baseUrl' => ['required', 'string', 'max:500', 'url'],
            'merchantName' => ['nullable', 'string', 'max:255'],
            'paymentMethodTypes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['enabled'] && trim((string) $validated['clientId']) === '') {
            $this->addError('clientId', 'Client ID wajib diisi saat gateway diaktifkan.');

            return;
        }

        if ($validated['enabled'] && trim((string) $validated['secretKey']) === '' && ! $this->secretConfigured) {
            $this->addError('secretKey', 'Secret Key wajib diisi saat pertama kali mengaktifkan gateway.');

            return;
        }

        $config->save(
            enabled: (bool) $validated['enabled'],
            mode: (string) $validated['mode'],
            clientId: (string) $validated['clientId'],
            secretKey: $validated['secretKey'] ?? null,
            baseUrl: (string) $validated['baseUrl'],
            merchantName: (string) ($validated['merchantName'] ?? ''),
            paymentMethodTypes: (string) ($validated['paymentMethodTypes'] ?? ''),
        );

        $this->secretKey = '';
        $this->secretConfigured = $config->secret() !== '';

        session()->flash('platform_admin_message', 'Pengaturan gateway langganan berhasil disimpan.');
    }

    public function render(PlatformDokuConfig $config)
    {
        return view('livewire.platform.admin.subscription-gateway-settings-page', [
            'title' => 'Platform Admin - Gateway Langganan',
            'activeGateway' => $config->isEnabled(),
        ]);
    }
}
