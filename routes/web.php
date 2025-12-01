
<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AccountDocumentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ChangelogController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\InvestmentController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectDocumentController;
use App\Http\Controllers\Admin\SystemStatusController;
use App\Http\Controllers\Admin\UpdateController;
use App\Http\Controllers\Admin\UpdateImageController;
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

Route::prefix('admin')->name('admin.')->middleware(['auth:investor', 'admin.investor'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/investments', [InvestmentController::class, 'index'])->name('investments.index');
    Route::get('/investments/create', [InvestmentController::class, 'create'])->name('investments.create');
    Route::post('/investments', [InvestmentController::class, 'store'])->name('investments.store');
    Route::get('/investments/{investment}/edit', [InvestmentController::class, 'edit'])->name('investments.edit');
    Route::put('/investments/{investment}', [InvestmentController::class, 'update'])->name('investments.update');
    Route::delete('/investments/{investment}', [InvestmentController::class, 'destroy'])->name('investments.destroy');
    Route::get('/investments/export', [InvestmentController::class, 'export'])->name('investments.export');
    Route::get('/investments/search-accounts', [InvestmentController::class, 'searchAccounts'])->name('investments.search-accounts');

    Route::get('/updates', [UpdateController::class, 'index'])->name('updates.index');
    Route::post('/updates', [UpdateController::class, 'store'])->name('updates.store');
    Route::get('/updates/{id}', [UpdateController::class, 'show'])->name('updates.show');
    Route::get('/updates/{id}/edit', [UpdateController::class, 'edit'])->name('updates.edit');
    Route::put('/updates/{id}', [UpdateController::class, 'update'])->name('updates.update');
    Route::delete('/updates/{id}', [UpdateController::class, 'destroy'])->name('updates.destroy');
    
    // Update images
    Route::delete('/update-images/{imageId}', [UpdateImageController::class, 'delete'])->name('update-images.delete');
    Route::post('/update-images/reorder', [UpdateImageController::class, 'reorder'])->name('update-images.reorder');
    Route::put('/update-images/{imageId}/description', [UpdateImageController::class, 'updateDescription'])->name('update-images.update-description');
    
    // System Status
    Route::get('/system-status', [SystemStatusController::class, 'index'])->name('system-status.index');
    Route::get('/system-status/create', [SystemStatusController::class, 'create'])->name('system-status.create');
    Route::post('/system-status', [SystemStatusController::class, 'store'])->name('system-status.store');
    Route::get('/system-status/{id}/edit', [SystemStatusController::class, 'edit'])->name('system-status.edit');
    Route::put('/system-status/{id}', [SystemStatusController::class, 'update'])->name('system-status.update');
    Route::delete('/system-status/{id}', [SystemStatusController::class, 'destroy'])->name('system-status.destroy');
    Route::post('/system-status/{id}/toggle', [SystemStatusController::class, 'toggle'])->name('system-status.toggle');
    Route::post('/system-status/{id}/add-update', [SystemStatusController::class, 'addUpdate'])->name('system-status.add-update');
    Route::post('/system-status-updates/{updateId}/mark-fixed', [SystemStatusController::class, 'markFixed'])->name('system-status-updates.mark-fixed');
    
    // Migration routes (accessible from admin)
    Route::get('/run-system-status-updates-migration', function () {
        try {
            $filePath = database_path('migrations_sql/008_create_system_status_updates.sql');
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'File not found: ' . $filePath,
                ], 404);
            }
            
            $sql = file_get_contents($filePath);
            
            // Check if table already exists
            if (\Illuminate\Support\Facades\Schema::connection('legacy')->hasTable('system_status_updates')) {
                return response()->json([
                    'success' => true,
                    'message' => 'System status updates table already exists!',
                    'statements_executed' => 0,
                    'errors' => [],
                    'note' => 'The table was already present in the database.',
                ], 200, [], JSON_PRETTY_PRINT);
            }

            // Execute SQL directly
            \DB::connection('legacy')->unprepared($sql);

            // Verify table creation
            if (\Illuminate\Support\Facades\Schema::connection('legacy')->hasTable('system_status_updates')) {
                return response()->json([
                    'success' => true,
                    'message' => 'System status updates table created successfully!',
                    'statements_executed' => 1,
                    'errors' => [],
                    'note' => 'The table has been created. You can now use the system status updates feature.',
                ], 200, [], JSON_PRETTY_PRINT);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'SQL executed but table was not created.',
                    'statements_executed' => 1,
                    'errors' => ['Table verification failed'],
                ], 500, [], JSON_PRETTY_PRINT);
            }
        } catch (\Exception $e) {
            // Check if error is because table already exists
            if (str_contains($e->getMessage(), 'already exists') || 
                str_contains($e->getMessage(), 'Duplicate') ||
                str_contains($e->getMessage(), 'Table \'jvsys.system_status_updates\' already exists')) {
                return response()->json([
                    'success' => true,
                    'message' => 'System status updates table already exists!',
                    'statements_executed' => 0,
                    'errors' => [],
                    'note' => 'The table was already present in the database.',
                ], 200, [], JSON_PRETTY_PRINT);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating system status updates table.',
                'statements_executed' => 0,
                'errors' => [$e->getMessage()],
            ], 500, [], JSON_PRETTY_PRINT);
        }
    })->name('admin.run-system-status-updates-migration');
    
    Route::get('/run-email-history-migration', function () {
        try {
            $filePath = database_path('migrations_sql/009_create_email_history_table.sql');
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'File not found: ' . $filePath,
                ], 404);
            }
            
            $sql = file_get_contents($filePath);
            
            // Check if table already exists
            if (\Illuminate\Support\Facades\Schema::connection('legacy')->hasTable('email_history')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email history table already exists!',
                    'statements_executed' => 0,
                    'errors' => [],
                    'note' => 'The table was already present in the database.',
                ], 200, [], JSON_PRETTY_PRINT);
            }

            // Execute SQL directly
            \DB::connection('legacy')->unprepared($sql);

            // Verify table creation
            if (\Illuminate\Support\Facades\Schema::connection('legacy')->hasTable('email_history')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email history table created successfully!',
                    'statements_executed' => 1,
                    'errors' => [],
                    'note' => 'The table has been created. You can now use the email history feature.',
                ], 200, [], JSON_PRETTY_PRINT);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'SQL executed but table was not created.',
                    'statements_executed' => 1,
                    'errors' => ['Table verification failed'],
                ], 500, [], JSON_PRETTY_PRINT);
            }
        } catch (\Exception $e) {
            // Check if error is because table already exists
            if (str_contains($e->getMessage(), 'already exists') || 
                str_contains($e->getMessage(), 'Duplicate') ||
                str_contains($e->getMessage(), 'Table \'jvsys.email_history\' already exists')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email history table already exists!',
                    'statements_executed' => 0,
                    'errors' => [],
                    'note' => 'The table was already present in the database.',
                ], 200, [], JSON_PRETTY_PRINT);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating email history table.',
                'statements_executed' => 0,
                'errors' => [$e->getMessage()],
            ], 500, [], JSON_PRETTY_PRINT);
        }
    })->name('admin.run-email-history-migration');
    
    Route::get('/run-account-shares-migration', function () {
        try {
            $filePath = database_path('migrations_sql/007_create_account_shares.sql');
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'File not found: ' . $filePath,
                ], 404);
            }
            
            $sql = file_get_contents($filePath);
            
            // Check if table already exists
            if (\Illuminate\Support\Facades\Schema::connection('legacy')->hasTable('account_shares')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account shares table already exists!',
                    'statements_executed' => 0,
                    'errors' => [],
                    'note' => 'The table was already present in the database.',
                ], 200, [], JSON_PRETTY_PRINT);
            }

            // Execute SQL directly
            \DB::connection('legacy')->unprepared($sql);

            // Verify table creation
            if (\Illuminate\Support\Facades\Schema::connection('legacy')->hasTable('account_shares')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account shares table created successfully!',
                    'statements_executed' => 1,
                    'errors' => [],
                    'note' => 'The table has been created. You can now use the account sharing feature.',
                ], 200, [], JSON_PRETTY_PRINT);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'SQL executed but table was not created.',
                    'statements_executed' => 1,
                    'errors' => ['Table verification failed'],
                ], 500, [], JSON_PRETTY_PRINT);
            }
        } catch (\Exception $e) {
            // Check if error is because table already exists
            if (str_contains($e->getMessage(), 'already exists') || 
                str_contains($e->getMessage(), 'Duplicate') ||
                str_contains($e->getMessage(), 'Table \'jvsys.account_shares\' already exists')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account shares table already exists!',
                    'statements_executed' => 0,
                    'errors' => [],
                    'note' => 'The table was already present in the database.',
                ], 200, [], JSON_PRETTY_PRINT);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating account shares table.',
                'statements_executed' => 0,
                'errors' => [$e->getMessage()],
            ], 500, [], JSON_PRETTY_PRINT);
        }
    })->name('admin.run-account-shares-migration');

    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::get('/accounts/{id}', [AccountController::class, 'show'])->name('accounts.show');

    Route::post('/accounts/{id}/type', [AccountController::class, 'updateType'])->name('accounts.updateType');
    Route::post('/accounts/{id}/password', [AccountController::class, 'updatePassword'])->name('accounts.updatePassword');
        Route::put('/accounts/{id}', [AccountController::class, 'update'])->name('accounts.update');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{projectId}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/projects/{projectId}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{projectId}', [ProjectController::class, 'update'])->name('projects.update');
    Route::post('/projects/{projectId}/resend-documents', [ProjectController::class, 'resendDocuments'])->name('projects.resend_documents');
    
    // Project documents
    Route::post('/projects/{projectId}/documents', [ProjectDocumentController::class, 'store'])->name('projects.documents.store');
    Route::delete('/project-documents/{documentId}', [ProjectDocumentController::class, 'destroy'])->name('project-documents.destroy');
    Route::put('/project-documents/{documentId}/visibility', [ProjectDocumentController::class, 'updateVisibility'])->name('project-documents.update-visibility');
    
    // Account documents
    Route::get('/accounts/{accountId}/documents', [AccountDocumentController::class, 'index'])->name('accounts.documents.index');
    Route::post('/accounts/{accountId}/documents', [AccountDocumentController::class, 'store'])->name('accounts.documents.store');
    Route::delete('/accounts/{accountId}/documents/{documentId}', [AccountDocumentController::class, 'destroy'])->name('accounts.documents.destroy');
    Route::delete('/account-documents/{documentId}', [AccountDocumentController::class, 'destroy'])->name('account-documents.destroy');
    
    // Document tracing for a specific project
    Route::get('/projects/{projectId}/trace-documents', function ($projectId) {
        $project = \App\Models\Project::where('project_id', $projectId)->firstOrFail();
        $documents = \App\Models\ProjectInvestorDocument::where('proposal_id', $project->id)->get();
        
        $results = [
            'project' => [
                'id' => $project->id,
                'project_id' => $project->project_id,
                'name' => $project->name,
            ],
            'documents' => [],
            'file_locations' => [],
        ];
        
        foreach ($documents as $document) {
            $docInfo = [
                'id' => $document->id,
                'name' => $document->name,
                'hash' => $document->hash,
                'proposal_id' => $document->proposal_id,
                'created_on' => $document->created_on?->format('Y-m-d H:i:s'),
            ];
            
            // Check all possible file locations
            $possiblePaths = [
                base_path('../jvsystem/App/Cache/Docs/Investor/' . $document->proposal_id . '/' . $document->hash . '.pdf'),
                dirname(base_path()) . '/jvsystem/App/Cache/Docs/Investor/' . $document->proposal_id . '/' . $document->hash . '.pdf',
                '/home/forge/jvsystem/App/Cache/Docs/Investor/' . $document->proposal_id . '/' . $document->hash . '.pdf',
                '/home/forge/beta.jaevee.co.uk/App/Cache/Docs/Investor/' . $document->proposal_id . '/' . $document->hash . '.pdf',
                '/var/www/jvsystem/App/Cache/Docs/Investor/' . $document->proposal_id . '/' . $document->hash . '.pdf',
                '/home/betajaeveecouk/beta.jaevee.co.uk/App/Cache/Docs/Investor/' . $document->proposal_id . '/' . $document->hash . '.pdf',
                storage_path('app/documents/investor/' . $document->proposal_id . '/' . $document->hash . '.pdf'),
            ];
            
            $fileChecks = [];
            foreach ($possiblePaths as $path) {
                $fileChecks[] = [
                    'path' => $path,
                    'exists' => file_exists($path),
                    'readable' => file_exists($path) && is_readable($path),
                    'size' => file_exists($path) ? filesize($path) : null,
                ];
            }
            
            $docInfo['file_checks'] = $fileChecks;
            $docInfo['found'] = collect($fileChecks)->contains('exists', true);
            
            $results['documents'][] = $docInfo;
        }
        
        // Check if the base directory exists
        $baseDirs = [
            base_path('../jvsystem/App/Cache/Docs/Investor'),
            dirname(base_path()) . '/jvsystem/App/Cache/Docs/Investor',
            '/home/forge/jvsystem/App/Cache/Docs/Investor',
            '/home/forge/beta.jaevee.co.uk/App/Cache/Docs/Investor',
            '/var/www/jvsystem/App/Cache/Docs/Investor',
            '/home/betajaeveecouk/beta.jaevee.co.uk/App/Cache/Docs/Investor',
        ];
        
        foreach ($baseDirs as $baseDir) {
            $proposalDir = $baseDir . '/' . $project->id;
            $results['file_locations'][] = [
                'base_dir' => $baseDir,
                'base_exists' => is_dir($baseDir),
                'proposal_dir' => $proposalDir,
                'proposal_dir_exists' => is_dir($proposalDir),
                'files_in_proposal_dir' => is_dir($proposalDir) ? array_map('basename', glob($proposalDir . '/*.pdf')) : [],
            ];
        }
        
        return response()->json($results, 200, [], JSON_PRETTY_PRINT);
    })->name('projects.trace_documents');

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

    // Changelog
    Route::get('/changelog', [ChangelogController::class, 'index'])->name('changelog.index');
    Route::get('/changelog/create', [ChangelogController::class, 'create'])->name('changelog.create');
    Route::post('/changelog', [ChangelogController::class, 'store'])->name('changelog.store');

    // Email templates
    Route::get('/email-templates', [EmailTemplateController::class, 'index'])->name('email-templates.index');
    Route::get('/email-templates/{id}/edit', [EmailTemplateController::class, 'edit'])->name('email-templates.edit');
    Route::put('/email-templates/{id}', [EmailTemplateController::class, 'update'])->name('email-templates.update');

});

