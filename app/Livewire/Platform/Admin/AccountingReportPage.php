<?php

namespace App\Livewire\Platform\Admin;

use App\Models\Platform\AccountingJournal;
use App\Models\Platform\AccountingPostingQueue;
use App\Models\Platform\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.platform-admin')]
class AccountingReportPage extends Component
{
    use WithPagination;

    #[Url(as: 'tenant', except: '')]
    public string $selectedTenantId = '';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function mount(): void
    {
        $firstTenant = Tenant::query()->latest('id')->first();

        if ($firstTenant && $this->selectedTenantId === '') {
            $this->selectedTenantId = (string) $firstTenant->getKey();
        }
    }

    public function updatingSelectedTenantId(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->statusFilter = 'all';
        $this->search = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function selectedTenant(): ?Tenant
    {
        if ($this->selectedTenantId === '') {
            return null;
        }

        return Tenant::query()->find((int) $this->selectedTenantId);
    }

    public function render()
    {
        $tenants = Tenant::query()->latest('id')->get();
        $selectedTenant = $this->selectedTenant();

        $baseQuery = AccountingJournal::query()
            ->with(['tenant', 'lines.account'])
            ->when($selectedTenant, fn (Builder $query) => $query->where('tenant_id', $selectedTenant->id))
            ->when($this->statusFilter !== 'all', fn (Builder $query) => $query->where('posting_status', $this->statusFilter))
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $inner): void {
                    $inner->where('journal_no', 'like', '%'.$this->search.'%')
                        ->orWhere('reference_no', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%')
                        ->orWhere('event_key', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->dateFrom !== '', fn (Builder $query) => $query->whereDate('journal_date', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $query) => $query->whereDate('journal_date', '<=', $this->dateTo));

        $summaryQuery = clone $baseQuery;
        $journals = $baseQuery->latest('journal_date')->latest('id')->paginate(10);

        $summaryJournals = $summaryQuery->get();
        $totalDebit = $summaryJournals->sum(fn (AccountingJournal $journal) => $journal->lines->sum(fn ($line) => (float) $line->debit));
        $totalCredit = $summaryJournals->sum(fn (AccountingJournal $journal) => $journal->lines->sum(fn ($line) => (float) $line->credit));
        $postedCount = $summaryJournals->where('posting_status', 'posted')->count();
        $pendingMappingCount = $summaryJournals->where('posting_status', 'pending_mapping')->count();
        $failedCount = $summaryJournals->where('posting_status', 'failed')->count();
        $queueCount = AccountingPostingQueue::query()
            ->when($selectedTenant, fn (Builder $query) => $query->where('tenant_id', $selectedTenant->id))
            ->when($this->statusFilter !== 'all', fn (Builder $query) => $query->where('status', $this->statusFilter))
            ->count();

        return view('livewire.platform.admin.accounting-report-page', [
            'tenants' => $tenants,
            'selectedTenant' => $selectedTenant,
            'journals' => $journals,
            'stats' => [
                'total_journals' => $summaryJournals->count(),
                'posted' => $postedCount,
                'pending_mapping' => $pendingMappingCount,
                'failed' => $failedCount,
                'queue' => $queueCount,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ],
            'title' => 'Platform Admin - Laporan Akuntansi',
        ]);
    }
}
