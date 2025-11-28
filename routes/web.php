
<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\InvestmentController;
use App\Http\Controllers\Admin\UpdateController;
use App\Http\Controllers\Investor\InvestorDashboardController;
use App\Http\Controllers\Investor\InvestorDocumentController;
use App\Http\Controllers\Investor\InvestorNotificationController;
use App\Http\Controllers\Investor\InvestorSupportController;
use App\Http\Controllers\PublicProjectController;
use App\Http\Controllers\UpdateShowController;

use App\Http\Controllers\InvestorAuthController;
use App\Models\Account;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Volt;


Route::get('/', function () {
    $highlightedProjects = \App\Models\Project::with('property')
        ->whereIn('status', [
            \App\Models\Project::STATUS_PENDING_EQUITY,
            \App\Models\Project::STATUS_PENDING_PURCHASE,
            \App\Models\Project::STATUS_PENDING_CONSTRUCTION,
            \App\Models\Project::STATUS_UNDER_CONSTRUCTION,
        ])->orderByDesc('launched_on')->limit(4)->get();

    return view('home', compact('highlightedProjects'));
})->name('home');

Route::prefix('admin')->name('admin.')->middleware('auth:investor')->group(function () {
    Route::get('/dashboard', [InvestmentController::class, 'index'])->name('investments.index');
    Route::get('/investments', [InvestmentController::class, 'index'])->name('investments.index');
    Route::get('/investments/export', [InvestmentController::class, 'export'])->name('investments.export');

    Route::get('/updates', [UpdateController::class, 'index'])->name('updates.index');
    Route::post('/updates', [UpdateController::class, 'store'])->name('updates.store');
    Route::get('/updates/{id}', [UpdateController::class, 'show'])->name('updates.show');
    Route::get('/updates/{id}/edit', [UpdateController::class, 'edit'])->name('updates.edit');
    Route::put('/updates/{id}', [UpdateController::class, 'update'])->name('updates.update');
    Route::delete('/updates/{id}', [UpdateController::class, 'destroy'])->name('updates.destroy');

    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/{id}', [AccountController::class, 'show'])->name('accounts.show');

    Route::post('/accounts/{id}/type', [AccountController::class, 'updateType'])->name('accounts.updateType');
    Route::post('/accounts/{id}/password', [AccountController::class, 'updatePassword'])->name('accounts.updatePassword');

    Route::post('accounts/{id}/masquerade', function ($id) {
        $target = Account::on('legacy')->findOrFail($id);

    // Store masquerade state
    session()->put('masquerading_as', $target->id);
    // Use a signed cookie for admin ID
    cookie()->queue(cookie('masquerading_from_admin', Auth::id(), 10, null, null, false, true, false, 'strict'));
    Auth::guard('investor')->login($target);

    return redirect()->route('investor.dashboard');
    })->name('accounts.masquerade');

    Route::post('/account/stop-masquerade', function () {
    session()->forget('masquerading_as');
    Auth::guard('investor')->logout();
    return redirect()->route('investor.login')->with('status', 'Logged out from masquerade');
    })->name('investor.stopMasquerade');

    // Bulk email
    Route::get('/updates/{id}/bulk-email', [UpdateController::class, 'bulkEmailPreflight'])->name('updates.bulk_email_preflight');
    Route::post('/updates/{id}/bulk-email', [UpdateController::class, 'sendBulkEmails'])->name('updates.bulk_email');

    // Selective Email
    Route::post('/updates/{update}/selective-email', [UpdateController::class, 'sendSelectiveEmails'])->name('updates.selective_email');

});

Route::prefix('investor')->name('investor.')->group(function () {
    Route::get('login', [InvestorAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [InvestorAuthController::class, 'login'])->name('login.post');
    Route::post('logout', [InvestorAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:investor')->group(function () {
        Route::get('/dashboard', InvestorDashboardController::class)->name('dashboard');
        Route::post('/projects/{project}/documents/email', [InvestorDocumentController::class, 'email'])->name('documents.email');
        Route::post('/notifications/{notification}/read', [InvestorNotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('/notifications/mark-all-read', [InvestorNotificationController::class, 'markAllRead'])->name('notifications.read_all');
        Route::post('/projects/{project}/support', [InvestorSupportController::class, 'store'])->name('support.store');
    });
});

Route::get('/updates/{id}', UpdateShowController::class)->name('updates.show');

Route::get('/projects', [PublicProjectController::class, 'index'])->name('public.projects.index');
Route::get('/projects/{project}', [PublicProjectController::class, 'show'])->name('public.projects.show');

require __DIR__.'/auth.php';