// Alias for 'login' route (for middleware redirects)
Route::get('login', function () {
    return redirect()->route('investor.login');
})->name('login');

Route::prefix('investor')->name('investor.')->group(function () {
    Route::get('login', [InvestorAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [InvestorAuthController::class, 'login'])->name('login.post');
    Route::post('logout', [InvestorAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:investor')->group(function () {
        Route::get('/dashboard', InvestorDashboardController::class)->name('dashboard');
            Route::put('/profile', [\App\Http\Controllers\Investor\InvestorProfileController::class, 'update'])->name('profile.update');
            Route::post('/projects/{project}/documents/email', [InvestorDocumentController::class, 'email'])->name('documents.email');
            Route::post('/notifications/{notification}/read', [InvestorNotificationController::class, 'markRead'])->name('notifications.read');
            Route::post('/notifications/mark-all-read', [InvestorNotificationController::class, 'markAllRead'])->name('notifications.read_all');
            Route::get('/support/tickets', [InvestorSupportController::class, 'index'])->name('support.index');
            Route::get('/support/tickets/{ticketId}', [InvestorSupportController::class, 'show'])->name('support.show');
            Route::post('/support/tickets', [InvestorSupportController::class, 'store'])->name('support.store');
            Route::post('/support/tickets/{ticketId}/reply', [InvestorSupportController::class, 'reply'])->name('support.reply');
            
            // Account sharing
            Route::get('/account-shares', [\App\Http\Controllers\Investor\AccountShareController::class, 'index'])->name('account-shares.index');
            Route::post('/account-shares', [\App\Http\Controllers\Investor\AccountShareController::class, 'store'])->name('account-shares.store');
            Route::delete('/account-shares/{shareId}', [\App\Http\Controllers\Investor\AccountShareController::class, 'destroy'])->name('account-shares.destroy');
            Route::post('/account-shares/{shareId}/remove', [\App\Http\Controllers\Investor\AccountShareController::class, 'removeSharedAccess'])->name('account-shares.remove');
    });
});

Route::get('/updates/{id}', UpdateShowController::class)->name('updates.show');

Route::get('/projects', [PublicProjectController::class, 'index'])->name('public.projects.index');
Route::get('/projects/{project}', [PublicProjectController::class, 'show'])->name('public.projects.show');

// Document download route - use where to allow any characters in hash
Route::get('/document/investor/{hash}', [DocumentController::class, 'investor'])
    ->where('hash', '.*')
    ->name('document.investor');

// Test route to check if legacy system is accessible
Route::get('/admin/test-legacy-proxy/{hash}', function ($hash) {
    $legacyUrl = config('app.legacy_system_url', 'https://beta.jaevee.co.uk');
    $proxyUrl = $legacyUrl . '/document/investor/' . $hash;
    
    try {
        $response = \Illuminate\Support\Facades\Http::timeout(15)
            ->withoutVerifying()
            ->withOptions(['allow_redirects' => true])
            ->get($proxyUrl);
        
        return response()->json([
            'legacy_url' => $legacyUrl,
            'proxy_url' => $proxyUrl,
            'status' => $response->status(),
            'successful' => $response->successful(),
            'headers' => $response->headers(),
            'body_size' => strlen($response->body()),
            'body_preview' => substr($response->body(), 0, 500),
            'is_pdf' => str_starts_with($response->body(), '%PDF'),
        ], 200, [], JSON_PRETTY_PRINT);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'error_type' => get_class($e),
            'legacy_url' => $legacyUrl,
            'proxy_url' => $proxyUrl,
        ], 500, [], JSON_PRETTY_PRINT);
    }
})->middleware('auth:investor')->name('admin.test.legacy.proxy');

