<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InvestorProfileController extends Controller
{
    public function update(Request $request)
    {
        $account = auth('investor')->user();
        
        $request->validate([
            'email' => 'required|email|unique:legacy.accounts,email,' . $account->id,
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'telephone_number' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $account->email = $request->email;
        if ($request->filled('password')) {
            $account->password = Hash::make($request->password);
        }
        $account->save();

        if ($account->person) {
            $person = $account->person;
            if ($request->filled('first_name')) {
                $person->first_name = $request->first_name;
            }
            if ($request->filled('last_name')) {
                $person->last_name = $request->last_name;
            }
            if ($request->filled('telephone_number')) {
                $person->telephone_number = $request->telephone_number;
            }
            $person->email = $request->email; // Sync email
            $person->save();
        } elseif ($account->company) {
            $company = $account->company;
            if ($request->filled('company_name')) {
                $company->name = $request->company_name;
            }
            $company->save();
        }

        return redirect()->back()->with('status', 'Profile updated successfully!');
    }
}

