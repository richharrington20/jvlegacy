<?php
/**
 * Deep parse check - simulate how Blade compiler parses @if statements
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
        'location' => 'deep_parse_check.php',
        'message' => $message,
        'data' => $data,
        'sessionId' => 'debug-session',
        'runId' => 'deep-parse',
        'hypothesisId' => $hypothesisId,
    ], JSON_PRETTY_PRINT) . "\n";
    fwrite($logFile, $entry);
};

echo "Deep parsing check...\n\n";

// Hypothesis O: Simulate Blade compiler's @if parsing
// Blade converts @if(condition) to <?php if(condition): ?>
// So we need to check if all conditions are properly formed

$stack = [];
$lineNum = 1;
$parsingIssues = [];

foreach ($lines as $line) {
    // Match @if with opening parenthesis
    if (preg_match('/@if\s*\(/', $line, $matches, PREG_OFFSET_CAPTURE)) {
        $ifPos = $matches[0][1];
        $afterIf = substr($line, $ifPos + strlen($matches[0][0]));
        
        // Count parentheses to find where condition ends
        $openCount = 1; // We already have the opening (
        $closeCount = 0;
        $pos = 0;
        $inString = false;
        $stringChar = null;
        $escaped = false;
        
        while ($pos < strlen($afterIf) && $openCount > $closeCount) {
            $char = $afterIf[$pos];
            
            if ($escaped) {
                $escaped = false;
                $pos++;
                continue;
            }
            
            if ($char === '\\') {
                $escaped = true;
                $pos++;
                continue;
            }
            
            if (!$inString && ($char === '"' || $char === "'")) {
                $inString = true;
                $stringChar = $char;
            } elseif ($inString && $char === $stringChar) {
                $inString = false;
                $stringChar = null;
            } elseif (!$inString) {
                if ($char === '(') $openCount++;
                if ($char === ')') $closeCount++;
            }
            
            $pos++;
        }
        
        if ($openCount !== $closeCount) {
            // Condition might span multiple lines
            $nextLines = array_slice($lines, $lineNum - 1, 10);
            $combined = implode(' ', $nextLines);
            
            // Re-check with combined lines
            if (preg_match('/@if\s*\(/', $combined, $m, PREG_OFFSET_CAPTURE)) {
                $afterIf2 = substr($combined, $m[0][1] + strlen($m[0][0]));
                $openCount2 = substr_count($afterIf2, '(');
                $closeCount2 = substr_count($afterIf2, ')');
                
                if ($openCount2 !== $closeCount2) {
                    $parsingIssues[] = [
                        'line' => $lineNum,
                        'content' => trim($line),
                        'issue' => 'unbalanced_parentheses_multi_line',
                        'open' => $openCount2 + 1,
                        'close' => $closeCount2,
                    ];
                }
            }
        }
        
        $stack[] = ['line' => $lineNum, 'content' => trim($line)];
    }
    
    if (preg_match('/@endif/', $line)) {
        if (empty($stack)) {
            $parsingIssues[] = [
                'line' => $lineNum,
                'type' => 'extra_endif',
                'content' => trim($line),
            ];
        } else {
            array_pop($stack);
        }
    }
    
    $lineNum++;
}

$log('O', 'Deep parsing analysis', [
    'parsing_issues' => $parsingIssues,
    'unclosed_count' => count($stack),
    'unclosed' => $stack,
]);

// Hypothesis P: Check if there's an @if that's commented out or in a string
$lineNum = 1;
$commentedIfs = [];
$stringIfs = [];

foreach ($lines as $line) {
    // Check for @if in HTML comments
    if (preg_match('/<!--.*@if.*-->/', $line)) {
        $commentedIfs[] = $lineNum;
    }
    
    // Check for @if in JavaScript strings (inside <script> tags)
    // This is a simplified check
    if (preg_match('/["\'].*@if.*["\']/', $line)) {
        $stringIfs[] = $lineNum;
    }
    
    $lineNum++;
}

$log('P', 'Commented or string @if', [
    'commented_ifs' => $commentedIfs,
    'string_ifs' => $stringIfs,
]);

fclose($logFile);

echo "=== RESULTS ===\n";
echo "Parsing issues: " . count($parsingIssues) . "\n";
echo "Unclosed @if: " . count($stack) . "\n";
echo "Commented @if: " . count($commentedIfs) . "\n";
echo "String @if: " . count($stringIfs) . "\n";

if (!empty($stack)) {
    echo "\nUNCLOSED @if STATEMENTS:\n";
    foreach ($stack as $item) {
        echo "  Line {$item['line']}: {$item['content']}\n";
    }
}

if (!empty($parsingIssues)) {
    echo "\nPARSING ISSUES:\n";
    foreach ($parsingIssues as $issue) {
        echo "  Line {$issue['line']}: {$issue['content']}\n";
        if (isset($issue['issue'])) {
            echo "    Issue: {$issue['issue']}\n";
        }
    }
}

echo "\nLogs written to: {$logPath}\n";