// Route to find where documents are actually stored
Route::get('/admin/find-docs-directory', function () {
    $results = [
        'current_base_path' => base_path(),
        'current_dir' => dirname(base_path()),
        'realpath_base' => realpath(base_path()),
        'realpath_current_dir' => realpath(dirname(base_path())),
        'checked_dirs' => [],
        'found_dirs' => [],
        'symlinks' => [],
        'proposal_dirs_found' => [],
    ];
    
    // Check for symlinks
    $possibleSymlinks = [
        base_path('../jvsystem'),
        dirname(base_path()) . '/jvsystem',
        '/home/forge/jvsystem',
    ];
    
    foreach ($possibleSymlinks as $link) {
        if (file_exists($link)) {
            $realpath = realpath($link);
            $isLink = is_link($link);
            $results['symlinks'][] = [
                'path' => $link,
                'realpath' => $realpath,
                'is_symlink' => $isLink,
                'exists' => file_exists($link),
                'is_dir' => is_dir($link),
            ];
            
            if ($realpath && is_dir($realpath)) {
                $docsPath = $realpath . '/App/Cache/Docs/Investor';
                if (is_dir($docsPath)) {
                    $results['found_dirs'][] = [
                        'path' => $docsPath,
                        'source' => 'symlink_resolution',
                        'readable' => is_readable($docsPath),
                    ];
                }
            }
        }
    }
    
    // Check specific proposal_id directory (2436) in various locations
    $proposalId = 2436;
    $testPaths = [
        base_path('../jvsystem/App/Cache/Docs/Investor/' . $proposalId),
        dirname(base_path()) . '/jvsystem/App/Cache/Docs/Investor/' . $proposalId,
        '/home/forge/jvsystem/App/Cache/Docs/Investor/' . $proposalId,
        '/home/forge/beta.jaevee.co.uk/App/Cache/Docs/Investor/' . $proposalId,
        '/var/www/jvsystem/App/Cache/Docs/Investor/' . $proposalId,
        '/home/betajaeveecouk/beta.jaevee.co.uk/App/Cache/Docs/Investor/' . $proposalId,
    ];
    
    foreach ($testPaths as $testPath) {
        $baseDir = dirname($testPath);
        $results['checked_dirs'][] = [
            'base_dir' => $baseDir,
            'base_exists' => is_dir($baseDir),
            'proposal_dir' => $testPath,
            'proposal_exists' => is_dir($testPath),
        ];
        
        if (is_dir($testPath)) {
            $files = glob($testPath . '/*.pdf');
            $results['proposal_dirs_found'][] = [
                'path' => $testPath,
                'file_count' => count($files),
                'files' => array_map('basename', $files),
            ];
        }
    }
    
    // Quick scan of common parent directories (non-recursive to avoid timeout)
    $quickScanDirs = [
        '/home/forge',
        '/var/www',
        dirname(base_path()),
    ];
    
    foreach ($quickScanDirs as $scanDir) {
        if (!is_dir($scanDir) || !is_readable($scanDir)) {
            continue;
        }
        
        try {
            $entries = scandir($scanDir);
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') continue;
                
                $fullPath = $scanDir . '/' . $entry;
                if (is_dir($fullPath) && (str_contains($entry, 'jvsystem') || str_contains($entry, 'jaevee'))) {
                    $docsPath = $fullPath . '/App/Cache/Docs/Investor';
                    if (is_dir($docsPath)) {
                        $results['found_dirs'][] = [
                            'path' => $docsPath,
                            'source' => 'quick_scan',
                            'readable' => is_readable($docsPath),
                            'proposal_2436_exists' => is_dir($docsPath . '/2436'),
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            // Skip
        }
    }
    
    return response()->json($results, 200, [], JSON_PRETTY_PRINT);
})->middleware('auth:investor')->name('admin.find.docs.directory');

// Test route to check a specific document hash
Route::get('/admin/test-document/{hash}', function ($hash) {
    $hash = urldecode($hash);
    $hashParts = explode('o', $hash);
    
    $authHash = $hashParts[0] ?? '';
    $timestamp = $hashParts[1] ?? '';
    $documentHash = implode('o', array_slice($hashParts, 2));
    
    // Try to find document
    $document = \App\Models\ProjectInvestorDocument::where('hash', $documentHash)->first();
    
    if (!$document) {
        // Try partial matches
        $allDocs = \App\Models\ProjectInvestorDocument::all();
        foreach ($allDocs as $doc) {
            if (str_contains($hash, $doc->hash) || str_contains($doc->hash, $documentHash)) {
                $document = $doc;
                break;
            }
        }
    }
    
    if (!$document) {
        return response()->json([
            'error' => 'Document not found in database',
            'searched_hash' => $documentHash,
            'all_documents' => \App\Models\ProjectInvestorDocument::select('id', 'proposal_id', 'hash', 'name')->get()->toArray(),
        ], 404);
    }
    
    // Check file locations - expanded list
    $baseDirs = [
        base_path('../jvsystem/App/Cache/Docs/Investor'),
        dirname(base_path()) . '/jvsystem/App/Cache/Docs/Investor',
        '/home/forge/jvsystem/App/Cache/Docs/Investor',
        '/home/forge/beta.jaevee.co.uk/App/Cache/Docs/Investor',
        '/var/www/jvsystem/App/Cache/Docs/Investor',
        '/home/betajaeveecouk/beta.jaevee.co.uk/App/Cache/Docs/Investor',
        storage_path('app/documents/investor'),
    ];
    
    // Also check for jvsystem in parent directories
    $parentDirs = [dirname(base_path()), dirname(dirname(base_path())), '/home/forge'];
    foreach ($parentDirs as $parentDir) {
        if (is_dir($parentDir)) {
            $jvsystemDirs = glob($parentDir . '/*/jvsystem', GLOB_ONLYDIR);
            foreach ($jvsystemDirs as $jvsystemDir) {
                $baseDirs[] = $jvsystemDir . '/App/Cache/Docs/Investor';
            }
            // Check if jvsystem is directly in parent
            if (is_dir($parentDir . '/jvsystem')) {
                $baseDirs[] = $parentDir . '/jvsystem/App/Cache/Docs/Investor';
            }
        }
    }
    
    $results = [
        'document' => [
            'id' => $document->id,
            'proposal_id' => $document->proposal_id,
            'hash' => $document->hash,
            'name' => $document->name,
        ],
        'file_checks' => [],
    ];
    
    foreach ($baseDirs as $baseDir) {
        if (is_dir($baseDir)) {
            $proposalDir = $baseDir . '/' . $document->proposal_id;
            $expectedFile = $proposalDir . '/' . $document->hash . '.pdf';
            
            $check = [
                'base_dir' => $baseDir,
                'exists' => is_dir($baseDir),
                'proposal_dir' => $proposalDir,
                'proposal_dir_exists' => is_dir($proposalDir),
                'expected_file' => $expectedFile,
                'file_exists' => file_exists($expectedFile),
                'file_readable' => file_exists($expectedFile) && is_readable($expectedFile),
            ];
            
            if (is_dir($proposalDir)) {
                $files = glob($proposalDir . '/*.pdf');
                $check['files_in_dir'] = array_map('basename', $files);
                $check['file_count'] = count($files);
            }
            
            $results['file_checks'][] = $check;
        } else {
            $results['file_checks'][] = [
                'base_dir' => $baseDir,
                'exists' => false,
            ];
        }
    }
    
    return response()->json($results, 200, [], JSON_PRETTY_PRINT);
})->middleware('auth:investor')->name('admin.test.document');

// Debug route to check document file locations (admin only)
Route::get('/admin/debug/document/{documentId}', function ($documentId) {
    $document = \App\Models\ProjectInvestorDocument::find($documentId);
    
    if (!$document) {
        return response()->json(['error' => 'Document not found'], 404);
    }
    
    $baseDirs = [
        base_path('../jvsystem/App/Cache/Docs/Investor'),
        dirname(base_path()) . '/jvsystem/App/Cache/Docs/Investor',
        '/var/www/jvsystem/App/Cache/Docs/Investor',
        '/home/betajaeveecouk/beta.jaevee.co.uk/App/Cache/Docs/Investor',
    ];
    
    $results = [
        'document' => [
            'id' => $document->id,
            'proposal_id' => $document->proposal_id,
            'hash' => $document->hash,
            'name' => $document->name,
        ],
        'checked_directories' => [],
        'found_files' => [],
    ];
    
    foreach ($baseDirs as $baseDir) {
        $exists = is_dir($baseDir);
        $readable = $exists && is_readable($baseDir);
        
        $results['checked_directories'][] = [
            'path' => $baseDir,
            'exists' => $exists,
            'readable' => $readable,
        ];
        
        if ($exists && $readable) {
            $proposalDir = $baseDir . '/' . $document->proposal_id;
            $proposalExists = is_dir($proposalDir);
            
            if ($proposalExists) {
                $expectedFile = $proposalDir . '/' . $document->hash . '.pdf';
                $fileExists = file_exists($expectedFile);
                
                $results['checked_directories'][] = [
                    'path' => $proposalDir,
                    'exists' => $proposalExists,
                    'readable' => is_readable($proposalDir),
                ];
                
                if ($fileExists) {
                    $results['found_files'][] = [
                        'path' => $expectedFile,
                        'size' => filesize($expectedFile),
                        'readable' => is_readable($expectedFile),
                    ];
                } else {
                    // List all files in the directory
                    $files = glob($proposalDir . '/*.pdf');
                    $results['found_files'][] = [
                        'path' => $proposalDir,
                        'expected_file' => $expectedFile,
                        'exists' => false,
                        'available_files' => array_map('basename', $files),
                    ];
                }
            }
        }
    }
    
    return response()->json($results, 200, [], JSON_PRETTY_PRINT);
})->middleware('auth:investor')->name('admin.debug.document');

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

// One-time route to run system status migration
Route::get('/run-system-status-migration', function () {
    try {
        $filePath = database_path('migrations_sql/005_create_system_status_table.sql');
        
        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'error' => 'File not found: ' . $filePath,
            ], 404);
        }
        
        $sql = file_get_contents($filePath);
        
        // First, check if table already exists
        try {
            $tableExists = \DB::connection('legacy')->select("SHOW TABLES LIKE 'system_status'");
            if (!empty($tableExists)) {
                return response()->json([
                    'success' => true,
                    'message' => 'System status table already exists!',
                    'statements_executed' => 0,
                    'errors' => [],
                    'note' => 'The table was already present in the database. You can now use the system status feature.',
                ], 200, [], JSON_PRETTY_PRINT);
            }
        } catch (\Exception $e) {
            // Continue with migration if check fails
        }
        
        // Execute the SQL directly (CREATE TABLE IF NOT EXISTS will handle duplicates)
        try {
            \DB::connection('legacy')->unprepared($sql);
            
            // Verify table was created
            $tableExists = \DB::connection('legacy')->select("SHOW TABLES LIKE 'system_status'");
            if (!empty($tableExists)) {
                return response()->json([
                    'success' => true,
                    'message' => 'System status table created successfully!',
                    'statements_executed' => 1,
                    'errors' => [],
                    'note' => 'The table has been created. You can now use the system status feature.',
                ], 200, [], JSON_PRETTY_PRINT);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'SQL executed but table was not created.',
                    'statements_executed' => 1,
                    'errors' => ['Table verification failed'],
                ], 500, [], JSON_PRETTY_PRINT);
            }
        } catch (\Exception $e) {
            // Check if error is because table already exists
            if (str_contains($e->getMessage(), 'already exists') || 
                str_contains($e->getMessage(), 'Duplicate') ||
                str_contains($e->getMessage(), 'Table \'jvsys.system_status\' already exists')) {
                return response()->json([
                    'success' => true,
                    'message' => 'System status table already exists!',
                    'statements_executed' => 0,
                    'errors' => [],
                    'note' => 'The table was already present in the database. You can now use the system status feature.',
                ], 200, [], JSON_PRETTY_PRINT);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating system status table.',
                'statements_executed' => 0,
                'errors' => [$e->getMessage()],
            ], 500, [], JSON_PRETTY_PRINT);
        }
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500, [], JSON_PRETTY_PRINT);
    }
})->name('run.system.status.migration');

