<?php

namespace App\Livewire\Platform\Admin;

use App\Models\Platform\Announcement;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.platform-admin')]
class AnnouncementManager extends Component
{
    use WithPagination;

    public array $form = [
        'title' => '',
        'message' => '',
        'tone' => 'info',
        'is_active' => true,
        'show_as_modal' => true,
        'starts_at' => '',
        'ends_at' => '',
    ];

    public function createAnnouncement(): void
    {
        $validated = $this->validate([
            'form.title' => ['required', 'string', 'max:255'],
            'form.message' => ['required', 'string', 'max:5000'],
            'form.tone' => ['required', 'in:info,maintenance,security,feature'],
            'form.is_active' => ['boolean'],
            'form.show_as_modal' => ['boolean'],
            'form.starts_at' => ['nullable', 'date'],
            'form.ends_at' => ['nullable', 'date', 'after_or_equal:form.starts_at'],
        ])['form'];

        Announcement::query()->create([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'tone' => $validated['tone'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'show_as_modal' => (bool) ($validated['show_as_modal'] ?? true),
            'starts_at' => filled($validated['starts_at'] ?? null) ? $validated['starts_at'] : null,
            'ends_at' => filled($validated['ends_at'] ?? null) ? $validated['ends_at'] : null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->reset('form');
        $this->form = [
            'title' => '',
            'message' => '',
            'tone' => 'info',
            'is_active' => true,
            'show_as_modal' => true,
            'starts_at' => '',
            'ends_at' => '',
        ];

        session()->flash('platform_admin_message', 'Pengumuman baru berhasil dibuat.');
    }

    public function toggleActive(int $announcementId): void
    {
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

    public function render()
    {
        return view('livewire.platform.admin.announcement-manager', [
            'announcements' => Announcement::query()
                ->with(['creator'])
                ->latest('id')
                ->paginate(10),
            'title' => 'Platform Admin - Pengumuman',
        ]);
    }
}
