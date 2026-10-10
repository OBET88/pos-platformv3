<?php

namespace Tests\Feature;

use App\Livewire\Platform\Admin\InboxManager;
use App\Models\Platform\InboxItem;
use App\Models\Platform\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformAdminInboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_can_open_dedicated_inbox_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'inbox-page-tenant',
            'name' => 'Inbox Page Tenant',
            'owner_name' => 'Owner Inbox',
            'owner_email' => 'owner-inbox@example.com',
            'status' => 'trial',
        ]);

        InboxItem::query()->create([
            'tenant_id' => $tenant->id,
            'category' => 'attention',
            'event_key' => 'tenant.subscription.expiring',
            'title' => 'Tenant mau expire',
            'message' => 'Subscription tenant memasuki reminder H-3.',
            'tone' => 'amber',
            'fingerprint' => 'test-inbox-item',
            'occurred_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('platform.admin.inbox.index'))
            ->assertOk()
            ->assertSee('Inbox Lengkap')
            ->assertSee('Inbox Page Tenant')
            ->assertSee('Tenant mau expire');
    }

    public function test_platform_admin_can_mark_inbox_items_read_and_unread(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'inbox-read-tenant',
            'name' => 'Inbox Read Tenant',
            'owner_name' => 'Owner Read',
            'owner_email' => 'owner-read@example.com',
            'status' => 'active',
        ]);

        $item = InboxItem::query()->create([
            'tenant_id' => $tenant->id,
            'category' => 'renewal',
            'event_key' => 'tenant.subscription.renewed',
            'title' => 'Perpanjangan terbaru',
            'message' => 'Subscription tenant diperpanjang.',
            'tone' => 'emerald',
            'fingerprint' => 'test-inbox-read',
            'occurred_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(InboxManager::class)
            ->call('markAsRead', $item->id)
            ->assertSet('readFilter', 'all')
            ->call('markAsUnread', $item->id);

        $this->assertDatabaseMissing('platform_inbox_item_reads', [
            'inbox_item_id' => $item->id,
            'user_id' => $admin->id,
        ]);
    }

    public function test_platform_admin_inbox_highlights_tenant_subscription_requests(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'request-upgrade-tenant',
            'name' => 'Tenant Request Upgrade',
            'owner_name' => 'Owner Request',
            'owner_email' => 'owner-request@example.com',
            'status' => 'active',
        ]);

        InboxItem::query()->create([
            'tenant_id' => $tenant->id,
            'category' => 'renewal',
            'event_key' => 'tenant.subscription.upgrade_requested',
            'title' => 'Upgrade requested',
            'message' => 'Tenant meminta upgrade paket.',
            'tone' => 'sky',
            'fingerprint' => 'test-upgrade-request',
            'payload' => [
                'requested_plan_name' => 'Pro',
                'current_plan_name' => 'Basic',
                'requested_by_name' => 'Owner Request',
                'requested_by_email' => 'owner-request@example.com',
            ],
            'occurred_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('platform.admin.inbox.index'))
            ->assertOk()
            ->assertSee('Request Upgrade')
            ->assertSee('Dari tenant')
            ->assertSee('Target paket: Pro')
            ->assertSee('Paket saat ini: Basic');
    }

    public function test_platform_admin_can_open_tenant_subscription_context_from_inbox(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'request-renew-tenant',
            'name' => 'Tenant Request Renew',
            'owner_name' => 'Owner Renew',
            'owner_email' => 'owner-renew@example.com',
            'status' => 'active',
        ]);

        $item = InboxItem::query()->create([
            'tenant_id' => $tenant->id,
            'category' => 'renewal',
            'event_key' => 'tenant.subscription.renewal_requested',
            'title' => 'Renewal requested',
            'message' => 'Tenant meminta perpanjangan.',
            'tone' => 'emerald',
            'fingerprint' => 'test-renew-request',
            'occurred_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(InboxManager::class)
            ->call('openTenantWorkspace', $item->id)
            ->assertRedirect(route('platform.admin.tenants.index', [
                'tenant' => $tenant->id,
                'focus' => 'subscription',
            ]).'#subscription-panel');

        $this->assertDatabaseHas('platform_inbox_item_reads', [
            'inbox_item_id' => $item->id,
            'user_id' => $admin->id,
        ]);
        $this->assertSame(
            'Request tenant dibuka. Panel langganan tenant sudah difokuskan untuk follow-up.',
            Session::get('platform_admin_message')
        );
    }

    public function test_platform_admin_can_update_persistent_follow_up_status_from_inbox(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'follow-up-tenant',
            'name' => 'Tenant Follow Up',
            'owner_name' => 'Owner Follow Up',
            'owner_email' => 'owner-follow-up@example.com',
            'status' => 'active',
        ]);

        $item = InboxItem::query()->create([
            'tenant_id' => $tenant->id,
            'category' => 'renewal',
            'event_key' => 'tenant.subscription.upgrade_requested',
            'title' => 'Upgrade requested',
            'message' => 'Tenant meminta upgrade paket.',
            'tone' => 'sky',
            'fingerprint' => 'test-follow-up-request',
            'occurred_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(InboxManager::class)
            ->call('updateFollowUpStatus', $item->id, 'in_progress');

        $item->refresh();

        $this->assertSame('in_progress', $item->follow_up_status);
        $this->assertSame($admin->id, $item->follow_up_user_id);
        $this->assertNotNull($item->follow_up_at);
        $this->assertSame('Ditandai sedang ditindaklanjuti dari inbox admin.', $item->follow_up_note);
    }
}
