<?php
/**
 * Verify Blade structure and identify the exact issue
 */

$filePath = __DIR__ . '/resources/views/investor/dashboard.blade.php';
$logPath = __DIR__ . '/.cursor/debug.log';

$content = file_get_contents($filePath);
$lines = explode("\n", $content);

$logFile = fopen($logPath, 'a');

$log = function($hypothesisId, $message, $data = []) use ($logFile) {
    $entry = json_encode([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => (int)(microtime(true) * 1000),
        'location' => 'verify_blade_structure.php',
        'message' => $message,
        'data' => $data,
        'sessionId' => 'debug-session',
        'runId' => 'verification',
        'hypothesisId' => $hypothesisId,
    ], JSON_PRETTY_PRINT) . "\n";
    fwrite($logFile, $entry);
};

// Hypothesis I: Check each @if with @elseif/@else has proper @endif
$ifBlocks = [];
$stack = [];
$lineNum = 1;

foreach ($lines as $line) {
    if (preg_match('/@if\s*\(/', $line)) {
        $stack[] = [
            'line' => $lineNum,
            'content' => trim($line),
            'has_elseif' => false,
            'has_else' => false,
            'endif_line' => null,
        ];
    }
    
    if (preg_match('/@elseif\s*\(/', $line)) {
        if (!empty($stack)) {
            $stack[count($stack) - 1]['has_elseif'] = true;
        }
    }
    
    if (preg_match('/@else\b/', $line) && !preg_match('/@elseif/', $line)) {
        if (!empty($stack)) {
            $stack[count($stack) - 1]['has_else'] = true;
        }
    }
    
    if (preg_match('/@endif/', $line)) {
        if (!empty($stack)) {
            $block = array_pop($stack);
            $block['endif_line'] = $lineNum;
            $ifBlocks[] = $block;
        }
    }
    
    $lineNum++;
}

// Check for blocks with @elseif/@else
$blocksWithElse = array_filter($ifBlocks, function($block) {
    return $block['has_elseif'] || $block['has_else'];
});

$log('I', 'Blocks with @elseif/@else', [
    'total_blocks' => count($ifBlocks),
    'blocks_with_else' => count($blocksWithElse),
    'blocks' => array_map(function($b) {
        return [
            'line' => $b['line'],
            'endif_line' => $b['endif_line'],
            'has_elseif' => $b['has_elseif'],
            'has_else' => $b['has_else'],
        ];
    }, $blocksWithElse),
]);

// Hypothesis J: Check if any @if is missing @endif by checking the end of file
$remainingStack = [];
$lineNum = 1;

foreach ($lines as $line) {
    if (preg_match('/@if\s*\(/', $line)) {
        $remainingStack[] = ['line' => $lineNum, 'content' => trim($line)];
    }
    if (preg_match('/@endif/', $line)) {
        if (!empty($remainingStack)) {
            array_pop($remainingStack);
        }
    }
    $lineNum++;
}

$log('J', 'Remaining stack at end of file', [
    'unclosed_count' => count($remainingStack),
    'unclosed' => $remainingStack,
    'last_50_lines' => array_slice($lines, -50),
]);

fclose($logFile);

echo "Verification complete. Check logs.\n";
if (!empty($remainingStack)) {
    echo "ERROR: Found " . count($remainingStack) . " unclosed @if statements:\n";
    foreach ($remainingStack as $item) {
        echo "  Line {$item['line']}: {$item['content']}\n";
    }
} else {
    echo "SUCCESS: All @if statements are closed.\n";
}

