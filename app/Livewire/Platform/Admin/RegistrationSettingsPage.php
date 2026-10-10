<?php

namespace App\Livewire\Platform\Admin;

use App\Models\Platform\Plan;
use App\Services\Platform\PlatformSettings;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.platform-admin')]
class RegistrationSettingsPage extends Component
{
    public int $defaultTrialDays = 14;

    public array $publicRegistrationPlanCodes = [];

    public string $publicRegistrationDefaultPlanCode = '';

    public function mount(PlatformSettings $platformSettings): void
    {
        $this->defaultTrialDays = $platformSettings->defaultTrialDays();
        $this->publicRegistrationPlanCodes = $platformSettings->publicRegistrationPlanCodes();
        $this->publicRegistrationDefaultPlanCode = $platformSettings->publicRegistrationDefaultPlanCode() ?? '';
    }

    public function updateDefaultTrialDays(PlatformSettings $platformSettings): void
    {
        $validated = $this->validate([
            'defaultTrialDays' => ['required', 'integer', 'min:1', 'max:90'],
        ]);

        $resolved = $platformSettings->setDefaultTrialDays((int) $validated['defaultTrialDays']);
        $this->defaultTrialDays = $resolved;

        session()->flash('platform_admin_message', 'Default trial duration berhasil diperbarui menjadi '.$resolved.' hari.');
    }

    public function updatePublicRegistrationPlans(PlatformSettings $platformSettings): void
    {
        $activePlanCodes = Plan::query()
            ->where('is_active', true)
            ->pluck('code')
            ->all();

        $validated = $this->validate([
            'publicRegistrationPlanCodes' => ['required', 'array', 'min:1'],
            'publicRegistrationPlanCodes.*' => ['required', 'string', Rule::in($activePlanCodes)],
            'publicRegistrationDefaultPlanCode' => ['required', 'string', Rule::in($activePlanCodes)],
        ]);

        $resolvedCodes = $platformSettings->setPublicRegistrationPlanCodes($validated['publicRegistrationPlanCodes']);
        $resolvedDefault = $platformSettings->setPublicRegistrationDefaultPlanCode(
            $validated['publicRegistrationDefaultPlanCode'],
            $resolvedCodes
        );

        $this->publicRegistrationPlanCodes = $resolvedCodes;
        $this->publicRegistrationDefaultPlanCode = $resolvedDefault ?? '';

        session()->flash(
            'platform_admin_message',
            'Pilihan paket pendaftaran publik berhasil diperbarui. Default form sekarang mengarah ke '.strtoupper($this->publicRegistrationDefaultPlanCode).'.'
        );
    }

    public function render()
    {
        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        return view('livewire.platform.admin.registration-settings-page', [
            'plans' => $plans,
            'publicRegistrationPreviewPlans' => $this->registrationPlanPreview($plans),
            'title' => 'Platform Admin - Setting Pendaftaran',
        ]);
    }

    private function registrationPlanPreview(Collection $plans): Collection
    {
        return $plans
            ->filter(fn (Plan $plan) => in_array($plan->code, $this->publicRegistrationPlanCodes, true))
            ->values();
    }
}
