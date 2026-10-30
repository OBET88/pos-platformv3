<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class TenantPaymentGatewayConfig extends Model
{
    protected $fillable = [
        'tenant_id',
        'provider',
        'mode',
        'client_id_encrypted',
        'secret_key_encrypted',
        'merchant_id_encrypted',
        'merchant_name',
        'callback_url_encrypted',
        'extra_config_encrypted',
        'is_active',
        'is_default',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        // Catatan: kolom `*_encrypted` menyimpan STRING terenkripsi, bukan JSON.
        // Jangan di-cast array; dipakai lewat getter yang mendekripsi.
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForTenant(Builder $query, Tenant|int $tenant): Builder
    {
        return $query->where('tenant_id', $tenant instanceof Tenant ? $tenant->getKey() : $tenant);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function setClientIdAttribute(string $value): void
    {
        $this->attributes['client_id_encrypted'] = $value !== '' ? Crypt::encryptString($value) : null;
    }

    public function setSecretKeyAttribute(string $value): void
    {
        $this->attributes['secret_key_encrypted'] = $value !== '' ? Crypt::encryptString($value) : null;
    }

    public function setMerchantIdAttribute(string $value): void
    {
        $this->attributes['merchant_id_encrypted'] = $value !== '' ? Crypt::encryptString($value) : null;
    }

    public function setCallbackUrlAttribute(string $value): void
    {
        $this->attributes['callback_url_encrypted'] = $value !== '' ? Crypt::encryptString($value) : null;
    }

    public function setExtraConfigAttribute(array $value): void
    {
        $this->attributes['extra_config_encrypted'] = $value !== [] ? Crypt::encryptString(json_encode($value)) : null;
    }

    public function getClientId(): string
    {
        if (! $this->client_id_encrypted) {
            return '';
        }

        try {
            return Crypt::decryptString($this->client_id_encrypted);
        } catch (\Throwable) {
            return '';
        }
    }

    public function getSecretKey(): string
    {
        if (! $this->secret_key_encrypted) {
            return '';
        }

        try {
            return Crypt::decryptString($this->secret_key_encrypted);
        } catch (\Throwable) {
            return '';
        }
    }

    public function getMerchantId(): string
    {
        if (! $this->merchant_id_encrypted) {
            return '';
        }

        try {
            return Crypt::decryptString($this->merchant_id_encrypted);
        } catch (\Throwable) {
            return '';
        }
    }

    public function getCallbackUrl(): string
    {
        if (! $this->callback_url_encrypted) {
            return '';
        }

        try {
            return Crypt::decryptString($this->callback_url_encrypted);
        } catch (\Throwable) {
            return '';
        }
    }

    public function getExtraConfig(): array
    {
        if (! $this->extra_config_encrypted) {
            return [];
        }

        try {
            return json_decode(Crypt::decryptString($this->extra_config_encrypted), true) ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    public function toGatewayConfig(): array
    {
        return [
            'provider' => $this->provider,
            'mode' => $this->mode,
            'client_id' => $this->getClientId(),
            'secret_key' => $this->getSecretKey(),
            'merchant_id' => $this->getMerchantId(),
            'merchant_name' => $this->merchant_name,
            'callback_url' => $this->getCallbackUrl(),
            'extra' => $this->getExtraConfig(),
        ];
    }

    public function isConfigured(): bool
    {
        return $this->getClientId() !== '' && $this->getSecretKey() !== '';
    }
}
