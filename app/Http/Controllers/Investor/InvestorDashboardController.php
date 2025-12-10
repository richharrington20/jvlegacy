<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\AccountDocument;
use App\Models\AccountShare;
use App\Models\DocumentEmailLog;
use App\Models\EmailHistory;
use App\Models\InvestorNotification;
use App\Models\Project;
use App\Models\ProjectQuarterlyIncomePayee;
use App\Models\SupportTicket;
use App\Models\SystemStatus;
use Illuminate\Http\Request;

class InvestorDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $account = auth('investor')->user();

        // Get own investments
        $ownInvestments = $account->investments()
            ->with('project')
            ->where('paid', 1)
            ->get();

        // Get shared accounts (accounts that have shared access with this account)
        $sharedAccounts = collect();
        try {
            $sharedAccounts = AccountShare::where('shared_account_id', $account->id)
                ->where('status', AccountShare::STATUS_ACTIVE)
                ->where('deleted', false)
                ->with('primaryAccount')
                ->get();
        } catch (\Exception $e) {
            // Table doesn't exist yet, use empty collection
            if (!str_contains($e->getMessage(), "Table 'jvsys.account_shares' doesn't exist")) {
                // Re-throw if it's a different error
                throw $e;
            }
        }

        // Get investments from shared accounts
        $sharedInvestments = collect();
        $sharedAccountMap = [];
        foreach ($sharedAccounts as $share) {
            $primaryAccount = $share->primaryAccount;
            if ($primaryAccount) {
                $primaryInvestments = $primaryAccount->investments()
                    ->with('project')
                    ->where('paid', 1)
                    ->get();
                
                // Mark these as shared investments
                foreach ($primaryInvestments as $investment) {
                    $investment->is_shared = true;
                    $investment->shared_from_account = $primaryAccount;
                    $sharedInvestments->push($investment);
                }
                $sharedAccountMap[$primaryAccount->id] = $primaryAccount;
            }
        }

        // Combine own and shared investments
        $allInvestments = $ownInvestments->merge($sharedInvestments);
        $investments = $allInvestments->groupBy('project_id');

        // For each project, get paginated updates
        $projectUpdates = [];
        $projectDocuments = [];
        $projectDocumentLogs = [];
        $projectPayouts = [];
        $projectTimelines = [];
        $perPage = 5;
        foreach ($investments as $projectId => $projectInvestments) {
            $firstInvestment = $projectInvestments->first();
            $project = $firstInvestment->project;
            
            // If project is null, try to load it directly using project_id from investment
            if (!$project && $firstInvestment->project_id) {
                // Cast project_id to integer to ensure proper matching
                $projectIdValue = (int) $firstInvestment->project_id;
                
                // Try loading with legacy connection explicitly using internal id
                $project = Project::on('legacy')
                    ->where('id', $projectIdValue)
                    ->first();
                
                // If still null, try loading by external project_id as fallback
                if (!$project) {
                    $project = Project::on('legacy')
                        ->where('project_id', $projectIdValue)
                        ->first();
                }
            }
            
            // If project relationship loaded but name is empty, reload it
            if ($project && empty(trim($project->name ?? '')) && $firstInvestment->project_id) {
                $projectIdValue = (int) $firstInvestment->project_id;
                $project = Project::on('legacy')
                    ->where('id', $projectIdValue)
                    ->first();
            }
            
            // Set the project back on all investments in this group so the view can access it
            if ($project) {
                foreach ($projectInvestments as $investment) {
                    $investment->setRelation('project', $project);
                }
                
                $project->loadMissing('investorDocuments', 'property');

                // Use a unique page parameter for each project
                $pageParam = 'updates_page_' . $projectId;
                $page = request()->input($pageParam, 1);
                $updatesQuery = $project->updates()->with('project', 'images')->where('category', 3)->orderByDesc('sent_on');
                $projectUpdates[$projectId] = $updatesQuery->paginate($perPage, ['*'], $pageParam, $page);

                $projectDocuments[$projectId] = $project->investorDocuments;
                $projectDocumentLogs[$projectId] = DocumentEmailLog::where('project_id', $project->project_id)
                    ->where('account_id', $account->id)
                    ->orderByDesc('sent_at')
                    ->limit(10)
                    ->get();

                $payouts = ProjectQuarterlyIncomePayee::with('quarterlyUpdate')
                    ->where('account_id', $account->id)
                    ->whereHas('quarterlyUpdate', function ($query) use ($project) {
                        $query->where('project_id', $project->id);
                    })
                    ->orderByDesc('paid_on')
                    ->get();
                $projectPayouts[$projectId] = $payouts;

                foreach ($payouts as $payout) {
                    if ($payout->paid) {
                        try {
                            InvestorNotification::firstOrCreate(
                                [
                                    'account_id' => $account->id,
                                    'source_type' => 'payout',
                                    'source_id' => $payout->id,
                                ],
                                [
                                    'project_id' => $project->project_id,
                                    'type' => 'payout',
                                    'message' => 'Payment of ' . strip_tags(money($payout->amount ?? 0)) . ' recorded on ' . ($payout->paid_on ? (is_string($payout->paid_on) ? \Carbon\Carbon::parse($payout->paid_on)->format('d M Y') : $payout->paid_on->format('d M Y')) : ''),
                                    'link' => url('/investor/dashboard') . '#project-' . $project->project_id,
                                ]
                            );
                        } catch (\Exception $e) {
                            // Table doesn't exist yet, skip notification creation
                        }
                    }
                }

                $projectTimelines[$projectId] = $this->buildTimeline($project);
            }
        }

        try {
            $notifications = InvestorNotification::where('account_id', $account->id)
                ->orderByDesc('created_at')
                ->limit(15)
                ->get();
            $unreadNotifications = $notifications->whereNull('read_at')->count();
        } catch (\Exception $e) {
            // Table doesn't exist yet, use empty collection
            $notifications = collect();
            $unreadNotifications = 0;
        }

        // Get support tickets
        try {
            $supportTickets = SupportTicket::where('account_id', $account->id)
                ->where('deleted', false)
                ->with(['project', 'replies'])
                ->orderByDesc('created_on')
                ->get();
        } catch (\Exception $e) {
            $supportTickets = collect();
        }

        // Get system status
        try {
            $systemStatus = SystemStatus::forLogin()
                ->orderByDesc('created_on')
                ->first();
            
            // Try to load updates if table exists
            if ($systemStatus) {
                try {
                    $systemStatus->load(['updates.account.person', 'updates.account.company', 'updates.fixedBy.person', 'updates.fixedBy.company']);
                } catch (\Exception $e) {
                    // Updates table doesn't exist yet, just continue without updates
                    if (!str_contains($e->getMessage(), "Table 'jvsys.system_status_updates' doesn't exist")) {
                        // Re-throw if it's a different error
                        throw $e;
                    }
                }
            }
        } catch (\Exception $e) {
            // Table doesn't exist yet
            if (str_contains($e->getMessage(), "Table 'jvsys.system_status' doesn't exist") || 
                str_contains($e->getMessage(), "Table 'jvsys.system_status_updates' doesn't exist")) {
                $systemStatus = null;
            } else {
                // Re-throw if it's a different error
                throw $e;
            }
        }

        // Get email history (combine all email sources)
        $emailHistory = collect();
        try {
            // Get document emails
            $documentEmails = collect();
            try {
                // Group document emails by project and sent_at to get all documents sent together
                $documentLogs = DocumentEmailLog::where('account_id', $account->id)
                    ->with('project')
                    ->orderBy('sent_at', 'desc')
                    ->get()
                    ->groupBy(function($log) {
                        return ($log->project_id ?? 'no-project') . '_' . ($log->sent_at ? $log->sent_at->format('Y-m-d H:i:s') : 'no-date');
                    });
                
                $documentEmails = collect();
                foreach ($documentLogs as $groupKey => $logs) {
                    $firstLog = $logs->first();
                    $documentNames = $logs->pluck('document_name')->filter()->unique()->values();
                    
                    $documentEmails->push((object)[
                        'id' => 'doc_group_' . $firstLog->id,
                        'email_type' => EmailHistory::TYPE_DOCUMENT,
                        'type_label' => 'Document Email',
                        'icon' => 'fas fa-file-alt text-blue-500',
                        'subject' => 'Your documents for ' . ($firstLog->project->name ?? 'your investment'),
                        'recipient' => $firstLog->recipient ?? $account->email,
                        'project' => $firstLog->project,
                        'sent_at' => $firstLog->sent_at,
                        'content' => $documentNames->join(', ') ?: 'Documents were sent to you via email.',
                        'documents' => $documentNames->map(fn($name) => (object)['name' => $name]),
                    ]);
                }
            } catch (\Exception $e) {
                // Table might not exist or query failed
                \Log::warning('Failed to load document emails: ' . $e->getMessage());
            }

            // Get project update emails (from updates sent)
            $updateEmails = collect();
            try {
                $updateEmails = \App\Models\Update::whereHas('project.investments', function($query) use ($account) {
                        $query->where('account_id', $account->id)->where('paid', 1);
                    })
                    ->where('category', 3)
                    ->where('sent', 1)
                    ->with('project', 'images')
                    ->get()
                    ->map(function($update) use ($account) {
                        return (object)[
                            'id' => 'update_' . $update->id,
                            'email_type' => EmailHistory::TYPE_PROJECT_UPDATE,
                            'type_label' => 'Project Update',
                            'icon' => 'fas fa-bullhorn text-green-500',
                            'subject' => 'Project Update: ' . ($update->project->name ?? 'Your Investment'),
                            'recipient' => $account->email,
                            'project' => $update->project,
                            'sent_at' => $update->sent_on,
                            'content' => $update->comment ?? '',
                            'images' => $update->images->map(function($img) {
                                try {
                                    return (object)[
                                        'url' => $img->url ?? '',
                                        'thumbnail_url' => $img->thumbnail_url ?? '',
                                        'description' => $img->description ?? '',
                                        'file_name' => $img->file_name ?? '',
                                        'is_image' => $img->is_image ?? false,
                                        'icon' => $img->icon ?? 'fas fa-file text-gray-400',
                                    ];
                                } catch (\Exception $e) {
                                    \Log::warning('Error mapping update image: ' . $e->getMessage());
                                    return (object)[
                                        'url' => '',
                                        'thumbnail_url' => '',
                                        'description' => '',
                                        'file_name' => '',
                                        'is_image' => false,
                                        'icon' => 'fas fa-file text-gray-400',
                                    ];
                                } catch (\Error $e) {
                                    \Log::warning('Error mapping update image: ' . $e->getMessage());
                                    return (object)[
                                        'url' => '',
                                        'thumbnail_url' => '',
                                        'description' => '',
                                        'file_name' => '',
                                        'is_image' => false,
                                        'icon' => 'fas fa-file text-gray-400',
                                    ];
                                }
                            })->filter(function($img) {
                                // Filter out images with no URL
                                return !empty($img->url);
                            })->values() ?? collect(),
                        ];
                    });
            } catch (\Exception $e) {
                // Query might fail
                \Log::warning('Failed to load update emails: ' . $e->getMessage());
            }

            // Get support ticket emails
            $ticketEmails = collect();
            try {
                $ticketEmails = SupportTicket::where('account_id', $account->id)
                    ->with('project')
                    ->get()
                    ->map(function($ticket) use ($account) {
                        return (object)[
                            'id' => 'ticket_' . $ticket->id,
                            'email_type' => EmailHistory::TYPE_SUPPORT_TICKET,
                            'type_label' => 'Support Ticket',
                            'icon' => 'fas fa-headset text-purple-500',
                            'subject' => 'Support Ticket Created - ' . ($ticket->ticket_id ?? 'N/A'),
                            'recipient' => $ticket->account->email ?? $account->email,
                            'project' => $ticket->project,
                            'sent_at' => $ticket->created_on,
                            'content' => $ticket->message ?? '',
                            'ticket_id' => $ticket->ticket_id ?? null,
                        ];
                    });
            } catch (\Exception $e) {
                // Table might not exist or query failed
                \Log::warning('Failed to load ticket emails: ' . $e->getMessage());
            }

            // Combine and sort by date
            $emailHistory = $documentEmails->merge($updateEmails)->merge($ticketEmails)
                ->sortByDesc(function($email) {
                    if (!$email->sent_at) {
                        return 0;
                    }
                    if (is_string($email->sent_at)) {
                        try {
                            return \Carbon\Carbon::parse($email->sent_at)->timestamp;
                        } catch (\Exception $e) {
                            return 0;
                        }
                    }
                    return $email->sent_at->timestamp ?? 0;
                })
                ->values();

            // Paginate manually
            $perPage = 20;
            $currentPage = request()->get('email_page', 1);
            $items = $emailHistory->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $emailHistory = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $emailHistory->count(),
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'pageName' => 'email_page']
            );
        } catch (\Exception $e) {
            // Fallback: create empty paginator
            \Log::error('Failed to load email history: ' . $e->getMessage());
            $emailHistory = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20, 1, ['path' => request()->url(), 'pageName' => 'email_page']);
        }

        // Get account documents (personal documents like share certificates)
        $accountDocuments = collect();
        try {
            $accountDocuments = AccountDocument::where('account_id', $account->id)
                ->where('deleted', false)
                ->orderBy('created_on', 'desc')
                ->get();
        } catch (\Exception $e) {
            // Table might not exist yet
            if (!str_contains($e->getMessage(), "Table 'jvsys.account_documents' doesn't exist")) {
                throw $e;
            }
        }

        return view('investor.dashboard', compact(
            'account',
            'investments',
            'projectUpdates',
            'projectDocuments',
            'projectDocumentLogs',
            'projectPayouts',
            'projectTimelines',
            'notifications',
            'unreadNotifications',
            'supportTickets',
            'systemStatus',
            'emailHistory',
            'accountDocuments'
        ));
    }

    protected function buildTimeline(Project $project): array
    {
        $currentStatus = $project->status;
        
        // Helper to ensure date is Carbon instance or null
        $ensureDate = function($date) {
            if (!$date) {
                return null;
            }
            if (is_string($date)) {
                try {
                    return \Carbon\Carbon::parse($date);
                } catch (\Exception $e) {
                    return null;
                }
            }
            return $date;
        };
        
        $stagesConfig = [
            [
                'label' => 'Due diligence complete',
                'date' => $ensureDate($project->submitted_on),
                'status' => Project::STATUS_PENDING_REVIEW,
            ],
            [
                'label' => 'Under review',
                'date' => $ensureDate($project->under_review_on),
                'status' => Project::STATUS_UNDER_REVIEW,
            ],
            [
                'label' => 'AIP signed',
                'date' => $ensureDate($project->aip_signed_on),
                'status' => Project::STATUS_AIP_SIGNED,
            ],
            [
                'label' => 'Set up complete',
                'date' => $ensureDate($project->set_up_completed_on),
                'status' => Project::STATUS_PENDING_COMPLIANCE,
            ],
            [
                'label' => 'Launched to investors',
                'date' => $ensureDate($project->launched_on),
                'status' => Project::STATUS_PENDING_EQUITY,
            ],
            [
                'label' => 'Purchase complete',
                'date' => $ensureDate(optional($project->property)->purchase_completion_date),
                'status' => Project::STATUS_PENDING_CONSTRUCTION,
            ],
            [
                'label' => 'Project complete',
                'date' => $ensureDate($project->completed_on),
                'status' => Project::STATUS_SOLD,
            ],
        ];

        $stages = [];
        foreach ($stagesConfig as $stage) {
            $stage['completed'] = (bool) $stage['date'] || $currentStatus >= $stage['status'];
            $stages[] = $stage;
        }

        // Ensure expected_payout_date is Carbon instance
        $expectedPayout = $project->expected_payout_date;
        if ($expectedPayout && is_string($expectedPayout)) {
            try {
                $expectedPayout = \Carbon\Carbon::parse($expectedPayout);
            } catch (\Exception $e) {
                $expectedPayout = null;
            }
        }

        return [
            'stages' => $stages,
            'expected_payout' => $expectedPayout,
            'investment_term' => optional($project->property)->investment_turnaround_time,
        ];
    }
}
