<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Tenant extends Model
{
    protected $fillable = [
        'code',
        'name',
        'owner_name',
        'owner_email',
        'owner_phone',
        'status',
        'trial_ends_at',
        'onboarded_at',
        'notes',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'onboarded_at' => 'datetime',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class);
    }

    public function databaseConfigs(): HasMany
    {
        return $this->hasMany(TenantDatabaseConfig::class);
    }

    public function provisionLogs(): HasMany
    {
        return $this->hasMany(TenantProvisionLog::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(TenantAudit::class);
    }

    public function inboxItems(): HasMany
    {
        return $this->hasMany(InboxItem::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PlatformPaymentTransaction::class);
    }

    public function accountingAccounts(): HasMany
    {
        return $this->hasMany(AccountingAccount::class);
    }

    public function accountingAccountMappings(): HasMany
    {
        return $this->hasMany(AccountingAccountMapping::class);
    }

    public function accountingJournals(): HasMany
    {
        return $this->hasMany(AccountingJournal::class);
    }

    public function latestSubscription(): ?Subscription
    {
        if ($this->relationLoaded('subscriptions')) {
            return $this->subscriptions->sortByDesc('id')->first();
        }

        return $this->subscriptions()->with('plan')->latest('id')->first();
    }

    public function activeSubscription(): ?Subscription
    {
        if ($this->relationLoaded('subscriptions')) {
            return $this->subscriptions
                ->sortByDesc('id')
                ->first(fn (Subscription $subscription) => $subscription->isCurrentlyActive());
        }

        return $this->subscriptions()
            ->with('plan')
            ->latest('id')
            ->get()
            ->first(fn (Subscription $subscription) => $subscription->isCurrentlyActive());
    }

    public function subscribedFeatures(): Collection
    {
        if ($this->status === 'expired') {
            return collect([
                'menu_subscription_center',
                'subscription_center',
            ])->map(fn (string $feature) => Str::of($feature)->trim()->lower()->toString());
        }

        return $this->activeSubscription()?->featureSet() ?? collect();
    }

    public function hasFeature(string $feature): bool
    {
        return $this->subscribedFeatures()->contains(
            \Illuminate\Support\Str::of($feature)->trim()->lower()->toString()
        );
    }
}
