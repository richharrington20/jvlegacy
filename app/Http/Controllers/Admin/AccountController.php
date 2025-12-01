<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeInvestorMail;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\DocumentEmailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
        
        // Load documents if table exists
        try {
            $account->load('documents');
        } catch (\Exception $e) {
            // Table doesn't exist yet, use empty collection
            $account->setRelation('documents', collect());
        }

        $entity = $account->person ?? $account->company;

        // Group investments by project for overview
        $projectInvestments = $account->investments()
            ->with('project')
            ->get()
            ->groupBy(function ($investment) {
                return $investment->project_id ?? 'no-project';
            })
            ->map(function ($investments, $projectId) {
                if ($projectId === 'no-project') {
                    return [
                        'project' => null,
                        'project_id' => null,
                        'project_name' => 'No Project Assigned',
                        'total_invested' => $investments->sum('amount'),
                        'total_paid' => $investments->where('paid', 1)->sum('amount'),
                        'total_unpaid' => $investments->where('paid', 0)->sum('amount'),
                        'investment_count' => $investments->count(),
                        'paid_count' => $investments->where('paid', 1)->count(),
                        'unpaid_count' => $investments->where('paid', 0)->count(),
                        'investments' => $investments,
                    ];
                }
                
                $project = $investments->first()->project;
                return [
                    'project' => $project,
                    'project_id' => $project->project_id ?? null,
                    'project_name' => $project->name ?? 'Unknown Project',
                    'total_invested' => $investments->sum('amount'),
                    'total_paid' => $investments->where('paid', 1)->sum('amount'),
                    'total_unpaid' => $investments->where('paid', 0)->sum('amount'),
                    'investment_count' => $investments->count(),
                    'paid_count' => $investments->where('paid', 1)->count(),
                    'unpaid_count' => $investments->where('paid', 0)->count(),
                    'investments' => $investments,
                ];
            })
            ->values();

        // Get available projects for upsell
        $availableProjects = \App\Models\Project::whereIn('status', [
            \App\Models\Project::STATUS_PENDING_EQUITY,
            \App\Models\Project::STATUS_PENDING_PURCHASE,
            \App\Models\Project::STATUS_PENDING_CONSTRUCTION,
            \App\Models\Project::STATUS_UNDER_CONSTRUCTION,
        ])
        ->orderByDesc('launched_on')
        ->limit(3)
        ->get();

        // Get account documents (if table exists)
        try {
            $accountDocuments = $account->documents;
        } catch (\Exception $e) {
            $accountDocuments = collect();
        }

        // Get email history (document emails sent to this account)
        try {
            $emailHistory = DocumentEmailLog::where('account_id', $account->id)
                ->with('project')
                ->orderByDesc('sent_at')
                ->limit(50)
                ->get();
        } catch (\Exception $e) {
            $emailHistory = collect();
        }

        // Calculate stats
        $totalInvested = $account->investments()->sum('amount');
        $totalPaid = $account->investments()->where('paid', 1)->sum('amount');
        $totalUnpaid = $account->investments()->where('paid', 0)->sum('amount');
        $investmentCount = $account->investments()->count();

        return view('admin.accounts.show', compact(
            'account', 
            'entity', 
            'projectInvestments', 
            'availableProjects', 
            'accountDocuments',
            'emailHistory',
            'totalInvested',
            'totalPaid',
            'totalUnpaid',
            'investmentCount'
        ));
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

    public function create()
    {
        $accountTypes = AccountType::orderBy('name')->get();
        return view('admin.accounts.create', compact('accountTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:legacy.accounts,email',
            'password' => 'required|string|min:8|confirmed',
            'type_id' => 'required|exists:legacy.account_types,id',
            'account_type' => 'required|in:person,company',
            // Person fields
            'first_name' => 'required_if:account_type,person|nullable|string|max:255',
            'last_name' => 'required_if:account_type,person|nullable|string|max:255',
            'telephone_number' => 'nullable|string|max:255',
            // Company fields
            'company_name' => 'required_if:account_type,company|nullable|string|max:255',
        ]);

        \DB::connection('legacy')->beginTransaction();
        try {
            $personId = null;
            $companyId = null;

            if ($request->account_type === 'person') {
                $person = \App\Models\Person::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'telephone_number' => $request->telephone_number,
                    'email' => $request->email,
                    'created_on' => now(),
                    'updated_on' => now(),
                ]);
                $personId = $person->id;
            } elseif ($request->account_type === 'company') {
                $company = \App\Models\Company::create([
                    'name' => $request->company_name,
                    'created_on' => now(),
                    'updated_on' => now(),
                ]);
                $companyId = $company->id;
            }

            $account = Account::create([
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'type_id' => $request->type_id,
                'person_id' => $personId,
                'company_id' => $companyId,
                'created_on' => now(),
                'updated_on' => now(),
                'status' => 1,
                'deleted' => 0,
            ]);

            \DB::connection('legacy')->commit();

            // Send welcome email
            try {
                Mail::to($account->email)->send(new WelcomeInvestorMail($account));
            } catch (\Exception $e) {
                \Log::error('Failed to send welcome email: ' . $e->getMessage());
            }

            return redirect()->route('admin.accounts.show', $account->id)
                ->with('status', 'Account created successfully!');
        } catch (\Exception $e) {
            \DB::connection('legacy')->rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create account: ' . $e->getMessage()]);
        }
    }
}
