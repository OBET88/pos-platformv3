<?php

namespace App\Livewire\Platform\Admin;

use App\Livewire\Platform\Admin\Concerns\AuthorizesPlatformAdmin;
use App\Models\Platform\Announcement;
use App\Support\HtmlSanitizer;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.platform-admin')]
class AnnouncementManager extends Component
{
    use AuthorizesPlatformAdmin;
    use WithPagination;

    public ?int $editingId = null;

    public bool $confirmingDelete = false;

    public ?int $deleteId = null;

    public array $form = [
        'title' => '',
        'message' => '',
        'tone' => 'info',
        'is_active' => true,
        'show_as_modal' => true,
        'starts_at' => '',
        'ends_at' => '',
    ];

    public function mount(): void
    {
        $this->authorizePlatformPermission('view_announcements');
    }

    private function rules(): array
    {
        return [
            'form.title' => ['required', 'string', 'max:255'],
            'form.message' => ['required', 'string', 'max:20000'],
            'form.tone' => ['required', 'in:info,maintenance,security,feature'],
            'form.is_active' => ['boolean'],
            'form.show_as_modal' => ['boolean'],
            'form.starts_at' => ['nullable', 'date'],
            'form.ends_at' => ['nullable', 'date', 'after_or_equal:form.starts_at'],
        ];
    }

    public function save(): void
    {
        $this->authorizePlatformPermission('manage_announcements');

        $validated = $this->validate($this->rules())['form'];

        $payload = [
            'title' => $validated['title'],
            'message' => $validated['message'],
            'message_html' => HtmlSanitizer::clean($validated['message']),
            'tone' => $validated['tone'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'show_as_modal' => (bool) ($validated['show_as_modal'] ?? true),
            'starts_at' => filled($validated['starts_at'] ?? null) ? $validated['starts_at'] : null,
            'ends_at' => filled($validated['ends_at'] ?? null) ? $validated['ends_at'] : null,
            'updated_by' => auth()->id(),
        ];

        if ($this->editingId) {
            Announcement::query()->whereKey($this->editingId)->update($payload);

            session()->flash('platform_admin_message', 'Pengumuman berhasil diperbarui.');
        } else {
            Announcement::query()->create($payload + ['created_by' => auth()->id()]);

            session()->flash('platform_admin_message', 'Pengumuman baru berhasil dibuat.');
        }

        $this->resetForm();
    }

    public function createAnnouncement(): void
    {
        // Alias kompatibilitas untuk pemanggilan lama.
        $this->save();
    }

    public function edit(int $announcementId): void
    {
        $this->authorizePlatformPermission('manage_announcements');

        $announcement = Announcement::query()->findOrFail($announcementId);

        $this->editingId = $announcement->id;
        $this->form = [
            'title' => $announcement->title,
            'message' => (string) ($announcement->message_html ?: $announcement->message),
            'tone' => $announcement->tone,
            'is_active' => (bool) $announcement->is_active,
            'show_as_modal' => (bool) $announcement->show_as_modal,
            'starts_at' => optional($announcement->starts_at)->format('Y-m-d\TH:i') ?? '',
            'ends_at' => optional($announcement->ends_at)->format('Y-m-d\TH:i') ?? '',
        ];

        $this->dispatch('announcement-editor-sync', html: $this->form['message']);
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function toggleActive(int $announcementId): void
    {
        $this->authorizePlatformPermission('manage_announcements');

        $announcement = Announcement::query()->findOrFail($announcementId);

        $announcement->forceFill([
            'is_active' => ! $announcement->is_active,
            'updated_by' => auth()->id(),
        ])->save();

        session()->flash(
            'platform_admin_message',
            'Pengumuman '.$announcement->title.' '.($announcement->is_active ? 'diaktifkan.' : 'dinonaktifkan.')
        );
    }

    /**
     * Tampilkan ulang: hapus semua catatan baca user tenant (dan dismissal admin)
     * agar pengumuman muncul lagi ke semua orang.
     */
    public function resetViews(int $announcementId): void
    {
        $this->authorizePlatformPermission('manage_announcements');

        $announcement = Announcement::query()->findOrFail($announcementId);

        $announcement->tenantReads()->delete();
        $announcement->dismissals()->delete();

        session()->flash('platform_admin_message', 'Pengumuman '.$announcement->title.' akan ditampilkan ulang ke semua user.');
    }

    public function confirmDelete(int $announcementId): void
    {
        $this->authorizePlatformPermission('manage_announcements');

        $this->deleteId = $announcementId;
        $this->confirmingDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    public function deleteAnnouncement(): void
    {
        $this->authorizePlatformPermission('manage_announcements');

        if ($this->deleteId) {
            Announcement::query()->whereKey($this->deleteId)->delete();
            session()->flash('platform_admin_message', 'Pengumuman berhasil dihapus.');
        }

        $this->cancelDelete();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->form = [
            'title' => '',
            'message' => '',
            'tone' => 'info',
            'is_active' => true,
            'show_as_modal' => true,
            'starts_at' => '',
            'ends_at' => '',
        ];
    }

    public function render()
    {
        return view('livewire.platform.admin.announcement-manager', [
            'announcements' => Announcement::query()
                ->with(['creator'])
                ->withCount('tenantReads')
                ->latest('id')
                ->paginate(10),
            'canManage' => $this->platformUserCan('manage_announcements'),
            'title' => 'Platform Admin - Pengumuman',
        ]);
    }
}
