<?php

use App\Http\Controllers\PlatformAdminTenantActionController;
use App\Http\Controllers\PlatformAnnouncementController;
use App\Http\Controllers\PlatformAuthController;
use App\Http\Controllers\PlatformOrderController;
use App\Http\Controllers\PlatformPaymentController;
use App\Http\Controllers\PlatformRegistrationController;
use App\Http\Controllers\PlatformRenewalController;
use App\Http\Controllers\PlatformUpgradeController;
use App\Http\Controllers\TenantSubscriptionCenterController;
use App\Livewire\Platform\Admin\AccountingReportPage;
use App\Livewire\Platform\Admin\AccountingSettingsPage;
use App\Livewire\Platform\Admin\AnnouncementManager;
use App\Livewire\Platform\Admin\InboxManager;
use App\Livewire\Platform\Admin\PaymentValidationManager;
use App\Livewire\Platform\Admin\PlanEditorPage;
use App\Livewire\Platform\Admin\PlanManager;
use App\Livewire\Platform\Admin\PlanMappingPage;
use App\Livewire\Platform\Admin\RegistrationSettingsPage;
use App\Livewire\Platform\Admin\TenantManager;
use Illuminate\Support\Facades\Route;

Route::get('/', [PlatformRegistrationController::class, 'landing'])
    ->name('platform.landing');
Route::get('/help-center', [PlatformRegistrationController::class, 'help'])
    ->name('platform.help');
Route::get('/kontak', [PlatformRegistrationController::class, 'contact'])
    ->name('platform.contact');
Route::get('/blog', [PlatformRegistrationController::class, 'blog'])
    ->name('platform.blog');
Route::get('/daftar', [PlatformRegistrationController::class, 'create'])
    ->name('platform.register.create');
Route::get('/perpanjang', [PlatformRenewalController::class, 'create'])
    ->name('platform.renewal.create');
Route::get('/upgrade', [PlatformUpgradeController::class, 'create'])
    ->name('platform.upgrade.create');
Route::get('/pesanan', [PlatformOrderController::class, 'create'])
    ->name('platform.orders.index');
Route::get('/subscription', TenantSubscriptionCenterController::class)
    ->name('tenant.subscription-center');
Route::post('/daftar', [PlatformRegistrationController::class, 'store'])
    ->name('platform.register.store');
Route::post('/perpanjang', [PlatformRenewalController::class, 'store'])
    ->name('platform.renewal.store');
Route::post('/upgrade', [PlatformUpgradeController::class, 'store'])
    ->name('platform.upgrade.store');
Route::post('/pesanan', [PlatformOrderController::class, 'lookup'])
    ->name('platform.orders.lookup');
Route::get('/daftar/email-owner-status', [PlatformRegistrationController::class, 'ownerEmailStatus'])
    ->name('platform.register.owner-email-status');
Route::get('/daftar/verifikasi/menunggu', [PlatformRegistrationController::class, 'pending'])
    ->name('platform.register.pending');
Route::post('/daftar/verifikasi/kirim-ulang', [PlatformRegistrationController::class, 'resendVerification'])
    ->name('platform.register.resend');
Route::get('/daftar/verifikasi/{token}', [PlatformRegistrationController::class, 'verify'])
    ->name('platform.register.verify');
Route::post('/daftar/draft', [PlatformRegistrationController::class, 'storeDraft'])
    ->name('platform.register.draft.store');
Route::get('/daftar/draft/{token}', [PlatformRegistrationController::class, 'showDraft'])
    ->name('platform.register.draft.show');
Route::get('/daftar/sukses/{tenant}', [PlatformRegistrationController::class, 'success'])
    ->name('platform.register.success');
Route::post('/daftar/sukses/{tenant}/bootstrap', [PlatformRegistrationController::class, 'bootstrap'])
    ->name('platform.register.bootstrap');
Route::get('/daftar/sukses/{tenant}/status', [PlatformRegistrationController::class, 'status'])
    ->name('platform.register.status');
Route::get('/payment/{invoiceNumber}', [PlatformPaymentController::class, 'show'])
    ->name('platform.payment.show');
Route::get('/payment/{invoiceNumber}/pdf', [PlatformPaymentController::class, 'showPdf'])
    ->name('platform.payment.pdf');
Route::post('/payment/{invoiceNumber}/doku', [PlatformPaymentController::class, 'redirectToDoku'])
    ->name('platform.payment.doku');
Route::post('/payment/{invoiceNumber}/proof', [PlatformPaymentController::class, 'submitProof'])
    ->name('platform.payment.proof');
Route::post('/webhooks/doku/checkout', [PlatformPaymentController::class, 'dokuWebhook'])
    ->name('platform.webhooks.doku.checkout');

Route::middleware('guest')->group(function () {
    Route::get('/platform-login', [PlatformAuthController::class, 'create'])
        ->name('platform.login');
    Route::post('/platform-login', [PlatformAuthController::class, 'store'])
        ->name('platform.login.store');
});

Route::middleware(['auth', 'platform.admin'])->group(function () {
    Route::post('/platform-logout', [PlatformAuthController::class, 'destroy'])
        ->name('platform.logout');

    Route::prefix('admin')->name('platform.admin.')->group(function () {
        Route::get('/tenants', TenantManager::class)
            ->name('tenants.index');
        Route::get('/inbox', InboxManager::class)
            ->name('inbox.index');
        Route::get('/payments', PaymentValidationManager::class)
            ->name('payments.index');
        Route::get('/announcements', AnnouncementManager::class)
            ->name('announcements.index');
        Route::get('/plans', PlanManager::class)
            ->name('plans.index');
        Route::get('/settings/registration', RegistrationSettingsPage::class)
            ->name('settings.registration');
        Route::get('/accounting/settings', AccountingSettingsPage::class)
            ->name('accounting.settings');
        Route::get('/accounting/reports', AccountingReportPage::class)
            ->name('accounting.reports');
        Route::get('/plans/{plan}/mapping', PlanMappingPage::class)
            ->name('plans.mapping');
        Route::get('/plans/{plan}/edit', PlanEditorPage::class)
            ->name('plans.edit');
        Route::post('/tenants/{tenant}/provision', [PlatformAdminTenantActionController::class, 'provision'])
            ->name('tenants.provision');
        Route::post('/tenants/{tenant}/bootstrap', [PlatformAdminTenantActionController::class, 'bootstrap'])
            ->name('tenants.bootstrap');
        Route::post('/tenants/{tenant}/profile', [PlatformAdminTenantActionController::class, 'updateProfile'])
            ->name('tenants.profile');
        Route::post('/tenants/{tenant}/renew', [PlatformAdminTenantActionController::class, 'renewSubscription'])
            ->name('tenants.renew');
        Route::post('/tenants/{tenant}/plan', [PlatformAdminTenantActionController::class, 'updatePlan'])
            ->name('tenants.plan');
        Route::post('/tenants/{tenant}/domain', [PlatformAdminTenantActionController::class, 'updateDomain'])
            ->name('tenants.domain');
        Route::post('/tenants/{tenant}/status', [PlatformAdminTenantActionController::class, 'updateStatus'])
            ->name('tenants.status');
        Route::post('/tenants/{tenant}/notes', [PlatformAdminTenantActionController::class, 'addNote'])
            ->name('tenants.notes');
        Route::post('/announcements/{announcement}/dismiss', [PlatformAnnouncementController::class, 'dismiss'])
            ->name('announcements.dismiss');
    });
});
