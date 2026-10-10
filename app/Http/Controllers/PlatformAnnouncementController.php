<?php

namespace App\Http\Controllers;

use App\Models\Platform\Announcement;
use App\Models\Platform\AnnouncementDismissal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlatformAnnouncementController extends Controller
{
    public function dismiss(Request $request, Announcement $announcement): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user && $user->hasPlatformPermission('view_announcements'), 403);

        AnnouncementDismissal::query()->updateOrCreate(
            [
                'announcement_id' => $announcement->getKey(),
                'user_id' => $request->user()->getKey(),
            ],
            [
                'dismissed_at' => now(),
            ]
        );

        return back()->with('platform_admin_message', 'Pengumuman ditutup. Anda masih bisa melihatnya lagi di halaman Pengumuman.');
    }
}