// One-time route to run support ticket replies migration
Route::get('/run-support-ticket-migration', function () {
    try {
        $filePath = database_path('migrations_sql/006_create_support_ticket_replies.sql');
        
        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'error' => 'File not found: ' . $filePath,
            ], 404);
        }
        
        $sql = file_get_contents($filePath);
        
        // Split SQL into individual statements and execute them one by one
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                $stmt = trim($stmt);
                return !empty($stmt) && 
                       !preg_match('/^--/', $stmt) &&
                       !preg_match('/^\/\*/', $stmt) &&
                       strlen($stmt) > 10;
            }
        );
        
        $executed = 0;
        $errors = [];
        $skipped = [];
        
        foreach ($statements as $statement) {
            try {
                if (!str_ends_with(trim($statement), ';')) {
                    $statement .= ';';
                }
                \DB::connection('legacy')->statement($statement);
                $executed++;
            } catch (\Exception $e) {
                // Check if error is because table/column/index already exists
                if (str_contains($e->getMessage(), 'already exists') || 
                    str_contains($e->getMessage(), 'Duplicate') ||
                    str_contains($e->getMessage(), 'Duplicate column name') ||
                    str_contains($e->getMessage(), 'Duplicate key name')) {
                    $skipped[] = $e->getMessage();
                    $executed++; // Count as executed since it's just a duplicate
                } else {
                    $errors[] = $e->getMessage();
                }
            }
        }
        
        if (empty($errors)) {
            return response()->json([
                'success' => true,
                'message' => 'Support ticket replies migration completed!',
                'statements_executed' => $executed,
                'skipped' => $skipped,
                'note' => 'The support_ticket_replies table has been created and support_tickets table has been updated.',
            ], 200, [], JSON_PRETTY_PRINT);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Some statements failed',
                'statements_executed' => $executed,
                'errors' => $errors,
                'skipped' => $skipped,
            ], 500, [], JSON_PRETTY_PRINT);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500, [], JSON_PRETTY_PRINT);
    }
})->name('run.support.ticket.migration');

