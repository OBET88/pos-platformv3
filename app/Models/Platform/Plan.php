<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Plan extends Model
{
    protected $fillable = [
        'code',
        'name',
        'price',
        'billing_cycle',
        'max_businesses',
        'max_branches',
        'max_users',
        'features_json',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features_json' => 'array',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PlatformPaymentTransaction::class);
    }

    public function features(): Collection
    {
        return collect($this->features_json ?? [])
            ->filter(fn ($feature) => is_string($feature) && trim($feature) !== '')
            ->map(fn (string $feature) => Str::of($feature)->trim()->lower()->toString())
            ->unique()
            ->values();
    }

    public function hasFeature(string $feature): bool
    {
        return $this->features()->contains(Str::of($feature)->trim()->lower()->toString());
    }
}
