<?php

namespace App\Livewire\Platform\Admin;

use App\Livewire\Platform\Admin\Concerns\InteractsWithPlans;
use App\Models\Platform\Plan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.platform-admin')]
class PlanEditorPage extends Component
{
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

    public function updatePlanMeta(): void
    {
        $validated = $this->validate([
            'planMetaInputs.'.$this->plan->id.'.name' => ['required', 'string', 'max:255'],
            'planMetaInputs.'.$this->plan->id.'.price' => ['required', 'numeric', 'min:0'],
            'planMetaInputs.'.$this->plan->id.'.billing_cycle' => ['required', 'string', 'in:monthly,quarterly,yearly,custom'],
            'planMetaInputs.'.$this->plan->id.'.max_businesses' => ['required', 'integer', 'min:1'],
            'planMetaInputs.'.$this->plan->id.'.max_branches' => ['required', 'integer', 'min:1'],
            'planMetaInputs.'.$this->plan->id.'.max_users' => ['required', 'integer', 'min:1'],
        ]);

        $payload = $validated['planMetaInputs'][$this->plan->id];

        $this->plan->forceFill([
            'name' => $payload['name'],
            'price' => $payload['price'],
            'billing_cycle' => $payload['billing_cycle'],
            'max_businesses' => $payload['max_businesses'],
            'max_branches' => $payload['max_branches'],
            'max_users' => $payload['max_users'],
        ])->save();

        $this->refreshPlanState();

        session()->flash('platform_admin_message', 'Info paket '.$this->plan->name.' berhasil diperbarui.');
    }

    public function togglePlanActive(): void
    {
        if ($this->plan->is_active && $this->activeSubscriptionCountForPlan($this->plan) > 0 && ! ($this->planDeactivationConfirmations[$this->plan->id] ?? false)) {
            $this->planDeactivationConfirmations[$this->plan->id] = true;

            session()->flash(
                'platform_admin_message',
                'Paket '.$this->plan->name.' masih dipakai tenant aktif. Klik sekali lagi untuk konfirmasi nonaktifkan.'
            );

            return;
        }

        $this->plan->forceFill([
            'is_active' => ! $this->plan->is_active,
        ])->save();

        $this->refreshPlanState();
        $this->planDeactivationConfirmations[$this->plan->id] = false;

        session()->flash(
            'platform_admin_message',
            'Paket '.$this->plan->name.' '.($this->plan->is_active ? 'diaktifkan kembali.' : 'dinonaktifkan.')
        );
    }

    public function duplicatePlan(): void
    {
        $duplicate = Plan::query()->create([
            'code' => $this->nextDuplicateCode($this->plan->code),
            'name' => $this->plan->name.' Copy',
            'price' => $this->plan->price,
            'billing_cycle' => $this->plan->billing_cycle,
            'max_businesses' => $this->plan->max_businesses,
            'max_branches' => $this->plan->max_branches,
            'max_users' => $this->plan->max_users,
            'features_json' => $this->plan->features_json ?? [],
            'is_active' => false,
        ]);

        session()->flash('platform_admin_message', 'Paket '.$this->plan->name.' berhasil diduplikasi menjadi '.$duplicate->name.'.');

        $this->redirectRoute('platform.admin.plans.edit', $duplicate, navigate: true);
    }

    public function render()
    {
        return view('livewire.platform.admin.plan-editor-page', [
            'title' => 'Platform Admin - Edit Paket',
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
