<?php
/**
 * Standalone Blade syntax diagnostic script
 * Run: php diagnose_blade.php
 */

$filePath = __DIR__ . '/resources/views/investor/dashboard.blade.php';
$logPath = __DIR__ . '/.cursor/debug.log';

if (!file_exists($filePath)) {
    die("File not found: {$filePath}\n");
}

$content = file_get_contents($filePath);
$lines = explode("\n", $content);

// Ensure log directory exists
$logDir = dirname($logPath);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$logFile = fopen($logPath, 'w'); // Clear and write

$log = function($hypothesisId, $message, $data = []) use ($logFile) {
    $entry = json_encode([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => (int)(microtime(true) * 1000),
        'location' => 'diagnose_blade.php',
        'message' => $message,
        'data' => $data,
        'sessionId' => 'debug-session',
        'runId' => 'diagnosis',
        'hypothesisId' => $hypothesisId,
    ], JSON_PRETTY_PRINT) . "\n";
    fwrite($logFile, $entry);
};

echo "Diagnosing Blade syntax...\n";

// Hypothesis A: Unbalanced @if/@endif count
$ifCount = preg_match_all('/@if\s*\(/', $content);
$endifCount = preg_match_all('/@endif/', $content);
$elseifCount = preg_match_all('/@elseif\s*\(/', $content);
$elseCount = preg_match_all('/@else\b/', $content) - $elseifCount;

$log('A', 'Directive counts', [
    'if' => $ifCount,
    'endif' => $endifCount,
    'elseif' => $elseifCount,
    'else' => $elseCount,
    'balanced' => $ifCount === $endifCount,
]);

// Hypothesis B: Track @if/@endif stack to find unclosed
$stack = [];
$lineNum = 1;
$syntaxErrors = [];
$ifLocations = [];
$endifLocations = [];

foreach ($lines as $line) {
    if (preg_match('/@if\s*\(/', $line)) {
        $stack[] = ['line' => $lineNum, 'content' => trim($line)];
        $ifLocations[] = $lineNum;
    }
    
    if (preg_match('/@endif/', $line)) {
        $endifLocations[] = $lineNum;
        if (empty($stack)) {
            $syntaxErrors[] = [
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

$log('B', 'Stack analysis', [
    'if_locations' => $ifLocations,
    'endif_locations' => $endifLocations,
    'unclosed_count' => count($stack),
    'unclosed' => $stack,
    'errors' => $syntaxErrors,
]);

// Hypothesis C: Check for @if with syntax errors in condition
$conditionErrors = [];
$lineNum = 1;

foreach ($lines as $line) {
    if (preg_match('/@if\s*\(/', $line)) {
        // Check if condition is properly closed on same line
        $openParens = substr_count($line, '(');
        $closeParens = substr_count($line, ')');
        
        if ($openParens > $closeParens) {
            // Check if closed within next 5 lines
            $nextLines = array_slice($lines, $lineNum - 1, 5);
            $combined = implode(' ', $nextLines);
            $totalOpen = substr_count($combined, '(');
            $totalClose = substr_count($combined, ')');
            
            if ($totalOpen !== $totalClose) {
                $conditionErrors[] = [
                    'line' => $lineNum,
                    'content' => trim($line),
                    'open' => $totalOpen,
                    'close' => $totalClose,
                ];
            }
        }
    }
    $lineNum++;
}

$log('C', 'Condition syntax errors', [
    'errors' => $conditionErrors,
]);

// Hypothesis D: Blade directives inside @php blocks
$inPhp = false;
$phpBlocks = [];
$bladeInPhp = [];
$lineNum = 1;

foreach ($lines as $line) {
    if (preg_match('/@php/', $line)) {
        $inPhp = true;
        $phpStart = $lineNum;
    }
    
    if (preg_match('/@endphp/', $line)) {
        if ($inPhp) {
            $phpBlocks[] = ['start' => $phpStart, 'end' => $lineNum];
        }
        $inPhp = false;
    }
    
    if ($inPhp) {
        if (preg_match('/@(if|endif|foreach|endforeach|elseif|else|section|endsection|push|endpush)\b/', $line, $matches)) {
            $bladeInPhp[] = [
                'line' => $lineNum,
                'directive' => $matches[1],
                'content' => trim($line),
            ];
        }
    }
    
    $lineNum++;
}

$log('D', 'Blade directives in @php blocks', [
    'php_blocks' => $phpBlocks,
    'blade_directives_in_php' => $bladeInPhp,
]);

// Hypothesis E: Character encoding issues
$encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
$hasBom = substr($content, 0, 3) === "\xEF\xBB\xBF";
$controlChars = [];

foreach ($lines as $num => $line) {
    if (preg_match('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/', $line)) {
        $controlChars[] = $num + 1;
    }
}

$log('E', 'Character encoding', [
    'encoding' => $encoding,
    'has_bom' => $hasBom,
    'control_chars_lines' => array_slice($controlChars, 0, 10),
]);

// Hypothesis F: Check file structure - @section/@push order
$sectionLine = null;
$pushLine = null;
$endpushLine = null;
$endsectionLine = null;
$lineNum = 1;

foreach ($lines as $line) {
    if (preg_match('/@section\s*\(/', $line)) {
        $sectionLine = $lineNum;
    }
    if (preg_match('/@push\s*\(/', $line)) {
        $pushLine = $lineNum;
    }
    if (preg_match('/@endpush/', $line)) {
        $endpushLine = $lineNum;
    }
    if (preg_match('/@endsection/', $line)) {
        $endsectionLine = $lineNum;
    }
    $lineNum++;
}

$log('F', 'File structure', [
    'section_line' => $sectionLine,
    'push_line' => $pushLine,
    'endpush_line' => $endpushLine,
    'endsection_line' => $endsectionLine,
    'total_lines' => count($lines),
    'structure_valid' => $sectionLine && $pushLine && $endpushLine && $endsectionLine && 
                         $sectionLine < $pushLine && $pushLine < $endpushLine && 
                         $endpushLine < $endsectionLine,
]);

fclose($logFile);

echo "\n=== DIAGNOSIS RESULTS ===\n";
echo "Hypothesis A (Counts): " . ($ifCount === $endifCount ? "PASS" : "FAIL") . " ({$ifCount} @if, {$endifCount} @endif)\n";
echo "Hypothesis B (Stack): " . (empty($stack) ? "PASS" : "FAIL") . " (" . count($stack) . " unclosed)\n";
echo "Hypothesis C (Conditions): " . (empty($conditionErrors) ? "PASS" : "FAIL") . " (" . count($conditionErrors) . " errors)\n";
echo "Hypothesis D (PHP blocks): " . (empty($bladeInPhp) ? "PASS" : "FAIL") . " (" . count($bladeInPhp) . " directives)\n";
echo "Hypothesis E (Encoding): " . ($encoding === 'UTF-8' && !$hasBom ? "PASS" : "CHECK") . " ({$encoding})\n";
echo "Hypothesis F (Structure): " . ($sectionLine && $pushLine && $endpushLine && $endsectionLine ? "PASS" : "FAIL") . "\n";

if (!empty($stack)) {
    echo "\nUNCLOSED @if STATEMENTS:\n";
    foreach ($stack as $item) {
        echo "  Line {$item['line']}: {$item['content']}\n";
    }
}

echo "\nDetailed logs written to: {$logPath}\n";