// One-time route to run email history migration (no auth required)
Route::get('/run-email-history-migration', function () {
    try {
        $filePath = database_path('migrations_sql/009_create_email_history_table.sql');
        
        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'error' => 'File not found: ' . $filePath,
            ], 404);
        }
        
        $sql = file_get_contents($filePath);
        
        // Check if table already exists
        if (\Illuminate\Support\Facades\Schema::connection('legacy')->hasTable('email_history')) {
            return response()->json([
                'success' => true,
                'message' => 'Email history table already exists!',
                'statements_executed' => 0,
                'errors' => [],
                'note' => 'The table was already present in the database.',
            ], 200, [], JSON_PRETTY_PRINT);
        }

        // Execute SQL directly
        \DB::connection('legacy')->unprepared($sql);

        // Verify table creation
        if (\Illuminate\Support\Facades\Schema::connection('legacy')->hasTable('email_history')) {
            return response()->json([
                'success' => true,
                'message' => 'Email history table created successfully!',
                'statements_executed' => 1,
                'errors' => [],
                'note' => 'The table has been created. You can now use the email history feature.',
            ], 200, [], JSON_PRETTY_PRINT);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'SQL executed but table was not created.',
                'statements_executed' => 1,
                'errors' => ['Table verification failed'],
            ], 500, [], JSON_PRETTY_PRINT);
        }
    } catch (\Exception $e) {
        // Check if error is because table already exists
        if (str_contains($e->getMessage(), 'already exists') || 
            str_contains($e->getMessage(), 'Duplicate') ||
            str_contains($e->getMessage(), 'Table \'jvsys.email_history\' already exists')) {
            return response()->json([
                'success' => true,
                'message' => 'Email history table already exists!',
                'statements_executed' => 0,
                'errors' => [],
                'note' => 'The table was already present in the database.',
            ], 200, [], JSON_PRETTY_PRINT);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Error creating email history table.',
            'statements_executed' => 0,
            'errors' => [$e->getMessage()],
        ], 500, [], JSON_PRETTY_PRINT);
    }
})->name('run.email.history.migration');

