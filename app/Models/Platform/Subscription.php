<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Subscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'started_at',
        'expired_at',
        'trial_ends_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expired_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PlatformPaymentTransaction::class);
    }

    public function expiresAt(): ?Carbon
    {
        return $this->expired_at ?? $this->trial_ends_at;
    }

    public function isCurrentlyActive(): bool
    {
        if (!in_array($this->status, ['trial', 'active'], true)) {
            return false;
        }

        $expiresAt = $this->expiresAt();

        return $expiresAt === null || $expiresAt->isFuture();
    }

    public function hasFeature(string $feature): bool
    {
        return $this->featurePlan() !== null && $this->featurePlan()->hasFeature($feature);
    }

    public function featurePlan(): ?Plan
    {
        if ($this->status === 'trial') {
            $trialPlan = $this->resolveTrialFeaturePlan();

            if ($trialPlan !== null) {
                return $trialPlan;
            }
        }

        return $this->plan;
    }

    public function featureSet(): Collection
    {
        return $this->featurePlan()?->features() ?? collect();
    }

    private function resolveTrialFeaturePlan(): ?Plan
    {
        if ($this->relationLoaded('tenant')) {
            $tenant = $this->tenant;

            if ($tenant && $tenant->relationLoaded('subscriptions')) {
                $loadedTrialPlan = $tenant->subscriptions
                    ->pluck('plan')
                    ->filter(fn ($plan) => $plan instanceof Plan && $plan->code === 'trial')
                    ->first();

                if ($loadedTrialPlan instanceof Plan) {
                    return $loadedTrialPlan;
                }
            }
        }

        if ($this->relationLoaded('plan') && $this->plan?->code === 'trial') {
            return $this->plan;
        }

        return Plan::query()
            ->where('code', 'trial')
            ->where('is_active', true)
            ->first();
    }
}
