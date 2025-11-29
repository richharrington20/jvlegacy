<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = Account::with('person', 'company')
            ->withCount([
                'investments as total_paid' => fn ($q) => $q->where('paid', 1),
                'investments as total_unpaid' => fn ($q) => $q->where('paid', 0),
            ]);

        // Filter by company name, person name, or account email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('company', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%$search%");
                })
                ->orWhereHas('person', function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%$search%")
                        ->orWhere('last_name', 'like', "%$search%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%$search%"]);
                })
                ->orWhere('email', 'like', "%$search%");
            });
        }

        // Filter by type_id
        if ($request->filled('type_filter')) {
            $query->where('type_id', $request->type_filter);
        }

        $accounts = $query->paginate(25)->withQueryString();
        $accountTypes = AccountType::orderBy('name')->get();

        return view('admin.accounts.index', compact('accounts', 'accountTypes'));
    }

    public function show($id)
    {
        $account = Account::on('legacy')->with(['person', 'company', 'investments.project'])->findOrFail($id);

        $entity = $account->person ?? $account->company;

        return view('admin.accounts.show', compact('account', 'entity'));
    }

    public function updateType(Request $request, $id)
    {
        $request->validate([
            'type_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!\App\Models\AccountType::on('legacy')->where('id', $value)->exists()) {
                        $fail('The selected account type is invalid.');
                    }
                }
            ],
        ]);
        $account = Account::on('legacy')->findOrFail($id);
        $account->type_id = $request->type_id;
        $account->save();
        return redirect()->back()->with('status', 'Account type updated!');
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);
        $account = Account::on('legacy')->findOrFail($id);
        $account->password = bcrypt($request->password);
        $account->save();
        return redirect()->back()->with('status', 'Password updated!');
    }

    public function update(Request $request, $id)
    {
        $account = Account::on('legacy')->with('person', 'company')->findOrFail($id);
        
        $request->validate([
            'email' => 'required|email|unique:legacy.accounts,email,' . $id,
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'telephone_number' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
        ]);

        $account->email = $request->email;
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

        return redirect()->back()->with('status', 'Account details updated successfully!');
    }
}
