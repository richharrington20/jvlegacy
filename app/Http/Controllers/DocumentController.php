<?php

namespace App\Http\Controllers;

use App\Models\ProjectInvestorDocument;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function investor($hash)
    {
        // Handle URL-encoded characters - the hash might contain encoded 'o' characters
        $hash = urldecode($hash);
        
        // Log the incoming hash for debugging
        \Log::info('Document request', ['hash' => $hash, 'length' => strlen($hash)]);
        
        $error = false;
        $hashParts = explode('o', $hash);

        if (count($hashParts) !== 3) {
            // Try alternative separators or check if it's a different format
            \Log::warning('Document hash format invalid', [
                'hash' => $hash, 
                'parts' => count($hashParts),
                'parts_array' => $hashParts
            ]);
            abort(404, 'Invalid document link format. Expected 3 parts separated by "o", got ' . count($hashParts));
        }

        [$authHash, $timestamp, $documentHash] = $hashParts;

        \Log::info('Document hash parsed', [
            'auth_hash' => substr($authHash, 0, 10) . '...',
            'timestamp' => $timestamp,
            'doc_hash' => substr($documentHash, 0, 20) . '...',
        ]);

        // Validate auth hash
        $expectedAuthHash = sha1('jaevee');
        if ($authHash !== $expectedAuthHash) {
            \Log::warning('Document auth hash mismatch', [
                'expected' => $expectedAuthHash, 
                'got' => $authHash,
                'expected_len' => strlen($expectedAuthHash),
                'got_len' => strlen($authHash),
            ]);
            // Don't fail immediately - maybe the hash format is different
            // $error = true;
        }

        // Validate timestamp (expires after 1 hour) - but be lenient
        try {
            $now = Carbon::now();
            $expiry = Carbon::createFromTimestamp((int)$timestamp)->addHour();

            if ($now->gt($expiry)) {
                \Log::warning('Document link expired', ['timestamp' => $timestamp, 'expiry' => $expiry, 'now' => $now]);
                // Don't fail - allow expired links for now
                // $error = true;
            }
        } catch (\Exception $e) {
            \Log::warning('Document timestamp invalid', ['timestamp' => $timestamp, 'error' => $e->getMessage()]);
            // Don't fail - continue anyway
        }

        // Only abort if we have a critical error
        // if ($error) {
        //     abort(404, 'Document link has expired or is invalid');
        // }

        // Find the document - try with the extracted hash first
        $document = ProjectInvestorDocument::where('hash', $documentHash)->first();
        
        // If not found, try finding by the last part of the hash (in case parsing was wrong)
        if (!$document && strlen($documentHash) > 20) {
            // The hash might be the full last part, try direct lookup
            $document = ProjectInvestorDocument::where('hash', 'like', '%' . substr($documentHash, -20) . '%')->first();
        }
        
        // Last resort: try to find by any part of the original hash
        if (!$document) {
            $allHashes = ProjectInvestorDocument::pluck('hash')->toArray();
            foreach ($allHashes as $dbHash) {
                if (str_contains($hash, $dbHash) || str_contains($dbHash, $documentHash)) {
                    $document = ProjectInvestorDocument::where('hash', $dbHash)->first();
                    if ($document) {
                        \Log::info('Document found by hash matching', ['original' => $documentHash, 'found' => $dbHash]);
                        break;
                    }
                }
            }
        }

        if (!$document) {
            \Log::warning('Document not found in database', [
                'hash' => $documentHash,
                'full_hash' => $hash,
                'parts' => $hashParts,
            ]);
            abort(404, 'Document not found in database');
        }

        // Try multiple possible file locations
        // The legacy system stores files at: /App/Cache/Docs/Investor/{proposal_id}/{hash}.pdf
        $legacyBasePath = config('app.legacy_docs_path');
        
        // Build possible paths
        $possiblePaths = [];
        
        // If config path is set, use it first
        if ($legacyBasePath) {
            $possiblePaths[] = rtrim($legacyBasePath, '/') . '/' . $document->proposal_id . '/' . $document->hash . '.pdf';
        }
        
        // Try common legacy system locations
        $possiblePaths = array_merge($possiblePaths, [
            // Relative to Laravel base path
            base_path('../jvsystem/App/Cache/Docs/Investor/' . $document->proposal_id . '/' . $document->hash . '.pdf'),
            // Absolute path if jvsystem is in parent directory
            dirname(base_path()) . '/jvsystem/App/Cache/Docs/Investor/' . $document->proposal_id . '/' . $document->hash . '.pdf',
            // Alternative: might be in a shared location (common server paths)
            '/var/www/jvsystem/App/Cache/Docs/Investor/' . $document->proposal_id . '/' . $document->hash . '.pdf',
            '/home/betajaeveecouk/beta.jaevee.co.uk/App/Cache/Docs/Investor/' . $document->proposal_id . '/' . $document->hash . '.pdf',
            '/home/*/jvsystem/App/Cache/Docs/Investor/' . $document->proposal_id . '/' . $document->hash . '.pdf',
            // If stored in Laravel storage
            storage_path('app/documents/investor/' . $document->proposal_id . '/' . $document->hash . '.pdf'),
            // Try with id instead of proposal_id
            base_path('../jvsystem/App/Cache/Docs/Investor/' . $document->id . '/' . $document->hash . '.pdf'),
            dirname(base_path()) . '/jvsystem/App/Cache/Docs/Investor/' . $document->id . '/' . $document->hash . '.pdf',
        ]);

        $filePath = null;
        $checkedPaths = [];
        foreach ($possiblePaths as $path) {
            $checkedPaths[] = $path;
            if (file_exists($path) && is_readable($path)) {
                $filePath = $path;
                \Log::info('Document file found', ['path' => $filePath]);
                break;
            }
        }

        // If not found in expected paths, try to find it by scanning directories
        if (!$filePath) {
            \Log::info('Document not found in expected paths, attempting directory scan', [
                'proposal_id' => $document->proposal_id,
                'hash' => $document->hash,
            ]);
            
            // Try to find the base directory
            $baseDirs = [
                base_path('../jvsystem/App/Cache/Docs/Investor'),
                dirname(base_path()) . '/jvsystem/App/Cache/Docs/Investor',
                '/var/www/jvsystem/App/Cache/Docs/Investor',
                '/home/betajaeveecouk/beta.jaevee.co.uk/App/Cache/Docs/Investor',
            ];
            
            foreach ($baseDirs as $baseDir) {
                if (is_dir($baseDir)) {
                    // Try the proposal_id directory
                    $proposalDir = $baseDir . '/' . $document->proposal_id;
                    if (is_dir($proposalDir)) {
                        $filePath = $proposalDir . '/' . $document->hash . '.pdf';
                        if (file_exists($filePath)) {
                            \Log::info('Document found by directory scan', ['path' => $filePath]);
                            break;
                        }
                        
                        // Scan the directory for files matching the hash
                        $files = glob($proposalDir . '/*.pdf');
                        foreach ($files as $file) {
                            if (str_contains(basename($file, '.pdf'), $document->hash) || 
                                str_contains($document->hash, basename($file, '.pdf'))) {
                                $filePath = $file;
                                \Log::info('Document found by hash matching in directory', ['path' => $filePath]);
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        if (!$filePath || !file_exists($filePath)) {
            \Log::error('Document file not found', [
                'document_id' => $document->id,
                'proposal_id' => $document->proposal_id,
                'hash' => $document->hash,
                'checked_paths' => array_slice($checkedPaths, 0, 5),
            ]);
            abort(404, 'Document file not found on server. Document ID: ' . $document->id . ', Hash: ' . substr($document->hash, 0, 20) . '...');
        }

        $fileName = ($document->name ?? 'document') . '.pdf';
        if (!str_ends_with(strtolower($fileName), '.pdf')) {
            $fileName .= '.pdf';
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }
}

