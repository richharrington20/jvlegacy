<?php
/**
 * Clear Blade compiled views cache
 * Run this on the SERVER to clear cached compiled views
 */

$logPath = __DIR__ . '/.cursor/debug.log';
$logFile = fopen($logPath, 'a');

$log = function($message, $data = []) use ($logFile) {
    $entry = json_encode([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => (int)(microtime(true) * 1000),
        'location' => 'clear_blade_cache.php',
        'message' => $message,
        'data' => $data,
        'sessionId' => 'debug-session',
        'runId' => 'cache-clear',
    ], JSON_PRETTY_PRINT) . "\n";
    fwrite($logFile, $entry);
};

echo "Clearing Blade compiled views cache...\n";

$compiledPath = __DIR__ . '/storage/framework/views';
$cleared = 0;

if (is_dir($compiledPath)) {
    $files = glob($compiledPath . '/*.php');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            $cleared++;
        }
    }
    $log('Cache cleared', [
        'compiled_path' => $compiledPath,
        'files_cleared' => $cleared,
    ]);
    echo "Cleared {$cleared} compiled view files.\n";
} else {
    $log('Cache directory not found', [
        'compiled_path' => $compiledPath,
    ]);
    echo "Cache directory not found: {$compiledPath}\n";
}

fclose($logFile);
echo "Done. Check .cursor/debug.log for details.\n";

