<?php
/**
 * Check the nested @if structure around lines 1257-1349
 */

$filePath = __DIR__ . '/resources/views/investor/dashboard.blade.php';
$content = file_get_contents($filePath);
$lines = explode("\n", $content);

echo "Checking nested structure around lines 1257-1349...\n\n";

$stack = [];
$lineNum = 1;

foreach ($lines as $line) {
    if ($lineNum >= 1250 && $lineNum <= 1360) {
        if (preg_match('/@if\s*\(/', $line)) {
            $stack[] = ['line' => $lineNum, 'content' => trim($line), 'type' => 'if'];
            echo "Line {$lineNum}: @if - Stack size: " . count($stack) . "\n";
        }
        if (preg_match('/@elseif\s*\(/', $line)) {
            echo "Line {$lineNum}: @elseif - Stack size: " . count($stack) . "\n";
        }
        if (preg_match('/@else\b/', $line) && !preg_match('/@elseif/', $line)) {
            echo "Line {$lineNum}: @else - Stack size: " . count($stack) . "\n";
        }
        if (preg_match('/@endif/', $line)) {
            if (!empty($stack)) {
                $popped = array_pop($stack);
                echo "Line {$lineNum}: @endif - Closes line {$popped['line']} - Stack size: " . count($stack) . "\n";
            } else {
                echo "Line {$lineNum}: @endif - ERROR: No matching @if!\n";
            }
        }
    }
    $lineNum++;
}

if (!empty($stack)) {
    echo "\nUNCLOSED @if STATEMENTS:\n";
    foreach ($stack as $item) {
        echo "  Line {$item['line']}: {$item['content']}\n";
    }
} else {
    echo "\nAll @if statements are properly closed!\n";
}