// One-time route to run system status updates migration (no auth required)
Route::get('/run-system-status-updates-migration', function () {
    try {
        $filePath = database_path('migrations_sql/008_create_system_status_updates.sql');
        
        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'error' => 'File not found: ' . $filePath,
            ], 404);
        }
        
        $sql = file_get_contents($filePath);
        
        // Check if table already exists
        if (\Illuminate\Support\Facades\Schema::connection('legacy')->hasTable('system_status_updates')) {
            return response()->json([
                'success' => true,
                'message' => 'System status updates table already exists!',
                'statements_executed' => 0,
                'errors' => [],
                'note' => 'The table was already present in the database.',
            ], 200, [], JSON_PRETTY_PRINT);
        }

        // Execute SQL directly
        \DB::connection('legacy')->unprepared($sql);

        // Verify table creation
        if (\Illuminate\Support\Facades\Schema::connection('legacy')->hasTable('system_status_updates')) {
            return response()->json([
                'success' => true,
                'message' => 'System status updates table created successfully!',
                'statements_executed' => 1,
                'errors' => [],
                'note' => 'The table has been created. You can now use the system status updates feature.',
            ], 200, [], JSON_PRETTY_PRINT);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'SQL executed but table was not created.',
                'statements_executed' => 1,
                'errors' => ['Table verification failed'],
            ], 500, [], JSON_PRETTY_PRINT);
        }
    } catch (\Exception $e) {
        // Check if error is because table already exists
        if (str_contains($e->getMessage(), 'already exists') || 
            str_contains($e->getMessage(), 'Duplicate') ||
            str_contains($e->getMessage(), 'Table \'jvsys.system_status_updates\' already exists')) {
            return response()->json([
                'success' => true,
                'message' => 'System status updates table already exists!',
                'statements_executed' => 0,
                'errors' => [],
                'note' => 'The table was already present in the database.',
            ], 200, [], JSON_PRETTY_PRINT);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Error creating system status updates table.',
            'statements_executed' => 0,
            'errors' => [$e->getMessage()],
        ], 500, [], JSON_PRETTY_PRINT);
    }
})->name('run.system.status.updates.migration');

// One-time route to run document migrations (remove after use)
Route::get('/run-document-migrations', function () {
    try {
        $results = [];
        
        // Read and execute SQL files
        $sqlFiles = [
            '001_create_account_documents.sql',
            '002_create_project_documents.sql',
            '003_create_update_images.sql',
            '004_add_rich_content_to_projects.sql',
        ];
        
        foreach ($sqlFiles as $file) {
            $filePath = database_path('migrations_sql/' . $file);
            
            if (!file_exists($filePath)) {
                $results[$file] = [
                    'success' => false,
                    'error' => 'File not found: ' . $filePath,
                ];
                continue;
            }
            
            $sql = file_get_contents($filePath);
            
            // Split SQL into individual statements
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($stmt) {
                    $stmt = trim($stmt);
                    // Skip empty statements and comments
                    return !empty($stmt) && 
                           !preg_match('/^--/', $stmt) &&
                           !preg_match('/^\/\*/', $stmt) &&
                           strlen($stmt) > 10; // Minimum statement length
                }
            );
            
            $executed = 0;
            $errors = [];
            
            foreach ($statements as $statement) {
                try {
                    \DB::connection('legacy')->statement($statement);
                    $executed++;
                } catch (\Exception $e) {
                    // Check if it's a "table/column already exists" error (which is fine)
                    if (str_contains($e->getMessage(), 'already exists') || 
                        str_contains($e->getMessage(), 'Duplicate column name')) {
                        // This is fine, table/column already exists
                        $executed++;
                    } else {
                        $errors[] = $e->getMessage();
                    }
                }
            }
            
            $results[$file] = [
                'success' => empty($errors),
                'statements_executed' => $executed,
                'errors' => $errors,
            ];
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Document migrations completed!',
            'results' => $results,
            'note' => 'If you see "already exists" errors, that\'s fine - it means the tables/columns were already created.',
        ], 200, [], JSON_PRETTY_PRINT);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500, [], JSON_PRETTY_PRINT);
    }
})->name('run.document.migrations');

