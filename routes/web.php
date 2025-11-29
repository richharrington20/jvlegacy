
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
