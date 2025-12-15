<?php
/**
 * Trace all @if blocks with @elseif/@else to ensure they have @endif
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
        'location' => 'trace_elseif_blocks.php',
        'message' => $message,
        'data' => $data,
        'sessionId' => 'debug-session',
        'runId' => 'trace-elseif',
        'hypothesisId' => $hypothesisId,
    ], JSON_PRETTY_PRINT) . "\n";
    fwrite($logFile, $entry);
};

echo "Tracing all @if blocks with @elseif/@else...\n\n";

$stack = [];
$blocks = [];
$lineNum = 1;
$currentBlock = null;

foreach ($lines as $line) {
    if (preg_match('/@if\s*\(/', $line)) {
        $currentBlock = [
            'start_line' => $lineNum,
            'content' => trim($line),
            'has_elseif' => false,
            'has_else' => false,
            'elseif_lines' => [],
            'else_line' => null,
            'endif_line' => null,
            'status' => 'open',
        ];
        $stack[] = $currentBlock;
    }
    
    if (preg_match('/@elseif\s*\(/', $line)) {
        if (!empty($stack)) {
            $stack[count($stack) - 1]['has_elseif'] = true;
            $stack[count($stack) - 1]['elseif_lines'][] = $lineNum;
        }
    }
    
    if (preg_match('/@else\b/', $line) && !preg_match('/@elseif/', $line)) {
        if (!empty($stack)) {
            $stack[count($stack) - 1]['has_else'] = true;
            $stack[count($stack) - 1]['else_line'] = $lineNum;
        }
    }
    
    if (preg_match('/@endif/', $line)) {
        if (!empty($stack)) {
            $block = array_pop($stack);
            $block['endif_line'] = $lineNum;
            $block['status'] = 'closed';
            $blocks[] = $block;
        }
    }
    
    $lineNum++;
}

// Check for blocks with @elseif/@else that might be missing @endif
$problematicBlocks = [];
foreach ($blocks as $block) {
    if (($block['has_elseif'] || $block['has_else']) && !$block['endif_line']) {
        $problematicBlocks[] = $block;
    }
}

// Also check unclosed blocks
$unclosedBlocks = [];
foreach ($stack as $block) {
    if ($block['has_elseif'] || $block['has_else']) {
        $unclosedBlocks[] = $block;
    }
}

$log('M', 'Blocks with @elseif/@else analysis', [
    'total_blocks' => count($blocks),
    'blocks_with_else' => array_filter($blocks, function($b) { return $b['has_elseif'] || $b['has_else']; }),
    'problematic_blocks' => $problematicBlocks,
    'unclosed_blocks' => $unclosedBlocks,
]);

// Hypothesis N: Check if there's an @if that's being parsed incorrectly
// by checking the actual compiled output structure
$lineNum = 1;
$ifStack = [];
$detailedTrace = [];

foreach ($lines as $line) {
    if (preg_match('/@if\s*\(/', $line)) {
        $ifStack[] = [
            'line' => $lineNum,
            'content' => trim($line),
            'full_line' => $line,
            'char_count' => strlen($line),
        ];
    }
    
    if (preg_match('/@elseif\s*\(/', $line)) {
        if (!empty($ifStack)) {
            $ifStack[count($ifStack) - 1]['has_elseif'] = true;
        }
    }
    
    if (preg_match('/@else\b/', $line) && !preg_match('/@elseif/', $line)) {
        if (!empty($ifStack)) {
            $ifStack[count($ifStack) - 1]['has_else'] = true;
        }
    }
    
    if (preg_match('/@endif/', $line)) {
        if (!empty($ifStack)) {
            $popped = array_pop($ifStack);
            $detailedTrace[] = [
                'if_line' => $popped['line'],
                'endif_line' => $lineNum,
                'has_elseif' => $popped['has_elseif'] ?? false,
                'has_else' => $popped['has_else'] ?? false,
                'if_content' => $popped['content'],
            ];
        }
    }
    
    $lineNum++;
}

$log('N', 'Detailed trace of all @if blocks', [
    'total_traced' => count($detailedTrace),
    'unclosed_count' => count($ifStack),
    'unclosed' => $ifStack,
    'last_10_blocks' => array_slice($detailedTrace, -10),
]);

fclose($logFile);

echo "=== RESULTS ===\n";
echo "Total @if blocks traced: " . count($blocks) . "\n";
echo "Blocks with @elseif/@else: " . count(array_filter($blocks, function($b) { return $b['has_elseif'] || $b['has_else']; })) . "\n";
echo "Problematic blocks: " . count($problematicBlocks) . "\n";
echo "Unclosed blocks: " . count($unclosedBlocks) . "\n";

if (!empty($unclosedBlocks)) {
    echo "\nUNCLOSED BLOCKS WITH @elseif/@else:\n";
    foreach ($unclosedBlocks as $block) {
        echo "  Line {$block['start_line']}: {$block['content']}\n";
        if ($block['has_elseif']) {
            echo "    - Has @elseif at lines: " . implode(', ', $block['elseif_lines']) . "\n";
        }
        if ($block['has_else']) {
            echo "    - Has @else at line: {$block['else_line']}\n";
        }
        echo "    - MISSING @endif!\n";
    }
}

echo "\nDetailed logs written to: {$logPath}\n";

