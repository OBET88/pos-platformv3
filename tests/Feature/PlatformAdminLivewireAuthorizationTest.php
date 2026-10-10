<?php

namespace Tests\Feature;

use App\Livewire\Platform\Admin\AnnouncementManager;
use App\Livewire\Platform\Admin\InboxManager;
use App\Livewire\Platform\Admin\PaymentValidationManager;
use App\Livewire\Platform\Admin\PlanEditorPage;
use App\Livewire\Platform\Admin\PlanManager;
use App\Livewire\Platform\Admin\PlanMappingPage;
use App\Livewire\Platform\Admin\RegistrationSettingsPage;
use App\Livewire\Platform\Admin\TenantManager;
use App\Models\Platform\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Request update Livewire tidak melewati middleware route halaman, sehingga setiap
 * komponen admin wajib memverifikasi sendiri otorisasinya (trait
 * AuthorizesPlatformAdmin). Pengguna tanpa hak admin harus menerima halaman 403.
 */
class PlatformAdminLivewireAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public static function adminComponentProvider(): array
    {
        return [
            'TenantManager' => [TenantManager::class, false],
            'InboxManager' => [InboxManager::class, false],
            'PaymentValidationManager' => [PaymentValidationManager::class, false],
            'AnnouncementManager' => [AnnouncementManager::class, false],
            'PlanManager' => [PlanManager::class, false],
            'PlanEditorPage' => [PlanEditorPage::class, true],
            'PlanMappingPage' => [PlanMappingPage::class, true],
            'RegistrationSettingsPage' => [RegistrationSettingsPage::class, false],
        ];
    }

    private function makePlan(): Plan
    {
        return Plan::query()->create([
            'code' => 'basic-auth-test-'.uniqid(),
            'name' => 'Basic Auth Test',
            'price' => 100000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 1,
            'max_branches' => 1,
            'max_users' => 3,
            'features_json' => ['pos'],
            'is_active' => true,
        ]);
    }

    private function renderComponent(object|string $component, bool $needsPlan): string
    {
        return Livewire::test($component, $needsPlan ? ['plan' => $this->makePlan()] : [])->html();
    }

    public function test_guest_gets_forbidden_page_on_admin_components(): void
    {
        foreach (self::adminComponentProvider() as $label => [$component, $needsPlan]) {
            $html = $this->renderComponent($component, $needsPlan);

            $this->assertStringContainsString(
                'Forbidden',
                $html,
                "Komponen {$label} seharusnya memblokir tamu dengan halaman 403."
            );
        }
    }

    public function test_non_admin_user_gets_forbidden_page_on_admin_components(): void
    {
        User::query()->create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => 'secret123',
            'is_platform_admin' => false,
        ]);
        $this->actingAs(User::query()->where('email', 'regular@example.com')->first());

        foreach (self::adminComponentProvider() as $label => [$component, $needsPlan]) {
            $html = $this->renderComponent($component, $needsPlan);

            $this->assertStringContainsString(
                'Forbidden',
                $html,
                "Komponen {$label} seharusnya memblokir non-admin dengan halaman 403."
            );
        }
    }

    public function test_platform_admin_can_render_admin_components(): void
    {
        User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);
        $this->actingAs(User::query()->where('email', 'admin@example.com')->first());

        foreach (self::adminComponentProvider() as $label => [$component, $needsPlan]) {
            Livewire::test($component, $needsPlan ? ['plan' => $this->makePlan()] : [])
                ->assertSuccessful();
        }

        $this->assertTrue(true);
    }
}
