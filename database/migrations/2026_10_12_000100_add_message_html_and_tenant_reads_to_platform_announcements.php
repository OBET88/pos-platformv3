<?php

use App\Models\Platform\Announcement;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom rich text tersanitasi + pelacakan "sudah ditutup" lintas-DB
 * (user tenant tidak ada di DB platform, jadi identitasnya tenant_code + user id tenant).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('platform_announcements') && ! Schema::hasColumn('platform_announcements', 'message_html')) {
            Schema::table('platform_announcements', function (Blueprint $table): void {
                $table->text('message_html')->nullable()->after('message');
            });

            // Backfill baris lama memakai pesan polos agar konten langsung terbaca.
            Announcement::query()
                ->whereNull('message_html')
                ->orderBy('id')
                ->chunkById(100, function ($announcements): void {
                    foreach ($announcements as $announcement) {
                        Announcement::query()
                            ->whereKey($announcement->getKey())
                            ->update(['message_html' => nl2br(e((string) $announcement->message))]);
                    }
                });
        }

        if (! Schema::hasTable('platform_announcement_user_reads')) {
            Schema::create('platform_announcement_user_reads', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('announcement_id')->constrained('platform_announcements')->cascadeOnDelete();
                $table->string('tenant_code', 64);
                $table->unsignedBigInteger('tenant_user_id');
                $table->string('tenant_user_email')->nullable();
                $table->timestamp('dismissed_at')->nullable();
                $table->timestamps();

                $table->unique(['announcement_id', 'tenant_code', 'tenant_user_id'], 'platform_announcement_read_unique');
                $table->index(['tenant_code', 'tenant_user_id'], 'platform_announcement_read_user_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_announcement_user_reads');

        if (Schema::hasTable('platform_announcements') && Schema::hasColumn('platform_announcements', 'message_html')) {
            Schema::table('platform_announcements', function (Blueprint $table): void {
                $table->dropColumn('message_html');
            });
        }
    }
};