// Safety: dedicated route to (re)create update_images table if it's still missing
Route::get('/run-update-images-table-fix', function () {
    try {
        // Use legacy connection where all JV system tables live
        $schema = \Illuminate\Support\Facades\Schema::connection('legacy');

        if ($schema->hasTable('update_images')) {
            return response()->json([
                'success' => true,
                'message' => 'update_images table already exists.',
            ], 200, [], JSON_PRETTY_PRINT);
        }

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `update_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `update_id` bigint(20) unsigned NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_on` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_update_id` (`update_id`),
  KEY `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        \DB::connection('legacy')->unprepared($sql);

        if ($schema->hasTable('update_images')) {
            return response()->json([
                'success' => true,
                'message' => 'update_images table created successfully.',
            ], 200, [], JSON_PRETTY_PRINT);
        }

        return response()->json([
            'success' => false,
            'message' => 'SQL executed but update_images table still not found.',
        ], 500, [], JSON_PRETTY_PRINT);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error creating update_images table.',
            'error' => $e->getMessage(),
        ], 500, [], JSON_PRETTY_PRINT);
    }
})->name('run.update.images.table.fix');

// One-time route to create changelog_entries table
Route::get('/run-changelog-migration', function () {
    try {
        $schema = \Illuminate\Support\Facades\Schema::connection('legacy');
        // Create table if missing
        if (!$schema->hasTable('changelog_entries')) {
            $filePath = database_path('migrations_sql/010_create_changelog_entries.sql');
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Migration file not found: ' . $filePath,
                ], 404, [], JSON_PRETTY_PRINT);
            }

            $sql = file_get_contents($filePath);
            \DB::connection('legacy')->unprepared($sql);
        }

        // Ensure commit_hash column exists for git sync
        if (!$schema->hasColumn('changelog_entries', 'commit_hash')) {
            try {
                \DB::connection('legacy')->statement("
                    ALTER TABLE `changelog_entries`
                    ADD COLUMN `commit_hash` varchar(64) DEFAULT NULL,
                    ADD KEY `idx_commit_hash` (`commit_hash`)
                ");
            } catch (\Exception $e) {
                if (!str_contains($e->getMessage(), 'Duplicate column name')) {
                    throw $e;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'changelog_entries table and commit_hash column are ready.',
        ], 200, [], JSON_PRETTY_PRINT);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error creating changelog_entries table.',
            'error' => $e->getMessage(),
        ], 500, [], JSON_PRETTY_PRINT);
    }
})->name('run.changelog.migration');

// One-time route to create email_templates table
Route::get('/run-email-templates-migration', function () {
    try {
        $schema = \Illuminate\Support\Facades\Schema::connection('legacy');
        if (!$schema->hasTable('email_templates')) {
            $filePath = database_path('migrations_sql/011_create_email_templates.sql');
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Migration file not found: ' . $filePath,
                ], 404, [], JSON_PRETTY_PRINT);
            }

            $sql = file_get_contents($filePath);
            \DB::connection('legacy')->unprepared($sql);
        }

        return response()->json([
            'success' => true,
            'message' => 'email_templates table is ready.',
        ], 200, [], JSON_PRETTY_PRINT);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error creating email_templates table.',
            'error' => $e->getMessage(),
        ], 500, [], JSON_PRETTY_PRINT);
    }
})->name('run.email.templates.migration');

