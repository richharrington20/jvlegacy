<?php
/**
 * Try to compile the Blade view using Laravel's compiler
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
        'location' => 'compile_blade_test.php',
        'message' => $message,
        'data' => $data,
        'sessionId' => 'debug-session',
        'runId' => 'compile-test',
        'hypothesisId' => $hypothesisId,
    ], JSON_PRETTY_PRINT) . "\n";
    fwrite($logFile, $entry);
};

echo "Attempting to compile investor.dashboard view...\n\n";

try {
    // Hypothesis Q: Try to compile the view and catch the exact error
    $viewPath = 'investor.dashboard';
    
    $log('Q', 'Attempting view compilation', [
        'view_path' => $viewPath,
    ]);
    
    $compiler = app('blade.compiler');
    $viewFinder = app('view.finder');
    
    $path = $viewFinder->find($viewPath);
    $log('Q', 'View file found', [
        'path' => $path,
        'exists' => file_exists($path),
    ]);
    
    // Read the actual file content
    $content = file_get_contents($path);
    $log('Q', 'File content read', [
        'size' => strlen($content),
        'lines' => substr_count($content, "\n") + 1,
        'last_50_chars' => substr($content, -50),
    ]);
    
    // Try to compile
    try {
        $compiled = $compiler->compile($content);
        $log('Q', 'Compilation successful', [
            'compiled_size' => strlen($compiled),
            'last_200_chars' => substr($compiled, -200),
        ]);
        echo "SUCCESS: View compiled without errors!\n";
    } catch (\Exception $e) {
        $log('Q', 'Compilation error', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
        echo "ERROR: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
    // Also check the layout file
    try {
        $layoutPath = $viewFinder->find('layouts.app');
        $layoutContent = file_get_contents($layoutPath);
        $layoutCompiled = $compiler->compile($layoutContent);
        $log('Q', 'Layout compilation successful', [
            'layout_path' => $layoutPath,
            'compiled_size' => strlen($layoutCompiled),
        ]);
    } catch (\Exception $e) {
        $log('Q', 'Layout compilation error', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        echo "LAYOUT ERROR: " . $e->getMessage() . "\n";
    }
    
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

