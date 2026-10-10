<?php

namespace App\Livewire\Platform\Admin;

use App\Livewire\Platform\Admin\Concerns\InteractsWithPlans;
use App\Models\Platform\Plan;
use App\Services\Platform\PlatformSettings;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.platform-admin')]
class PlanManager extends Component
{
    use InteractsWithPlans;

    public array $newPlanForm = [
        'code' => '',
        'name' => '',
        'price' => '',
        'billing_cycle' => 'monthly',
        'max_businesses' => 1,
        'max_branches' => 1,
        'max_users' => 3,
        'preset' => 'basic',
        'is_active' => true,
    ];

    public array $planFeatureInputs = [];

    public array $planCustomFeatureInputs = [];

    public array $planSearchInputs = [];

    public array $planMetaInputs = [];

    public array $planDeactivationConfirmations = [];

    public bool $showCreatePlanForm = false;

    public function mount(): void
    {
        $this->applyPresetDefaults($this->newPlanForm['preset']);
        $this->syncPlanInputs();
    }

    public function updatedNewPlanFormPreset(string $presetKey): void
    {
        $this->applyPresetDefaults($presetKey);
    }

    public function savePlanFeatures(int $planId): void
    {
        $plan = $this->plansQuery()->findOrFail($planId);

        $selectedFeatures = collect($this->planFeatureInputs[$planId] ?? [])
            ->filter(fn ($feature) => is_string($feature) && array_key_exists($feature, self::FEATURE_CATALOG))
            ->values();

        $selectedFeatures = $this->expandLegacyCompatibility($selectedFeatures);

        $customFeatures = $this->normalizeCustomFeatures((string) ($this->planCustomFeatureInputs[$planId] ?? ''));

        $plan->forceFill([
            'features_json' => $selectedFeatures
                ->merge($customFeatures)
                ->unique()
                ->values()
                ->all(),
        ])->save();

        $this->syncSinglePlan($plan->fresh());

        session()->flash('platform_admin_message', 'Fitur paket '.$plan->name.' berhasil diperbarui.');
    }

    public function createPlan(): void
    {
        $validated = $this->validate([
            'newPlanForm.code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:plans,code'],
            'newPlanForm.name' => ['required', 'string', 'max:255'],
            'newPlanForm.price' => ['required', 'numeric', 'min:0'],
            'newPlanForm.billing_cycle' => ['required', 'string', 'in:monthly,quarterly,yearly,custom'],
            'newPlanForm.max_businesses' => ['required', 'integer', 'min:1'],
            'newPlanForm.max_branches' => ['required', 'integer', 'min:1'],
            'newPlanForm.max_users' => ['required', 'integer', 'min:1'],
            'newPlanForm.preset' => ['nullable', 'string'],
            'newPlanForm.is_active' => ['boolean'],
        ])['newPlanForm'];

        $presetFeatures = collect($this->planPresets()[$validated['preset']]['features'] ?? [])
            ->filter(fn (string $feature) => array_key_exists($feature, self::FEATURE_CATALOG))
            ->unique()
            ->values()
            ->all();

        $plan = Plan::query()->create([
            'code' => Str::lower($validated['code']),
            'name' => $validated['name'],
            'price' => $validated['price'],
            'billing_cycle' => $validated['billing_cycle'],
            'max_businesses' => $validated['max_businesses'],
            'max_branches' => $validated['max_branches'],
            'max_users' => $validated['max_users'],
            'features_json' => $presetFeatures,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        $this->resetNewPlanForm();
        $this->syncSinglePlan($plan);
        $this->showCreatePlanForm = false;

        session()->flash('platform_admin_message', 'Paket baru '.$plan->name.' berhasil dibuat.');
    }

    public function updatePlanMeta(int $planId): void
    {
        $plan = Plan::query()->findOrFail($planId);

        $validated = $this->validate([
            'planMetaInputs.'.$planId.'.code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:plans,code,'.$plan->id],
            'planMetaInputs.'.$planId.'.name' => ['required', 'string', 'max:255'],
            'planMetaInputs.'.$planId.'.price' => ['required', 'numeric', 'min:0'],
            'planMetaInputs.'.$planId.'.billing_cycle' => ['required', 'string', 'in:monthly,quarterly,yearly,custom'],
            'planMetaInputs.'.$planId.'.max_businesses' => ['required', 'integer', 'min:1'],
            'planMetaInputs.'.$planId.'.max_branches' => ['required', 'integer', 'min:1'],
            'planMetaInputs.'.$planId.'.max_users' => ['required', 'integer', 'min:1'],
        ]);

        $payload = $validated['planMetaInputs'][$planId];

        $plan->forceFill([
            'code' => Str::lower($payload['code']),
            'name' => $payload['name'],
            'price' => $payload['price'],
            'billing_cycle' => $payload['billing_cycle'],
            'max_businesses' => $payload['max_businesses'],
            'max_branches' => $payload['max_branches'],
            'max_users' => $payload['max_users'],
        ])->save();

        $this->syncSinglePlan($plan->fresh());

        session()->flash('platform_admin_message', 'Info paket '.$plan->name.' berhasil diperbarui.');
    }

