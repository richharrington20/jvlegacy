<?php
/**
 * Test Blade compilation to find the actual issue
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$logPath = __DIR__ . '/.cursor/debug.log';
$logFile = fopen($logPath, 'a');

$log = function($hypothesisId, $message, $data = []) use ($logFile) {
    $entry = json_encode([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => (int)(microtime(true) * 1000),
        'location' => 'test_blade_compile.php',
        'message' => $message,
        'data' => $data,
        'sessionId' => 'debug-session',
        'runId' => 'compile-test',
        'hypothesisId' => $hypothesisId,
    ], JSON_PRETTY_PRINT) . "\n";
    fwrite($logFile, $entry);
};

echo "Testing Blade compilation...\n";

try {
    // Hypothesis G: Try to compile the view and catch the exact error
    $viewPath = 'investor.dashboard';
    
    $log('G', 'Attempting view compilation', [
        'view_path' => $viewPath,
    ]);
    
    $compiler = app('blade.compiler');
    $viewFinder = app('view.finder');
    
    $path = $viewFinder->find($viewPath);
    $log('G', 'View file found', [
        'path' => $path,
        'exists' => file_exists($path),
    ]);
    
    // Read the actual file content
    $content = file_get_contents($path);
    $log('G', 'File content read', [
        'size' => strlen($content),
        'lines' => substr_count($content, "\n") + 1,
    ]);
    
    // Try to compile
    try {
        $compiled = $compiler->compile($content);
        $log('G', 'Compilation successful', [
            'compiled_size' => strlen($compiled),
        ]);
        echo "SUCCESS: View compiled without errors!\n";
    } catch (\Exception $e) {
        $log('G', 'Compilation error', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
        echo "ERROR: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
    // Hypothesis H: Check compiled view cache
    $compiledPath = storage_path('framework/views');
    $compiledFiles = [];
    if (is_dir($compiledPath)) {
        $files = glob($compiledPath . '/*.php');
        foreach ($files as $file) {
            $fileContent = file_get_contents($file);
            if (strpos($fileContent, 'Welcome,') !== false || strpos($fileContent, 'investor/dashboard') !== false) {
                $compiledFiles[] = [
                    'path' => $file,
                    'size' => filesize($file),
                    'modified' => date('Y-m-d H:i:s', filemtime($file)),
                    'content_preview' => substr($fileContent, 0, 500),
                ];
            }
        }
    }
    
    $log('H', 'Compiled view cache', [
        'compiled_path' => $compiledPath,
        'found_files' => $compiledFiles,
    ]);
    
} catch (\Exception $e) {
    $log('ERROR', 'Script error', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
    echo "Script error: " . $e->getMessage() . "\n";
}

fclose($logFile);
echo "\nLogs written to: {$logPath}\n";

