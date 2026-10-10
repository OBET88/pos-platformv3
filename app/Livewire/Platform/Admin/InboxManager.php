<?php

namespace App\Livewire\Platform\Admin;

use App\Models\Platform\InboxItem;
use App\Models\Platform\InboxItemRead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.platform-admin')]
class InboxManager extends Component
{
    use WithPagination;

    #[Url(as: 'filter', except: 'all')]
    public string $categoryFilter = 'all';

    #[Url(as: 'state', except: 'all')]
    public string $readFilter = 'all';

    #[Url(as: 'followup', except: 'all')]
    public string $followUpFilter = 'all';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatingReadFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFollowUpFilter(): void
    {
        $this->resetPage();
    }

    public function markAsRead(int $itemId): void
    {
        $item = InboxItem::query()->findOrFail($itemId);

        InboxItemRead::query()->updateOrCreate(
            [
                'inbox_item_id' => $item->getKey(),
                'user_id' => auth()->id(),
            ],
            [
                'read_at' => now(),
            ]
        );
    }

    public function markAsUnread(int $itemId): void
    {
        InboxItemRead::query()
            ->where('inbox_item_id', $itemId)
            ->where('user_id', auth()->id())
            ->delete();
    }

    public function markFilteredAsRead(): void
    {
        $itemIds = $this->filteredInboxQuery()->pluck('platform_inbox_items.id');

        foreach ($itemIds as $itemId) {
            InboxItemRead::query()->updateOrCreate(
                [
                    'inbox_item_id' => $itemId,
                    'user_id' => auth()->id(),
                ],
                [
                    'read_at' => now(),
                ]
            );
        }

        session()->flash('platform_admin_message', 'Inbox yang sedang difilter sudah ditandai terbaca.');
    }

    public function openTenantWorkspace(int $itemId)
    {
        $item = InboxItem::query()->with('tenant')->findOrFail($itemId);

        $this->markAsRead($itemId);

        $focusPanel = match (true) {
            in_array($item->event_key, [
                'tenant.subscription.upgrade_requested',
                'tenant.subscription.renewal_requested',
                'tenant.subscription.expiring',
                'tenant.subscription.expired',
                'tenant.subscription.renewed',
                'tenant.plan.updated',
            ], true) => 'subscription',
            in_array($item->event_key, [
                'tenant.payment.awaiting_validation',
                'tenant.payment.rejected',
                'tenant.payment.approved',
                'tenant.payment.proof_submitted',
            ], true) => 'billing',
            $item->event_key === 'tenant.profile.updated' => 'profile',
            in_array($item->event_key, [
                'tenant.provision.failed',
                'tenant.provision.recovered',
                'tenant.bootstrap.failed',
                'tenant.bootstrap.recovered',
            ], true) => 'recovery',
            $item->event_key === 'tenant.domain.updated' => 'domain',
            $item->event_key === 'tenant.status.updated' => 'status',
            $item->event_key === 'tenant.note.added' => 'audit',
            default => '',
        };

        session()->flash(
            'platform_admin_message',
            match ($focusPanel) {
                'subscription' => 'Request tenant dibuka. Panel langganan tenant sudah difokuskan untuk follow-up.',
                'billing' => 'Request tenant dibuka. Panel penagihan tenant sudah difokuskan untuk follow-up invoice dan validasi pembayaran.',
                'profile' => 'Detail tenant dibuka. Panel profil tenant sudah difokuskan untuk follow-up perubahan data inti.',
                'recovery' => 'Detail tenant dibuka. Panel recovery tenant sudah difokuskan untuk tindak lanjut onboarding atau bootstrap.',
                'domain' => 'Detail tenant dibuka. Panel domain tenant sudah difokuskan untuk tindak lanjut akses aplikasi.',
                'status' => 'Detail tenant dibuka. Panel status tenant sudah difokuskan untuk tindak lanjut lifecycle.',
                'audit' => 'Detail tenant dibuka. Panel audit tenant sudah difokuskan untuk melihat catatan dan jejak perubahan.',
                default => 'Detail tenant dibuka dari inbox operasional.',
            }
        );

        return redirect()->to(route('platform.admin.tenants.index', array_filter([
            'tenant' => $item->tenant_id,
            'focus' => $focusPanel !== '' ? $focusPanel : null,
        ])).match ($focusPanel) {
            'subscription' => '#subscription-panel',
            'billing' => '#billing-panel',
            'profile' => '#profile-panel',
            'recovery' => '#recovery-panel',
            'domain' => '#domain-panel',
            'status' => '#status-panel',
            'audit' => '#audit-panel',
            default => '#tenant-detail',
        });
    }

    public function updateFollowUpStatus(int $itemId, string $status): void
    {
        $item = InboxItem::query()->findOrFail($itemId);

        if (! in_array($status, ['open', 'in_progress', 'resolved'], true)) {
            abort(404);
        }

        $item->forceFill([
            'follow_up_status' => $status,
            'follow_up_user_id' => auth()->id(),
            'follow_up_at' => now(),
            'follow_up_note' => $this->defaultFollowUpNote($status),
        ])->save();

        if ($status !== 'open') {
            $this->markAsRead($itemId);
        }

        session()->flash('platform_admin_message', match ($status) {
            'in_progress' => 'Item inbox ditandai sedang ditindaklanjuti.',
            'resolved' => 'Item inbox ditandai selesai ditindaklanjuti.',
            default => 'Item inbox dibuka kembali untuk follow-up.',
        });
    }

