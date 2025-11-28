<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\DocumentEmailLog;
use App\Models\InvestorNotification;
use App\Models\Project;
use App\Models\ProjectQuarterlyIncomePayee;
use Illuminate\Http\Request;

class InvestorDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $account = auth('investor')->user();

        $investments = $account->investments()
            ->with('project')
            ->where('paid', 1)
            ->get()
            ->groupBy('project_id');

        // For each project, get paginated updates
        $projectUpdates = [];
        $projectDocuments = [];
        $projectDocumentLogs = [];
        $projectPayouts = [];
        $projectTimelines = [];
        $perPage = 5;
        foreach ($investments as $projectId => $projectInvestments) {
            $project = $projectInvestments->first()->project;
            if ($project) {
                $project->loadMissing('investorDocuments', 'property');

                // Use a unique page parameter for each project
                $pageParam = 'updates_page_' . $projectId;
                $page = request()->input($pageParam, 1);
                $updatesQuery = $project->updates()->where('category', 3)->orderByDesc('sent_on');
                $projectUpdates[$projectId] = $updatesQuery->paginate($perPage, ['*'], $pageParam, $page);

                $projectDocuments[$projectId] = $project->investorDocuments;
                $projectDocumentLogs[$projectId] = DocumentEmailLog::where('project_id', $project->project_id)
                    ->where('account_id', $account->id)
                    ->orderByDesc('sent_at')
                    ->limit(10)
                    ->get();

                $payouts = ProjectQuarterlyIncomePayee::with('update')
                    ->where('account_id', $account->id)
                    ->whereHas('update', function ($query) use ($project) {
                        $query->where('project_id', $project->id);
                    })
                    ->orderByDesc('paid_on')
                    ->get();
                $projectPayouts[$projectId] = $payouts;

                foreach ($payouts as $payout) {
                    if ($payout->paid) {
                        InvestorNotification::firstOrCreate(
                            [
                                'account_id' => $account->id,
                                'source_type' => 'payout',
                                'source_id' => $payout->id,
                            ],
                            [
                                'project_id' => $project->project_id,
                                'type' => 'payout',
                                'message' => 'Payment of ' . strip_tags(money($payout->amount ?? 0)) . ' recorded on ' . ($payout->paid_on ? $payout->paid_on->format('d M Y') : ''),
                                'link' => url('/investor/dashboard') . '#project-' . $project->project_id,
                            ]
                        );
                    }
                }

                $projectTimelines[$projectId] = $this->buildTimeline($project);
            }
        }

        $notifications = InvestorNotification::where('account_id', $account->id)
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();
        $unreadNotifications = $notifications->whereNull('read_at')->count();

        return view('investor.dashboard', compact(
            'account',
            'investments',
            'projectUpdates',
            'projectDocuments',
            'projectDocumentLogs',
            'projectPayouts',
            'projectTimelines',
            'notifications',
            'unreadNotifications'
        ));
    }

    protected function buildTimeline(Project $project): array
    {
        $currentStatus = $project->status;
        $stagesConfig = [
            [
                'label' => 'Due diligence complete',
                'date' => $project->submitted_on,
                'status' => Project::STATUS_PENDING_REVIEW,
            ],
            [
                'label' => 'Under review',
                'date' => $project->under_review_on,
                'status' => Project::STATUS_UNDER_REVIEW,
            ],
            [
                'label' => 'AIP signed',
                'date' => $project->aip_signed_on,
                'status' => Project::STATUS_AIP_SIGNED,
            ],
            [
                'label' => 'Set up complete',
                'date' => $project->set_up_completed_on,
                'status' => Project::STATUS_PENDING_COMPLIANCE,
            ],
            [
                'label' => 'Launched to investors',
                'date' => $project->launched_on,
                'status' => Project::STATUS_PENDING_EQUITY,
            ],
            [
                'label' => 'Purchase complete',
                'date' => optional($project->property)->purchase_completion_date,
                'status' => Project::STATUS_PENDING_CONSTRUCTION,
            ],
            [
                'label' => 'Project complete',
                'date' => $project->completed_on,
                'status' => Project::STATUS_SOLD,
            ],
        ];

        $stages = [];
        foreach ($stagesConfig as $stage) {
            $stage['completed'] = (bool) $stage['date'] || $currentStatus >= $stage['status'];
            $stages[] = $stage;
        }

        return [
            'stages' => $stages,
            'expected_payout' => $project->expected_payout_date,
            'investment_term' => optional($project->property)->investment_turnaround_time,
        ];
    }
}
