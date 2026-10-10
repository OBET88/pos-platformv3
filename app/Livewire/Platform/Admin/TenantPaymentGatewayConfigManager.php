<?php

namespace App\Livewire\Platform\Admin;

use App\Livewire\Platform\Admin\Concerns\AuthorizesPlatformAdmin;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantPaymentGatewayConfig;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.platform-admin')]
class TenantPaymentGatewayConfigManager extends Component
{
    use AuthorizesPlatformAdmin;
    use WithPagination;

    #[Url(as: 'tenant', except: '')]
    public string $selectedTenantId = '';

    public ?int $editingId = null;

    public string $provider = 'doku';

    public string $mode = 'sandbox';

    public string $client_id = '';

    public string $secret_key = '';

    public string $merchant_id = '';

    public string $merchant_name = '';

    public string $callback_url = '';

    public array $extra_config = [];

    public bool $is_active = true;

    public bool $is_default = false;

    public function mount(): void
    {
        $this->authorizePlatformPermission('manage_tenant_gateways');

        $firstTenant = Tenant::query()->latest('id')->first();

        if ($firstTenant && $this->selectedTenantId === '') {
            $this->selectedTenantId = (string) $firstTenant->getKey();
        }
    }

    public function selectTenant(int $tenantId): void
    {
        $this->selectedTenantId = (string) $tenantId;
        $this->resetForm();
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorizePlatformPermission('manage_tenant_gateways');
        $this->resetForm();
        $this->editingId = null;
    }

    public function edit(int $id): void
    {
        $this->authorizePlatformPermission('manage_tenant_gateways');

        $config = TenantPaymentGatewayConfig::query()->findOrFail($id);
        $this->editingId = $config->id;
        $this->provider = $config->provider;
        $this->mode = $config->mode;
        $this->client_id = $config->getClientId();
        $this->secret_key = $config->getSecretKey();
        $this->merchant_id = $config->getMerchantId();
        $this->merchant_name = $config->merchant_name ?? '';
        $this->callback_url = $config->getCallbackUrl();
        $this->extra_config = $config->getExtraConfig();
        $this->is_active = $config->is_active;
        $this->is_default = $config->is_default;
    }

    public function save(): void
    {
        $this->authorizePlatformPermission('manage_tenant_gateways');

        $tenant = $this->selectedTenant();
        if (! $tenant) {
            $this->dispatch('notify', type: 'danger', message: 'Tenant belum dipilih.');

            return;
        }

        $rules = [
            'provider' => ['required', 'string', 'max:50', 'in:doku,midtrans,xendit'],
            'mode' => ['required', 'in:sandbox,production'],
            'client_id' => ['required', 'string', 'max:255'],
            'secret_key' => ['required', 'string', 'max:255'],
            'merchant_id' => ['nullable', 'string', 'max:255'],
            'merchant_name' => ['nullable', 'string', 'max:255'],
            'callback_url' => ['nullable', 'string', 'max:500', 'url'],
            'extra_config' => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
        ];

        if ($this->editingId) {
            $rules['provider'] = ['required', 'string', 'max:50'];
            $rules['client_id'] = ['nullable', 'string', 'max:255'];
            $rules['secret_key'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $this->validate($rules);

        if ($validated['is_default'] ?? false) {
            TenantPaymentGatewayConfig::query()
                ->where('tenant_id', $tenant->id)
                ->where('id', '!=', $this->editingId ?? 0)
                ->update(['is_default' => false]);
        }

        $config = TenantPaymentGatewayConfig::query()->updateOrCreate(
            ['id' => $this->editingId ?? 0],
            [
                'tenant_id' => $tenant->id,
                'provider' => $validated['provider'],
                'mode' => $validated['mode'],
                'client_id_encrypted' => $validated['client_id'] ?? '',
                'secret_key_encrypted' => $validated['secret_key'] ?? '',
                'merchant_id_encrypted' => $validated['merchant_id'] ?? null,
                'merchant_name' => $validated['merchant_name'] ?? null,
                'callback_url_encrypted' => $validated['callback_url'] ?? null,
                'extra_config_encrypted' => $validated['extra_config'] ?? [],
                'is_active' => $validated['is_active'] ?? true,
                'is_default' => $validated['is_default'] ?? false,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]
        );

        $this->resetForm();
        $this->dispatch('notify', type: 'success', message: 'Konfigurasi gateway '.($this->editingId ? 'diperbarui' : 'ditambahkan').'.');
    }

    public function delete(int $id): void
    {
        $this->authorizePlatformPermission('manage_tenant_gateways');

        $config = TenantPaymentGatewayConfig::query()->findOrFail($id);

        if ($config->is_default) {
            $this->dispatch('notify', type: 'danger', message: 'Konfigurasi default tidak bisa dihapus. Matikan default dulu.');

            return;
        }

        $config->delete();
        $this->dispatch('notify', type: 'success', message: 'Konfigurasi gateway dihapus.');
    }

    public function toggleDefault(int $id): void
    {
        $this->authorizePlatformPermission('manage_tenant_gateways');

        $config = TenantPaymentGatewayConfig::query()->findOrFail($id);
        $tenant = $config->tenant;

        TenantPaymentGatewayConfig::query()
            ->where('tenant_id', $tenant->id)
            ->update(['is_default' => false]);

        $config->update(['is_default' => true]);
        $this->dispatch('notify', type: 'success', message: 'Konfigurasi dijadikan default untuk tenant ini.');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->provider = 'doku';
        $this->mode = 'sandbox';
        $this->client_id = '';
        $this->secret_key = '';
        $this->merchant_id = '';
        $this->merchant_name = '';
        $this->callback_url = '';
        $this->extra_config = [];
        $this->is_active = true;
        $this->is_default = false;
        $this->resetValidation();
    }

    public function selectedTenant(): ?Tenant
    {
        if ($this->selectedTenantId === '') {
            return null;
        }

        return Tenant::query()->find((int) $this->selectedTenantId);
    }

    public function render()
    {
        $tenants = Tenant::query()->latest('id')->get();
        $selectedTenant = $this->selectedTenant();
        $configs = $selectedTenant
            ? $selectedTenant->paymentGatewayConfigs()->latest('id')->paginate(10)
            : collect()->paginate(10);

        return view('livewire.platform.admin.tenant-payment-gateway-config-manager', [
            'tenants' => $tenants,
            'selectedTenant' => $selectedTenant,
            'configs' => $configs,
            'title' => 'Platform Admin - Konfigurasi Payment Gateway Tenant',
            'providers' => [
                ['value' => 'doku', 'label' => 'DOKU'],
                ['value' => 'midtrans', 'label' => 'Midtrans'],
                ['value' => 'xendit', 'label' => 'Xendit'],
            ],
        ]);
    }
}