    private function filteredInboxQuery(): Builder
    {
        return InboxItem::query()
            ->with([
                'tenant',
                'reads' => fn ($query) => $query->where('user_id', auth()->id()),
                'followUpUser',
            ])
            ->when($this->categoryFilter !== 'all', fn (Builder $query) => $query->where('category', $this->categoryFilter))
            ->when($this->followUpFilter !== 'all', fn (Builder $query) => $query->where('follow_up_status', $this->followUpFilter))
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $inner): void {
                    $inner->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('message', 'like', '%'.$this->search.'%')
                        ->orWhereHas('tenant', function (Builder $tenantQuery): void {
                            $tenantQuery->where('name', 'like', '%'.$this->search.'%')
                                ->orWhere('code', 'like', '%'.$this->search.'%')
                                ->orWhere('owner_email', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->readFilter === 'unread', function (Builder $query): void {
                $query->whereDoesntHave('reads', fn (Builder $readQuery) => $readQuery->where('user_id', auth()->id()));
            })
            ->when($this->readFilter === 'read', function (Builder $query): void {
                $query->whereHas('reads', fn (Builder $readQuery) => $readQuery->where('user_id', auth()->id()));
            })
            ->latest('occurred_at')
            ->latest('id');
    }

    private function unreadCount(string $category = 'all'): int
    {
        return InboxItem::query()
            ->when($category !== 'all', fn (Builder $query) => $query->where('category', $category))
            ->whereDoesntHave('reads', fn (Builder $query) => $query->where('user_id', auth()->id()))
            ->count();
    }

    private function followUpCount(string $status = 'all'): int
    {
        return InboxItem::query()
            ->when($status !== 'all', fn (Builder $query) => $query->where('follow_up_status', $status))
            ->count();
    }

    private function defaultFollowUpNote(string $status): string
    {
        return match ($status) {
            'in_progress' => 'Ditandai sedang ditindaklanjuti dari inbox admin.',
            'resolved' => 'Ditandai selesai ditindaklanjuti dari inbox admin.',
            default => 'Dibuka kembali untuk tindak lanjut tambahan dari inbox admin.',
        };
    }

    public function presentItem(InboxItem $item): array
    {
        $payload = (array) ($item->payload ?? []);

        $meta = [
            'headline' => $item->title,
            'eyebrow' => null,
            'summary' => $item->message,
            'highlights' => [],
            'follow_up_label' => match ($item->follow_up_status) {
                'in_progress' => 'Sedang Ditindaklanjuti',
                'resolved' => 'Selesai',
                default => 'Perlu Follow-up',
            },
        ];

        if ($item->event_key === 'tenant.subscription.upgrade_requested') {
            $requestedPlan = $payload['requested_plan_name']
                ?? $payload['target_plan_name']
                ?? $payload['requested_plan_code']
                ?? $payload['target_plan_code']
                ?? null;

            $currentPlan = $payload['current_plan_name']
                ?? $payload['current_plan_code']
                ?? null;

            $meta['headline'] = 'Request Upgrade';
            $meta['eyebrow'] = 'Dari tenant';
            $meta['summary'] = $item->message !== ''
                ? $item->message
                : 'Tenant mengajukan kenaikan paket dan menunggu tindak lanjut admin platform.';
            $meta['highlights'] = array_values(array_filter([
                $requestedPlan ? 'Target paket: '.$requestedPlan : null,
                $currentPlan ? 'Paket saat ini: '.$currentPlan : null,
                !empty($payload['requested_by_name']) ? 'PIC: '.$payload['requested_by_name'] : null,
                !empty($payload['requested_by_email']) ? $payload['requested_by_email'] : null,
                !empty($payload['notes']) ? 'Catatan: '.$payload['notes'] : null,
            ]));
        }

        if ($item->event_key === 'tenant.subscription.renewal_requested') {
            $requestedTerm = $payload['requested_term_label']
                ?? (!empty($payload['requested_term_months']) ? $payload['requested_term_months'].' bulan' : null);

            $meta['headline'] = 'Request Perpanjangan';
            $meta['eyebrow'] = 'Dari tenant';
            $meta['summary'] = $item->message !== ''
                ? $item->message
                : 'Tenant meminta perpanjangan masa aktif/langganan dan menunggu follow-up admin.';
            $meta['highlights'] = array_values(array_filter([
                $requestedTerm ? 'Durasi: '.$requestedTerm : null,
                !empty($payload['current_plan_name']) ? 'Paket: '.$payload['current_plan_name'] : null,
                !empty($payload['requested_by_name']) ? 'PIC: '.$payload['requested_by_name'] : null,
                !empty($payload['requested_by_email']) ? $payload['requested_by_email'] : null,
                !empty($payload['notes']) ? 'Catatan: '.$payload['notes'] : null,
            ]));
        }

        return $meta;
    }

    public function render()
    {
        /** @var LengthAwarePaginator $items */
        $items = $this->filteredInboxQuery()->paginate(12);
        $items->setCollection(
            $items->getCollection()->map(function (InboxItem $item): array {
                return [
                    'model' => $item,
                    'meta' => $this->presentItem($item),
                ];
            })
        );

        return view('livewire.platform.admin.inbox-manager', [
            'items' => $items,
            'unreadTotal' => $this->unreadCount(),
            'unreadAttention' => $this->unreadCount('attention'),
            'unreadRenewal' => $this->unreadCount('renewal'),
            'unreadAdmin' => $this->unreadCount('admin'),
            'unreadRegistration' => $this->unreadCount('registration'),
            'unreadActivation' => $this->unreadCount('activation'),
            'followUpOpen' => $this->followUpCount('open'),
            'followUpInProgress' => $this->followUpCount('in_progress'),
            'followUpResolved' => $this->followUpCount('resolved'),
            'title' => 'Platform Admin - Inbox',
        ]);
    }
}
