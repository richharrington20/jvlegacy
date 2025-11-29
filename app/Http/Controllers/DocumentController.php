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
        
        $error = false;
        $hashParts = explode('o', $hash);

        if (count($hashParts) !== 3) {
            // Try alternative separators or check if it's a different format
            \Log::warning('Document hash format invalid', ['hash' => $hash, 'parts' => count($hashParts)]);
            abort(404, 'Invalid document link format');
        }

        [$authHash, $timestamp, $documentHash] = $hashParts;

        // Validate auth hash
        $expectedAuthHash = sha1('jaevee');
        if ($authHash !== $expectedAuthHash) {
            \Log::warning('Document auth hash mismatch', ['expected' => $expectedAuthHash, 'got' => $authHash]);
            $error = true;
        }

        // Validate timestamp (expires after 1 hour)
        try {
            $now = Carbon::now();
            $expiry = Carbon::createFromTimestamp((int)$timestamp)->addHour();

            if ($now->gt($expiry)) {
                \Log::warning('Document link expired', ['timestamp' => $timestamp, 'expiry' => $expiry, 'now' => $now]);
                $error = true;
            }
        } catch (\Exception $e) {
            \Log::warning('Document timestamp invalid', ['timestamp' => $timestamp, 'error' => $e->getMessage()]);
            $error = true;
        }

        if ($error) {
            abort(404, 'Document link has expired or is invalid');
        }

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
            // Alternative: might be in a shared location
            '/var/www/jvsystem/App/Cache/Docs/Investor/' . $document->proposal_id . '/' . $document->hash . '.pdf',
            '/home/*/jvsystem/App/Cache/Docs/Investor/' . $document->proposal_id . '/' . $document->hash . '.pdf',
            // If stored in Laravel storage
            storage_path('app/documents/investor/' . $document->proposal_id . '/' . $document->hash . '.pdf'),
        ]);

        $filePath = null;
        $checkedPaths = [];
        foreach ($possiblePaths as $path) {
            $checkedPaths[] = $path;
            if (file_exists($path) && is_readable($path)) {
                $filePath = $path;
                break;
            }
        }

        if (!$filePath || !file_exists($filePath)) {
            \Log::error('Document file not found', [
                'document_id' => $document->id,
                'proposal_id' => $document->proposal_id,
                'hash' => $document->hash,
                'checked_paths' => $checkedPaths,
            ]);
            abort(404, 'Document file not found on server. Checked: ' . implode(', ', array_slice($checkedPaths, 0, 3)));
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

