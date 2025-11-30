<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AccountShareController extends Controller
{
    public function index(Request $request)
    {
        $account = $request->user('investor');
        
        // Accounts that have shared access to this account's investments
        $sharedWithMe = AccountShare::where('shared_account_id', $account->id)
            ->where('status', AccountShare::STATUS_ACTIVE)
            ->where('deleted', false)
            ->with('primaryAccount.person', 'primaryAccount.company')
            ->get();

        // Accounts this account has shared access with
        $sharedByMe = AccountShare::where('primary_account_id', $account->id)
            ->where('deleted', false)
            ->with('sharedAccount.person', 'sharedAccount.company')
            ->get();

        return response()->json([
            'shared_with_me' => $sharedWithMe,
            'shared_by_me' => $sharedByMe,
        ]);
    }

    public function store(Request $request)
    {
        $account = $request->user('investor');

        $validated = $request->validate([
            'email' => 'required|email|exists:legacy.accounts,email',
        ]);

        // Find the account to share with
        $sharedAccount = Account::where('email', $validated['email'])->first();

        if (!$sharedAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found with that email address.',
            ], 404);
        }

        // Can't share with yourself
        if ($sharedAccount->id === $account->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot share access with your own account.',
            ], 400);
        }

        // Check if share already exists
        $existingShare = AccountShare::where('primary_account_id', $account->id)
            ->where('shared_account_id', $sharedAccount->id)
            ->where('deleted', false)
            ->first();

        if ($existingShare) {
            if ($existingShare->status === AccountShare::STATUS_ACTIVE) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already shared access with this account.',
                ], 400);
            } elseif ($existingShare->status === AccountShare::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'A pending invitation already exists for this account.',
                ], 400);
            }
        }

        // Create the share (auto-accept for now, can add email invitation later)
        $share = AccountShare::create([
            'primary_account_id' => $account->id,
            'shared_account_id' => $sharedAccount->id,
            'status' => AccountShare::STATUS_ACTIVE,
            'invited_by' => $account->id,
            'invited_on' => now(),
            'accepted_on' => now(),
            'created_on' => now(),
            'updated_on' => now(),
        ]);

        // Send notification email (optional)
        try {
            Mail::send('emails.account_share_notification', [
                'primaryAccount' => $account,
                'sharedAccount' => $sharedAccount,
                'share' => $share,
            ], function ($message) use ($sharedAccount) {
                $message->to($sharedAccount->email, $sharedAccount->name)
                    ->subject('Account Access Shared - JaeVee');
            });
        } catch (\Exception $e) {
            Log::error('Failed to send account share notification email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Access shared successfully!',
            'share' => $share->load('sharedAccount.person', 'sharedAccount.company'),
        ]);
    }

    public function destroy(Request $request, $shareId)
    {
        $account = $request->user('investor');

        $share = AccountShare::where('id', $shareId)
            ->where('primary_account_id', $account->id)
            ->where('deleted', false)
            ->firstOrFail();

        $share->update([
            'status' => AccountShare::STATUS_REVOKED,
            'revoked_on' => now(),
            'updated_on' => now(),
            'deleted' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Access revoked successfully.',
        ]);
    }

    public function removeSharedAccess(Request $request, $shareId)
    {
        $account = $request->user('investor');

        $share = AccountShare::where('id', $shareId)
            ->where('shared_account_id', $account->id)
            ->where('deleted', false)
            ->firstOrFail();

        $share->update([
            'status' => AccountShare::STATUS_REVOKED,
            'revoked_on' => now(),
            'updated_on' => now(),
            'deleted' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shared access removed successfully.',
        ]);
    }
}

