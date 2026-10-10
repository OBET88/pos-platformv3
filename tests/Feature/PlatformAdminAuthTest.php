<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PlatformAdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_can_login(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $response = $this->post(route('platform.login.store'), [
            'email' => $admin->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('platform.admin.tenants.index'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_user_password_is_stored_as_a_hash(): void
    {
        $user = User::query()->create([
            'name' => 'Secure User',
            'email' => 'secure-user@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $this->assertNotSame('secret123', $user->getRawOriginal('password'));
        $this->assertTrue(Hash::check('secret123', $user->getRawOriginal('password')));
    }

    public function test_non_platform_admin_is_rejected(): void
    {
        User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'secret123',
            'is_platform_admin' => false,
        ]);

        $response = $this->from(route('platform.login'))
            ->post(route('platform.login.store'), [
                'email' => 'user@example.com',
                'password' => 'secret123',
            ]);

        $response->assertRedirect(route('platform.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_locked_after_five_failed_attempts_for_fifteen_minutes(): void
    {
        User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $throttleKey = 'platform-admin@example.com|127.0.0.1';

        foreach (range(1, 5) as $attempt) {
            $response = $this->from(route('platform.login'))
                ->post(route('platform.login.store'), [
                    'email' => 'platform-admin@example.com',
                    'password' => 'wrong-password',
                ]);

            $response->assertRedirect(route('platform.login'));
        }

        $lockedResponse = $this->from(route('platform.login'))
            ->post(route('platform.login.store'), [
                'email' => 'platform-admin@example.com',
                'password' => 'wrong-password',
            ]);

        $lockedResponse->assertRedirect(route('platform.login'));
        $lockedResponse->assertSessionHasErrors('email');
        $this->assertGreaterThan(0, RateLimiter::availableIn($throttleKey));
        $this->assertLessThanOrEqual(900, RateLimiter::availableIn($throttleKey));
        $this->assertGuest();
    }

    public function test_non_platform_admin_cannot_access_platform_dashboard(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => 'secret123',
            'is_platform_admin' => false,
        ]);

        $this->actingAs($user)
            ->get(route('platform.admin.tenants.index'))
            ->assertForbidden();
    }
}