    public function togglePlanActive(int $planId): void
    {
        $plan = Plan::query()->findOrFail($planId);

        if ($plan->is_active && $this->activeSubscriptionCountForPlan($plan) > 0 && ! ($this->planDeactivationConfirmations[$planId] ?? false)) {
            $this->planDeactivationConfirmations[$planId] = true;

            session()->flash(
                'platform_admin_message',
                'Paket '.$plan->name.' masih dipakai tenant aktif. Klik sekali lagi untuk konfirmasi nonaktifkan.'
            );

            return;
        }

        $plan->forceFill([
            'is_active' => ! $plan->is_active,
        ])->save();

        $this->syncSinglePlan($plan->fresh());
        $this->planDeactivationConfirmations[$planId] = false;

        session()->flash(
            'platform_admin_message',
            'Paket '.$plan->name.' '.($plan->is_active ? 'diaktifkan kembali.' : 'dinonaktifkan.')
        );
    }

    public function duplicatePlan(int $planId): void
    {
        $plan = Plan::query()->findOrFail($planId);

        $duplicate = Plan::query()->create([
            'code' => $this->nextDuplicateCode($plan->code),
            'name' => $plan->name.' Copy',
            'price' => $plan->price,
            'billing_cycle' => $plan->billing_cycle,
            'max_businesses' => $plan->max_businesses,
            'max_branches' => $plan->max_branches,
            'max_users' => $plan->max_users,
            'features_json' => $plan->features_json ?? [],
            'is_active' => false,
        ]);

        $this->syncSinglePlan($duplicate);

        session()->flash('platform_admin_message', 'Paket '.$plan->name.' berhasil diduplikasi menjadi '.$duplicate->name.'.');
    }

    public function openCreatePlanForm(): void
    {
        $this->showCreatePlanForm = true;
    }

    public function closeCreatePlanForm(): void
    {
        $this->showCreatePlanForm = false;
    }

    public function selectGroupFeatures(int $planId, string $groupName): void
    {
        if (! array_key_exists($groupName, self::FEATURE_GROUPS)) {
            return;
        }

        $groupFeatures = collect(self::FEATURE_GROUPS[$groupName]['features'])
            ->filter(fn (string $feature) => array_key_exists($feature, self::FEATURE_CATALOG));

        $selectedFeatures = collect($this->planFeatureInputs[$planId] ?? [])
            ->filter(fn ($feature) => is_string($feature) && array_key_exists($feature, self::FEATURE_CATALOG))
            ->merge($groupFeatures)
            ->unique()
            ->values()
            ->all();

        $this->planFeatureInputs[$planId] = $selectedFeatures;
    }

    public function clearGroupFeatures(int $planId, string $groupName): void
    {
        if (! array_key_exists($groupName, self::FEATURE_GROUPS)) {
            return;
        }

        $groupFeatures = collect(self::FEATURE_GROUPS[$groupName]['features'])->all();

        $this->planFeatureInputs[$planId] = collect($this->planFeatureInputs[$planId] ?? [])
            ->reject(fn ($feature) => in_array($feature, $groupFeatures, true))
            ->values()
            ->all();
    }

    public function applyPlanPreset(int $planId, string $presetKey): void
    {
        $preset = $this->planPresets()[$presetKey] ?? null;

        if ($preset === null) {
            return;
        }

        $features = collect($preset['features'] ?? [])
            ->filter(fn (string $feature) => array_key_exists($feature, self::FEATURE_CATALOG))
            ->unique()
            ->values()
            ->all();

        $this->planFeatureInputs[$planId] = $features;
        $this->planCustomFeatureInputs[$planId] = '';
    }

    public function filteredFeatureGroups(int $planId): array
    {
        return $this->filteredFeatureGroupsForPlan($planId, $this->planSearchInputs);
    }

    public function render()
    {
        $plans = $this->plansQuery()->get();

        $platformSettings = app(PlatformSettings::class);
        $publicCodes = $platformSettings->publicRegistrationPlanCodes();

        return view('livewire.platform.admin.plan-manager', [
            'plans' => $plans,
            'featureCatalog' => $this->featureCatalog(),
            'featureGroups' => $this->featureGroups(),
            'planPresets' => $this->planPresets(),
            'publicRegistrationPlanCodes' => $publicCodes,
            'publicRegistrationDefaultPlanCode' => $platformSettings->publicRegistrationDefaultPlanCode(),
            'title' => 'Platform Admin - Paket & Fitur',
        ]);
    }

    private function syncPlanInputs(): void
    {
        $this->plansQuery()->get()->each(fn (Plan $plan) => $this->syncSinglePlan($plan));
    }

    private function syncSinglePlan(Plan $plan): void
    {
        $this->fillPlanInputs(
            $plan,
            $this->planFeatureInputs,
            $this->planCustomFeatureInputs,
            $this->planSearchInputs,
            $this->planMetaInputs,
            $this->planDeactivationConfirmations
        );
    }

    private function resetNewPlanForm(): void
    {
        $this->newPlanForm = [
            'code' => '',
            'name' => '',
            'price' => '',
            'billing_cycle' => 'monthly',
            'max_businesses' => 1,
            'max_branches' => 1,
            'max_users' => 3,
            'preset' => 'basic',
            'is_active' => true,
        ];

        $this->applyPresetDefaults($this->newPlanForm['preset']);
    }

    private function applyPresetDefaults(?string $presetKey): void
    {
        if (! is_string($presetKey) || ! array_key_exists($presetKey, $this->planPresets())) {
            return;
        }

        $defaults = $this->planPresetDefaults($presetKey);

        $this->newPlanForm['max_businesses'] = $defaults['max_businesses'];
        $this->newPlanForm['max_branches'] = $defaults['max_branches'];
        $this->newPlanForm['max_users'] = $defaults['max_users'];
    }
}
