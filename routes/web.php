
<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\InvestmentController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\UpdateController;
use App\Http\Controllers\DocumentController;
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

    Route::get('/projects/{projectId}', [ProjectController::class, 'show'])->name('projects.show');
    Route::post('/projects/{projectId}/resend-documents', [ProjectController::class, 'resendDocuments'])->name('projects.resend_documents');

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

// Document download route - use where to allow any characters in hash
Route::get('/document/investor/{hash}', [DocumentController::class, 'investor'])
    ->where('hash', '.*')
    ->name('document.investor');

// One-time route to run missing migrations (remove after use)
Route::get('/run-migrations', function () {
    try {
        \Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_11_17_000001_create_document_email_logs_table.php',
            '--database' => 'legacy',
            '--force' => true,
        ]);
        \Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_11_17_000002_create_investor_notifications_table.php',
            '--database' => 'legacy',
            '--force' => true,
        ]);
        \Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_11_17_000003_create_support_tickets_table.php',
            '--database' => 'legacy',
            '--force' => true,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Migrations completed successfully!',
            'output' => \Artisan::output(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
})->name('run.migrations');

// One-time route to create admin account (remove after use)
Route::get('/create-admin-account', function () {
    $email = 'rich@rise-capital.uk';
    $password = 'password123';
    
    // Check if account already exists
    $existingAccount = \App\Models\Account::where('email', $email)->first();
    if ($existingAccount) {
        return response()->json([
            'success' => true,
            'message' => 'Account already exists',
            'email' => $email,
            'account_id' => $existingAccount->id,
            'type_id' => $existingAccount->type_id,
            'login_url' => url('/investor/login'),
            'note' => 'If you need to reset the password or change type, use the admin panel.',
        ]);
    }
    
    // Split name
    $nameParts = explode(' ', 'Rich Copestake', 2);
    $firstName = $nameParts[0];
    $lastName = $nameParts[1] ?? '';
    
    // Check if person already exists, if not create one
    $person = \App\Models\Person::where('email', $email)
        ->where('first_name', $firstName)
        ->where('last_name', $lastName)
        ->first();
    
    if (!$person) {
        // Create person record with a unique telephone_number to avoid constraint violation
        $person = \App\Models\Person::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'telephone_number' => 'admin-' . time(), // Unique value to avoid constraint violation
        ]);
    }
    
    // Create account with GUARDIAN type (type_id = 2) for global admin access
    $account = \App\Models\Account::create([
        'email' => $email,
        'password' => bcrypt($password),
        'type_id' => 2, // GUARDIAN = System Admin
        'person_id' => $person->id,
        'deleted' => 0,
    ]);
    
    return response()->json([
        'success' => true,
        'message' => 'Admin account created successfully!',
        'email' => $email,
        'password' => $password,
        'type' => 'GUARDIAN (Global Admin)',
        'account_id' => $account->id,
        'person_id' => $person->id,
        'login_url' => url('/investor/login'),
    ]);
})->name('create.admin.account');

require __DIR__.'/auth.php';
