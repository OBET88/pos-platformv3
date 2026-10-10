<?php

namespace App\Livewire\Platform\Admin;

use App\Livewire\Platform\Admin\Concerns\InteractsWithPlans;
use App\Models\Platform\Plan;
use Livewire\Attributes\Layout;
use App\Livewire\Platform\Admin\Concerns\AuthorizesPlatformAdmin;
use Livewire\Component;

#[Layout('layouts.platform-admin')]
class PlanMappingPage extends Component
{
    use AuthorizesPlatformAdmin;
    use InteractsWithPlans;

    public Plan $plan;

    public array $planFeatureInputs = [];

    public array $planCustomFeatureInputs = [];

    public array $planSearchInputs = [];

    public array $planMetaInputs = [];

    public array $planDeactivationConfirmations = [];

    public function mount(Plan $plan): void
    {
        $this->plan = $this->plansQuery()->findOrFail($plan->id);
        $this->syncPlanInputs();
    }

    public function savePlanFeatures(): void
    {
        $selectedFeatures = collect($this->planFeatureInputs[$this->plan->id] ?? [])
            ->filter(fn ($feature) => is_string($feature) && array_key_exists($feature, self::FEATURE_CATALOG))
            ->values();

        $selectedFeatures = $this->expandLegacyCompatibility($selectedFeatures);

        $customFeatures = $this->normalizeCustomFeatures((string) ($this->planCustomFeatureInputs[$this->plan->id] ?? ''));

        $this->plan->forceFill([
            'features_json' => $selectedFeatures
                ->merge($customFeatures)
                ->unique()
                ->values()
                ->all(),
        ])->save();

        $this->refreshPlanState();

        session()->flash('platform_admin_message', 'Fitur paket '.$this->plan->name.' berhasil diperbarui.');
    }

    public function selectGroupFeatures(string $groupName): void
    {
        if (! array_key_exists($groupName, self::FEATURE_GROUPS)) {
            return;
        }

        $groupFeatures = collect(self::FEATURE_GROUPS[$groupName]['features'])
            ->filter(fn (string $feature) => array_key_exists($feature, self::FEATURE_CATALOG));

        $selectedFeatures = collect($this->planFeatureInputs[$this->plan->id] ?? [])
            ->filter(fn ($feature) => is_string($feature) && array_key_exists($feature, self::FEATURE_CATALOG))
            ->merge($groupFeatures)
            ->unique()
            ->values()
            ->all();

        $this->planFeatureInputs[$this->plan->id] = $selectedFeatures;
    }

    public function clearGroupFeatures(string $groupName): void
    {
        if (! array_key_exists($groupName, self::FEATURE_GROUPS)) {
            return;
        }

        $groupFeatures = collect(self::FEATURE_GROUPS[$groupName]['features'])->all();

        $this->planFeatureInputs[$this->plan->id] = collect($this->planFeatureInputs[$this->plan->id] ?? [])
            ->reject(fn ($feature) => in_array($feature, $groupFeatures, true))
            ->values()
            ->all();
    }

    public function applyPlanPreset(string $presetKey): void
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

        $this->planFeatureInputs[$this->plan->id] = $features;
        $this->planCustomFeatureInputs[$this->plan->id] = '';
    }

    public function filteredFeatureGroups(): array
    {
        return $this->filteredFeatureGroupsForPlan($this->plan->id, $this->planSearchInputs);
    }

    public function render()
    {
        return view('livewire.platform.admin.plan-mapping-page', [
            'featureCatalog' => $this->featureCatalog(),
            'featureGroups' => $this->featureGroups(),
            'planPresets' => $this->planPresets(),
            'title' => 'Platform Admin - Mapping Paket',
        ]);
    }

    private function syncPlanInputs(): void
    {
        $this->fillPlanInputs(
            $this->plan,
            $this->planFeatureInputs,
            $this->planCustomFeatureInputs,
            $this->planSearchInputs,
            $this->planMetaInputs,
            $this->planDeactivationConfirmations
        );
    }

    private function refreshPlanState(): void
    {
        $this->plan = $this->plansQuery()->findOrFail($this->plan->id);
        $this->syncPlanInputs();
    }
}
