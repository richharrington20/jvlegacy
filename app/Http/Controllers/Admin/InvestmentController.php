<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investments;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvestmentController extends Controller
{
    private function filteredInvestmentsQuery(Request $request)
    {
        $query = Investments::with(['account.person', 'account.company', 'project']);

        // Apply project filter - need to find project by project_id (external) and use its id (internal)
        if ($request->filled('project_id')) {
            $project = Project::where('project_id', $request->project_id)->first();
            if ($project) {
                $query->where('project_id', $project->id); // Use internal id
            }
        }

        // Apply paid filter
        if ($request->has('paid') && $request->paid !== '') {
            $query->where('paid', $request->paid);
        }

        // Apply name search
        if ($request->filled('search')) {
            $query->whereHas('account', function ($q) use ($request) {
                $q->whereHas('person', fn ($q) => $q->where('first_name', 'like', "%{$request->search}%")
                    ->orWhere('last_name', 'like', "%{$request->search}%"))
                    ->orWhereHas('company', fn ($q) => $q->where('name', 'like', "%{$request->search}%"));
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->filteredInvestmentsQuery($request);

        $investments = $query->orderByDesc('id')->paginate(25)->withQueryString();

        $projects = Project::whereNotNull('project_id')
            ->where('project_id', '!=', '')
            ->orderBy('project_id')
            ->get(['project_id', 'name']);

        return view('admin.investments.index', compact('investments', 'projects'));
    }


    public function export(Request $request): StreamedResponse
    {
        $query = $this->filteredInvestmentsQuery($request);

        $investments = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=investments.csv',
        ];

        return response()->streamDownload(function () use ($investments) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'ID', 'Project ID', 'Project Name', 'Account Name', 'Transfer ID', 'Pay In ID',
                'Amount (£)', 'Type', 'Paid', 'Paid On', 'Reserved Until'
            ]);

            foreach ($investments as $inv) {
                fputcsv($handle, [
                    $inv->id,
                    $inv->project_id,
                    $inv->project->name ?? '',
                    $inv->account->name ?? '',
                    $inv->transfer_id,
                    $inv->pay_in_id,
                    number_format($inv->amount / 100, 2),
                    $inv->type_label,
                    $inv->paid ? 'Yes' : 'No',
                    $inv->paid_on,
                    $inv->reserved_until,
                ]);
            }

            fclose($handle);
        }, 'investments.csv', $headers);
    }

    public function create()
    {
        $projects = Project::whereNotNull('project_id')
            ->where('project_id', '!=', '')
            ->orderBy('project_id')
            ->get(['project_id', 'id', 'name']);

        $accounts = \App\Models\Account::on('legacy')
            ->where('deleted', 0)
            ->with('person', 'company')
            ->orderBy('id')
            ->limit(500) // Limit to avoid timeout
            ->get();

        return view('admin.investments.create', compact('projects', 'accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:legacy.projects,project_id',
            'account_id' => 'required|exists:legacy.accounts,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'nullable|in:1,2', // 1 = Debt, 2 = Mezzanine
            'paid' => 'nullable|boolean',
            'transfer_id' => 'nullable|integer|min:1',
            'pay_in_id' => 'nullable|integer|min:1',
        ]);

        // Get the project - need to find by project_id but use id for the investment
        $project = Project::where('project_id', $validated['project_id'])->firstOrFail();

        $investment = Investments::create([
            'project_id' => $project->id, // Use internal id, not project_id
            'account_id' => $validated['account_id'],
            'amount' => (int)($validated['amount'] * 100), // Convert to pennies
            'type' => $validated['type'] ?? 1, // Default to Debt
            'paid' => $validated['paid'] ? 1 : 0,
            'transfer_id' => !empty($validated['transfer_id']) ? $validated['transfer_id'] : null,
            'pay_in_id' => !empty($validated['pay_in_id']) ? $validated['pay_in_id'] : null,
            'paid_on' => $validated['paid'] ? now() : null,
        ]);

        return redirect()->route('admin.investments.index')
            ->with('success', 'Investment created successfully.');
    }

    public function edit(Investments $investment)
    {
        $projects = Project::whereNotNull('project_id')
            ->where('project_id', '!=', '')
            ->orderBy('project_id')
            ->get(['project_id', 'id', 'name']);

        $accounts = \App\Models\Account::on('legacy')
            ->where('deleted', 0)
            ->with('person', 'company')
            ->orderBy('id')
            ->limit(500)
            ->get();

        // Get the project_id (not internal id) for the form
        $project = $investment->project;
        $investment->project_id_display = $project->project_id ?? null;

        return view('admin.investments.edit', compact('investment', 'projects', 'accounts'));
    }

    public function update(Request $request, Investments $investment)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:legacy.projects,project_id',
            'account_id' => 'required|exists:legacy.accounts,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'nullable|in:1,2', // 1 for Debt, 2 for Mezzanine
            'paid' => 'nullable|boolean',
            'transfer_id' => 'nullable|integer|min:1',
            'pay_in_id' => 'nullable|integer|min:1',
        ]);

        // Get the project's internal ID
        $project = Project::where('project_id', $validated['project_id'])->firstOrFail();

        $investment->update([
            'project_id' => $project->id,
            'account_id' => $validated['account_id'],
            'amount' => (int)($validated['amount'] * 100),
            'type' => $validated['type'] ?? 1, // Default to Debt
            'paid' => $validated['paid'] ? 1 : 0,
            'transfer_id' => !empty($validated['transfer_id']) ? $validated['transfer_id'] : null,
            'pay_in_id' => !empty($validated['pay_in_id']) ? $validated['pay_in_id'] : null,
            'paid_on' => $validated['paid'] ? ($investment->paid_on ?? now()) : null,
        ]);

        return redirect()->route('admin.investments.index')
            ->with('success', 'Investment updated successfully.');
    }

    public function destroy(Investments $investment)
    {
        // Check if deleted column exists
        if (Schema::connection('legacy')->hasColumn('project_investments', 'deleted')) {
            $investment->update(['deleted' => 1]);
        } else {
            // If no soft delete, we can't actually delete (legacy system constraint)
            // Just return with a message
            return redirect()->route('admin.investments.index')
                ->with('error', 'Cannot delete investment - legacy system does not support deletion.');
        }

        return redirect()->route('admin.investments.index')
            ->with('success', 'Investment deleted successfully.');
    }
}