// One-time route to seed initial email templates
Route::get('/seed-email-templates', function () {
    try {
        $templates = [
            [
                'key' => 'welcome_investor',
                'name' => 'Welcome Email',
                'subject' => 'Welcome to JaeVee',
                'body_html' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to JaeVee</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px 40px 20px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">Welcome to JaeVee</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px 40px;">
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;">Hello {{name}},</p>
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;">Welcome to JaeVee! We\'re excited to have you on board.</p>
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;">You can now access your investor dashboard to view your investments, documents, and project updates.</p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{dashboard_url}}" style="display: inline-block; padding: 14px 32px; background-color: #667eea; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">Access Your Dashboard</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 20px 0 0; color: #666666; font-size: 14px; line-height: 1.6;">If you have any questions, please don\'t hesitate to contact our support team.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 40px; background-color: #f8f9fa; border-top: 1px solid #e9ecef; text-align: center;">
                            <p style="margin: 0; color: #6c757d; font-size: 12px;">© ' . date('Y') . ' JaeVee. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>',
                'body_text' => "Welcome to JaeVee\n\nHello {{name}},\n\nWelcome to JaeVee! We're excited to have you on board.\n\nYou can now access your investor dashboard to view your investments, documents, and project updates.\n\nAccess your dashboard: {{dashboard_url}}\n\nIf you have any questions, please don't hesitate to contact our support team.\n\n© " . date('Y') . " JaeVee. All rights reserved.",
            ],
            [
                'key' => 'password_reset',
                'name' => 'Password Reset',
                'subject' => 'Reset Your Password',
                'body_html' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px 40px 20px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">Reset Your Password</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px 40px;">
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;">Hello {{name}},</p>
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;">We received a request to reset your password for your JaeVee account.</p>
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;">Click the button below to reset your password. This link will expire in {{expires_in}}.</p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{reset_url}}" style="display: inline-block; padding: 14px 32px; background-color: #667eea; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">Reset Password</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 20px 0 0; color: #666666; font-size: 14px; line-height: 1.6;">If you did not request a password reset, please ignore this email or contact support if you have concerns.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 40px; background-color: #f8f9fa; border-top: 1px solid #e9ecef; text-align: center;">
                            <p style="margin: 0; color: #6c757d; font-size: 12px;">© ' . date('Y') . ' JaeVee. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>',
                'body_text' => "Reset Your Password\n\nHello {{name}},\n\nWe received a request to reset your password for your JaeVee account.\n\nClick the link below to reset your password. This link will expire in {{expires_in}}.\n\n{{reset_url}}\n\nIf you did not request a password reset, please ignore this email or contact support if you have concerns.\n\n© " . date('Y') . " JaeVee. All rights reserved.",
            ],
            [
                'key' => 'project_update',
                'name' => 'Project Update',
                'subject' => 'New Update: {{project_name}}',
                'body_html' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Update</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px 40px 20px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">Project Update</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px 40px;">
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;">Hello {{name}},</p>
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;">We have a new update for <strong>{{project_name}}</strong>.</p>
                            <div style="margin: 30px 0; padding: 20px; background-color: #f8f9fa; border-radius: 6px; border-left: 4px solid #667eea;">
                                <div style="color: #333333; font-size: 16px; line-height: 1.8;">{{update_content}}</div>
                            </div>
                            <p style="margin: 20px 0; color: #666666; font-size: 14px;">Update posted on {{update_date}}</p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{project_url}}" style="display: inline-block; padding: 14px 32px; background-color: #667eea; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">View Full Update</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 40px; background-color: #f8f9fa; border-top: 1px solid #e9ecef; text-align: center;">
                            <p style="margin: 0; color: #6c757d; font-size: 12px;">© ' . date('Y') . ' JaeVee. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>',
                'body_text' => "Project Update\n\nHello {{name}},\n\nWe have a new update for {{project_name}}.\n\n{{update_content}}\n\nUpdate posted on {{update_date}}\n\nView full update: {{project_url}}\n\n© " . date('Y') . " JaeVee. All rights reserved.",
            ],
            [
                'key' => 'support_ticket_confirmation',
                'name' => 'Support Ticket Confirmation',
                'subject' => 'Support Ticket Created - {{ticket_id}}',
                'body_html' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Ticket Confirmation</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px 40px 20px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">Support Ticket Created</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px 40px;">
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;">Hello {{name}},</p>
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;">Thank you for contacting us. We\'ve received your support request and our team will get back to you as soon as possible.</p>
                            <div style="margin: 30px 0; padding: 20px; background-color: #f8f9fa; border-radius: 6px; border-left: 4px solid #667eea;">
                                <p style="margin: 0 0 10px; color: #333333; font-size: 14px; font-weight: 600;">Ticket ID: {{ticket_id}}</p>
                                <p style="margin: 0 0 10px; color: #333333; font-size: 14px; font-weight: 600;">Subject: {{subject}}</p>
                                <p style="margin: 10px 0 0; color: #666666; font-size: 14px; line-height: 1.6;">{{message}}</p>
                            </div>
                            <p style="margin: 20px 0; color: #666666; font-size: 14px; line-height: 1.6;">You\'ll receive email updates when we respond to your ticket.</p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{dashboard_url}}" style="display: inline-block; padding: 14px 32px; background-color: #667eea; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">View Ticket</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 40px; background-color: #f8f9fa; border-top: 1px solid #e9ecef; text-align: center;">
                            <p style="margin: 0; color: #6c757d; font-size: 12px;">© ' . date('Y') . ' JaeVee. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>',
                'body_text' => "Support Ticket Confirmation\n\nHello {{name}},\n\nThank you for contacting us. We've received your support request and our team will get back to you as soon as possible.\n\nTicket ID: {{ticket_id}}\nSubject: {{subject}}\n\n{{message}}\n\nYou'll receive email updates when we respond to your ticket.\n\nView ticket: {{dashboard_url}}\n\n© " . date('Y') . " JaeVee. All rights reserved.",
            ],
            [
                'key' => 'account_share_notification',
                'name' => 'Account Share Notification',
                'subject' => 'Account Access Shared - JaeVee',
                'body_html' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Access Shared</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px 40px 20px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">Account Access Shared</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px 40px;">
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;">Hello {{name}},</p>
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;"><strong>{{primary_account_name}}</strong> has shared their investment account access with you.</p>
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;">You can now view their investments, documents, and project updates in your dashboard.</p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{dashboard_url}}" style="display: inline-block; padding: 14px 32px; background-color: #667eea; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">View Dashboard</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 40px; background-color: #f8f9fa; border-top: 1px solid #e9ecef; text-align: center;">
                            <p style="margin: 0; color: #6c757d; font-size: 12px;">© ' . date('Y') . ' JaeVee. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>',
                'body_text' => "Account Access Shared\n\nHello {{name}},\n\n{{primary_account_name}} has shared their investment account access with you.\n\nYou can now view their investments, documents, and project updates in your dashboard.\n\nView dashboard: {{dashboard_url}}\n\n© " . date('Y') . " JaeVee. All rights reserved.",
            ],
            [
                'key' => 'project_documents',
                'name' => 'Project Documents',
                'subject' => 'Your Documents for {{project_name}}',
                'body_html' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Project Documents</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px 40px 20px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">Your Project Documents</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px 40px;">
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;">Hello {{name}},</p>
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;">Your documents for <strong>{{project_name}}</strong> are ready for download.</p>
                            <div style="margin: 30px 0; padding: 20px; background-color: #f8f9fa; border-radius: 6px;">
                                {{documents_list}}
                            </div>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{dashboard_url}}" style="display: inline-block; padding: 14px 32px; background-color: #667eea; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">View Dashboard</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 40px; background-color: #f8f9fa; border-top: 1px solid #e9ecef; text-align: center;">
                            <p style="margin: 0; color: #6c757d; font-size: 12px;">© ' . date('Y') . ' JaeVee. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>',
                'body_text' => "Your Project Documents\n\nHello {{name}},\n\nYour documents for {{project_name}} are ready for download.\n\n{{documents_list_text}}\n\nView dashboard: {{dashboard_url}}\n\n© " . date('Y') . " JaeVee. All rights reserved.",
            ],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($templates as $template) {
            $exists = \DB::connection('legacy')
                ->table('email_templates')
                ->where('key', $template['key'])
                ->where('deleted', 0)
                ->exists();

            if (!$exists) {
                \DB::connection('legacy')->table('email_templates')->insert([
                    'key' => $template['key'],
                    'name' => $template['name'],
                    'subject' => $template['subject'],
                    'body_html' => $template['body_html'],
                    'body_text' => $template['body_text'],
                    'created_on' => now(),
                    'updated_on' => now(),
                    'deleted' => 0,
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Email templates seeded. Created: {$created}, Skipped: {$skipped}",
            'created' => $created,
            'skipped' => $skipped,
        ], 200, [], JSON_PRETTY_PRINT);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error seeding email templates.',
            'error' => $e->getMessage(),
        ], 500, [], JSON_PRETTY_PRINT);
    }
})->name('seed.email.templates');

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
