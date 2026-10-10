<?php

namespace Tests\Feature;

use App\Livewire\Platform\Admin\RegistrationSettingsPage;
use App\Models\Platform\Plan;
use App\Models\Platform\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformAdminRegistrationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_can_open_registration_settings_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin-registration@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('platform.admin.settings.registration'))
            ->assertOk()
            ->assertSee('Atur trial dan paket yang tampil di form daftar.')
            ->assertSee('Paket Form Daftar');
    }

    public function test_platform_admin_can_configure_public_registration_plan_choices_and_default(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin-registration-save@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $basic = Plan::query()->where('code', 'basic')->firstOrFail();
        $pro = Plan::query()->where('code', 'pro')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(RegistrationSettingsPage::class)
            ->set('publicRegistrationPlanCodes', [$basic->code, $pro->code])
            ->set('publicRegistrationDefaultPlanCode', $pro->code)
            ->call('updatePublicRegistrationPlans')
            ->assertHasNoErrors();

        $this->assertSame(
            json_encode([$basic->code, $pro->code]),
            PlatformSetting::query()->where('key', 'public_registration_plan_codes')->value('value')
        );
        $this->assertSame(
            $pro->code,
            PlatformSetting::query()->where('key', 'public_registration_default_plan_code')->value('value')
        );
    }
}
