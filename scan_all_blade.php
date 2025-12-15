<?php
/**
 * Comprehensive scan of ALL Blade files in the system
 */

$logPath = __DIR__ . '/.cursor/debug.log';
$logFile = fopen($logPath, 'w');

$log = function($hypothesisId, $message, $data = []) use ($logFile) {
    $entry = json_encode([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => (int)(microtime(true) * 1000),
        'location' => 'scan_all_blade.php',
        'message' => $message,
        'data' => $data,
        'sessionId' => 'debug-session',
        'runId' => 'full-scan',
        'hypothesisId' => $hypothesisId,
    ], JSON_PRETTY_PRINT) . "\n";
    fwrite($logFile, $entry);
};

echo "Scanning ALL Blade files in the system...\n\n";

$bladeFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__ . '/resources/views')
);
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php' && 
        strpos($file->getFilename(), '.blade.php') !== false) {
        $bladeFiles[] = $file->getPathname();
    }
}

$log('SCAN', 'Found Blade files', [
    'count' => count($bladeFiles),
    'files' => array_map(function($f) { return str_replace(__DIR__ . '/', '', $f); }, $bladeFiles),
]);

$issues = [];
$targetFile = null;

foreach ($bladeFiles as $filePath) {
    $relativePath = str_replace(__DIR__ . '/', '', $filePath);
    
    if (strpos($relativePath, 'investor/dashboard') !== false) {
        $targetFile = $filePath;
    }
    
    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    
    // Check for unbalanced @if/@endif
    $ifCount = preg_match_all('/@if\s*\(/', $content);
    $endifCount = preg_match_all('/@endif/', $content);
    
    // Check for @if/@endif stack
    $stack = [];
    $lineNum = 1;
    $fileIssues = [];
    
    foreach ($lines as $line) {
        if (preg_match('/@if\s*\(/', $line)) {
            $stack[] = ['line' => $lineNum, 'file' => $relativePath, 'content' => trim($line)];
        }
        if (preg_match('/@endif/', $line)) {
            if (empty($stack)) {
                $fileIssues[] = [
                    'type' => 'extra_endif',
                    'line' => $lineNum,
                    'file' => $relativePath,
                ];
            } else {
                array_pop($stack);
            }
        }
        $lineNum++;
    }
    
    if ($ifCount !== $endifCount || !empty($stack)) {
        $issues[] = [
            'file' => $relativePath,
            'if_count' => $ifCount,
            'endif_count' => $endifCount,
            'unclosed' => $stack,
            'errors' => $fileIssues,
        ];
    }
    
    // Check for Blade directives in @php blocks
    $inPhp = false;
    $bladeInPhp = [];
    $lineNum = 1;
    
    foreach ($lines as $line) {
        if (preg_match('/@php/', $line)) {
            $inPhp = true;
        }
        if (preg_match('/@endphp/', $line)) {
            $inPhp = false;
        }
        if ($inPhp && preg_match('/@(if|endif|foreach|endforeach|elseif|else|section|endsection|push|endpush)\b/', $line, $matches)) {
            $bladeInPhp[] = [
                'line' => $lineNum,
                'directive' => $matches[1],
                'file' => $relativePath,
            ];
        }
        $lineNum++;
    }
    
    if (!empty($bladeInPhp)) {
        $issues[] = [
            'file' => $relativePath,
            'type' => 'blade_in_php',
            'directives' => $bladeInPhp,
        ];
    }
}

$log('SCAN', 'Issues found', [
    'total_issues' => count($issues),
    'issues' => $issues,
]);

// Special focus on dashboard file
if ($targetFile) {
    echo "\n=== FOCUSING ON DASHBOARD FILE ===\n";
    $content = file_get_contents($targetFile);
    $lines = explode("\n", $content);
    
    // Hypothesis K: Check for @if statements that might be parsed incorrectly
    $lineNum = 1;
    $detailedStack = [];
    
    foreach ($lines as $line) {
        // Check for @if with potential issues
        if (preg_match('/@if\s*\(/', $line)) {
            // Check if condition might span multiple lines
            $openParens = substr_count($line, '(');
            $closeParens = substr_count($line, ')');
            
            if ($openParens > $closeParens) {
                // Check next 10 lines
                $nextLines = array_slice($lines, $lineNum - 1, 10);
                $combined = implode(' ', $nextLines);
                $totalOpen = substr_count($combined, '(');
                $totalClose = substr_count($combined, ')');
                
                if ($totalOpen !== $totalClose) {
                    $detailedStack[] = [
                        'line' => $lineNum,
                        'content' => trim($line),
                        'open' => $totalOpen,
                        'close' => $totalClose,
                        'issue' => 'unbalanced_parentheses',
                    ];
                }
            }
            
            $detailedStack[] = [
                'line' => $lineNum,
                'content' => trim($line),
                'full_line' => $line,
            ];
        }
        
        if (preg_match('/@endif/', $line)) {
            if (!empty($detailedStack)) {
                array_pop($detailedStack);
            }
        }
        
        $lineNum++;
    }
    
    $log('K', 'Detailed dashboard analysis', [
        'unclosed_count' => count($detailedStack),
        'unclosed' => $detailedStack,
        'total_lines' => count($lines),
        'last_10_lines' => array_slice($lines, -10),
    ]);
    
    // Hypothesis L: Check the exact end of file
    $lastLines = array_slice($lines, -20);
    $log('L', 'End of file analysis', [
        'last_20_lines' => array_map(function($l, $i) use ($lines) {
            return [
                'line_num' => count($lines) - 20 + $i + 1,
                'content' => $l,
                'has_if' => preg_match('/@if/', $l),
                'has_endif' => preg_match('/@endif/', $l),
                'has_elseif' => preg_match('/@elseif/', $l),
                'has_else' => preg_match('/@else\b/', $l),
            ];
        }, $lastLines, array_keys($lastLines)),
    ]);
}

fclose($logFile);

echo "\n=== SCAN RESULTS ===\n";
echo "Total Blade files scanned: " . count($bladeFiles) . "\n";
echo "Files with issues: " . count($issues) . "\n";

if (!empty($issues)) {
    echo "\nISSUES FOUND:\n";
    foreach ($issues as $issue) {
        echo "  File: {$issue['file']}\n";
        if (isset($issue['if_count'])) {
            echo "    - @if count: {$issue['if_count']}, @endif count: {$issue['endif_count']}\n";
        }
        if (isset($issue['unclosed']) && !empty($issue['unclosed'])) {
            echo "    - Unclosed @if statements:\n";
            foreach ($issue['unclosed'] as $unclosed) {
                echo "      Line {$unclosed['line']}: {$unclosed['content']}\n";
            }
        }
        if (isset($issue['type']) && $issue['type'] === 'blade_in_php') {
            echo "    - Blade directives in @php blocks:\n";
            foreach ($issue['directives'] as $dir) {
                echo "      Line {$dir['line']}: @{$dir['directive']}\n";
            }
        }
    }
} else {
    echo "\nNo issues found in any Blade files!\n";
}

echo "\nDetailed logs written to: {$logPath}\n";

