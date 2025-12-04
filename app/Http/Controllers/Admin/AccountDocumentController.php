<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AccountDocumentController extends Controller
{
    public function index($accountId)
    {
        try {
            $account = Account::findOrFail($accountId);
            $documents = AccountDocument::where('account_id', $account->id)
                ->where('deleted', false)
                ->orderBy('created_on', 'desc')
                ->get()
                ->map(function($doc) {
                    return [
                        'id' => $doc->id,
                        'name' => $doc->name,
                        'file_type' => $doc->file_type,
                        'file_size' => $doc->file_size,
                        'category' => $doc->category,
                        'url' => $doc->url,
                    ];
                });

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json($documents);
            }

            return $documents;
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['error' => 'Account not found'], 404);
            }
            return redirect()->back()->with('error', 'Account not found.');
        }
    }

    public function store(Request $request, $accountId)
    {
        $account = Account::findOrFail($accountId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'nullable|file|max:10240', // 10MB max, now optional
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_private' => 'nullable|boolean',
        ]);

        // Only process if a file was uploaded
        if (!$request->hasFile('file')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file was uploaded. Please select a file to upload.',
                ], 422);
            }
            return redirect()->back()->with('error', 'No file was uploaded. Please select a file to upload.');
        }

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::slug($validated['name']) . '_' . time() . '.' . $extension;
        $filePath = $file->storeAs('account-documents/' . $account->id, $fileName, 'public');

        $document = AccountDocument::create([
            'account_id' => $account->id,
            'name' => $validated['name'],
            'file_path' => $filePath,
            'file_type' => $extension,
            'file_size' => $file->getSize(),
            'category' => $validated['category'] ?? 'general',
            'description' => $validated['description'] ?? null,
            'is_private' => $validated['is_private'] ?? true,
            'uploaded_by' => auth()->id(),
            'created_on' => now(),
            'updated_on' => now(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully.',
                'document' => [
                    'id' => $document->id,
                    'name' => $document->name,
                    'file_type' => $document->file_type,
                    'file_size' => $document->file_size,
                    'category' => $document->category,
                    'url' => $document->url,
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Document uploaded successfully.');
    }

    public function destroy($accountId, $documentId)
    {
        $document = AccountDocument::where('account_id', $accountId)
            ->where('id', $documentId)
            ->firstOrFail();
        
        // Soft delete
        $document->deleted = true;
        $document->updated_on = now();
        $document->save();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Document deleted successfully.']);
        }

        return redirect()->back()->with('success', 'Document deleted successfully.');
    }
}

