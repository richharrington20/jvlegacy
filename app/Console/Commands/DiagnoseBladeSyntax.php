<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DiagnoseBladeSyntax extends Command
{
    protected $signature = 'blade:diagnose {file=resources/views/investor/dashboard.blade.php}';
    protected $description = 'Diagnose Blade syntax errors in a template file';

    public function handle()
    {
        $filePath = $this->argument('file');
        $fullPath = base_path($filePath);
        
        if (!file_exists($fullPath)) {
            $this->error("File not found: {$fullPath}");
            return 1;
        }

        $content = file_get_contents($fullPath);
        $lines = explode("\n", $content);
        
        $logPath = base_path('.cursor/debug.log');
        $logFile = fopen($logPath, 'a');
        
        $log = function($hypothesisId, $message, $data = []) use ($logFile) {
            $entry = json_encode([
                'id' => 'log_' . time() . '_' . uniqid(),
                'timestamp' => (int)(microtime(true) * 1000),
                'location' => 'DiagnoseBladeSyntax.php',
                'message' => $message,
                'data' => $data,
                'sessionId' => 'debug-session',
                'runId' => 'diagnosis',
                'hypothesisId' => $hypothesisId,
            ]) . "\n";
            fwrite($logFile, $entry);
        };

        // Hypothesis A: Unbalanced @if/@endif count
        $ifCount = preg_match_all('/@if\s*\(/', $content);
        $endifCount = preg_match_all('/@endif/', $content);
        $elseifCount = preg_match_all('/@elseif\s*\(/', $content);
        $elseCount = preg_match_all('/@else\b/', $content) - $elseifCount; // Exclude @elseif
        
        $log('A', 'Directive counts', [
            'if' => $ifCount,
            'endif' => $endifCount,
            'elseif' => $elseifCount,
            'else' => $elseCount,
            'total_opens' => $ifCount,
            'total_closes' => $endifCount,
            'balanced' => $ifCount === $endifCount,
        ]);

        // Hypothesis B: @if with syntax errors in condition
        $stack = [];
        $lineNum = 1;
        $syntaxErrors = [];
        
        foreach ($lines as $line) {
            if (preg_match('/@if\s*\(/', $line)) {
                // Check if condition is properly closed
                $openParens = substr_count($line, '(');
                $closeParens = substr_count($line, ')');
                
                // Check if it spans multiple lines
                $nextLines = array_slice($lines, $lineNum - 1, 3);
                $combined = implode(' ', $nextLines);
                $totalOpen = substr_count($combined, '(');
                $totalClose = substr_count($combined, ')');
                
                if ($totalOpen > $totalClose) {
                    // Check if it's closed within reasonable distance
                    $checkLines = array_slice($lines, $lineNum - 1, 10);
                    $checkCombined = implode(' ', $checkLines);
                    $checkOpen = substr_count($checkCombined, '(');
                    $checkClose = substr_count($checkCombined, ')');
                    
                    if ($checkOpen !== $checkClose) {
                        $syntaxErrors[] = [
                            'line' => $lineNum,
                            'content' => trim($line),
                            'open' => $checkOpen,
                            'close' => $checkClose,
                        ];
                    }
                }
                
                $stack[] = ['line' => $lineNum, 'content' => trim($line)];
            }
            
            if (preg_match('/@endif/', $line)) {
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
        
        $log('B', 'Syntax errors in @if conditions', [
            'errors' => $syntaxErrors,
            'unclosed_count' => count($stack),
            'unclosed' => $stack,
        ]);

        // Hypothesis C: Blade directives inside @php blocks
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
        
        $log('C', 'Blade directives in @php blocks', [
            'php_blocks' => $phpBlocks,
            'blade_directives_in_php' => $bladeInPhp,
        ]);

        // Hypothesis D: Character encoding or hidden characters
        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        $hasBom = substr($content, 0, 3) === "\xEF\xBB\xBF";
        $controlChars = [];
        
        foreach ($lines as $num => $line) {
            if (preg_match('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/', $line)) {
                $controlChars[] = $num + 1;
            }
        }
        
        $log('D', 'Character encoding issues', [
            'encoding' => $encoding,
            'has_bom' => $hasBom,
            'control_chars_lines' => array_slice($controlChars, 0, 10),
        ]);

        // Hypothesis E: Multi-line @if conditions not properly closed
        $multilineIfs = [];
        $lineNum = 1;
        
        foreach ($lines as $line) {
            if (preg_match('/@if\s*\(/', $line)) {
                $openParens = substr_count($line, '(');
                $closeParens = substr_count($line, ')');
                
                if ($openParens > $closeParens) {
                    // Check next few lines
                    $nextLines = array_slice($lines, $lineNum - 1, 5);
                    $combined = implode(' ', $nextLines);
                    $totalOpen = substr_count($combined, '(');
                    $totalClose = substr_count($combined, ')');
                    
                    if ($totalOpen !== $totalClose) {
                        $multilineIfs[] = [
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
        
        $log('E', 'Multi-line @if conditions', [
            'multiline_ifs' => $multilineIfs,
        ]);

        // Hypothesis F: Check compiled view if it exists
        $compiledPath = storage_path('framework/views');
        $compiledFiles = [];
        if (is_dir($compiledPath)) {
            $files = glob($compiledPath . '/*.php');
            foreach ($files as $file) {
                $fileContent = file_get_contents($file);
                if (strpos($fileContent, 'Welcome, {{ $account->name }}') !== false) {
                    $compiledFiles[] = [
                        'path' => $file,
                        'size' => filesize($file),
                        'modified' => filemtime($file),
                    ];
                }
            }
        }
        
        $log('F', 'Compiled view files', [
            'compiled_path' => $compiledPath,
            'found_files' => $compiledFiles,
        ]);

        fclose($logFile);
        
        $this->info("Diagnosis complete. Check .cursor/debug.log for details.");
        $this->table(
            ['Hypothesis', 'Status'],
            [
                ['A: Unbalanced @if/@endif', $ifCount === $endifCount ? 'PASS' : 'FAIL'],
                ['B: Syntax errors in conditions', empty($syntaxErrors) ? 'PASS' : 'FAIL'],
                ['C: Blade in @php blocks', empty($bladeInPhp) ? 'PASS' : 'FAIL'],
                ['D: Encoding issues', $encoding === 'UTF-8' && !$hasBom ? 'PASS' : 'CHECK'],
                ['E: Multi-line @if issues', empty($multilineIfs) ? 'PASS' : 'FAIL'],
            ]
        );
        
        if ($ifCount !== $endifCount) {
            $this->error("Mismatch: {$ifCount} @if vs {$endifCount} @endif");
        }
        
        if (!empty($stack)) {
            $this->error("Unclosed @if statements found:");
            foreach ($stack as $item) {
                $this->line("  Line {$item['line']}: {$item['content']}");
            }
        }
        
        return 0;
    }
}

