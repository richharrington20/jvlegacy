@extends('layouts.app')

@section('content')
<div class="mx-auto mt-10" x-data="{ activeTab: 'overview' }">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Welcome, {{ $account->name }}</h1>
            <p class="text-gray-600 mt-1">Manage your investments and track your portfolio</p>
        </div>
        <button onclick="document.getElementById('profile-edit-form').classList.toggle('hidden')" class="px-4 py-2 bg-brand-magenta text-white rounded-lg hover:bg-brand-magenta-dark font-medium">
            <i class="fas fa-user-edit mr-2"></i>Edit Profile
        </button>
    </div>

    @if (session('status'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('status') }}
        </div>
    @endif

    <!-- Profile Edit Form -->
    <div id="profile-edit-form" class="hidden bg-white shadow-lg rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Edit Profile</h2>
        <form method="POST" action="{{ route('investor.profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-sm font-medium mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ $account->email }}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
            </div>

            @if($account->person)
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">First Name</label>
                        <input type="text" name="first_name" value="{{ $account->person->first_name ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Last Name</label>
                        <input type="text" name="last_name" value="{{ $account->person->last_name ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Telephone</label>
                    <input type="text" name="telephone_number" value="{{ $account->person->telephone_number ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
            @elseif($account->company)
                <div>
                    <label class="block text-sm font-medium mb-1">Company Name</label>
                    <input type="text" name="company_name" value="{{ $account->company->name ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium mb-1">New Password (leave blank to keep current)</label>
                <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-brand-magenta text-white rounded hover:bg-brand-magenta-dark">Save Changes</button>
                <button type="button" onclick="document.getElementById('profile-edit-form').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px" aria-label="Tabs">
                <button 
                    @click="activeTab = 'overview'"
                    :class="activeTab === 'overview' ? 'border-brand-teal text-brand-teal' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                >
                    <i class="fas fa-home mr-2"></i>Overview
                </button>
                <button 
                    @click="activeTab = 'investments'"
                    :class="activeTab === 'investments' ? 'border-brand-teal text-brand-teal' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                >
                    <i class="fas fa-chart-line mr-2"></i>Investments
                </button>
                <button 
                    @click="activeTab = 'documents'"
                    :class="activeTab === 'documents' ? 'border-brand-teal text-brand-teal' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                >
                    <i class="fas fa-file-alt mr-2"></i>Documents
                </button>
                <button 
                    @click="activeTab = 'payouts'"
                    :class="activeTab === 'payouts' ? 'border-brand-teal text-brand-teal' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                >
                    <i class="fas fa-pound-sign mr-2"></i>Payouts
                </button>
                <button 
                    @click="activeTab = 'email-history'"
                    :class="activeTab === 'email-history' ? 'border-brand-teal text-brand-teal' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                >
                    <i class="fas fa-envelope mr-2"></i>Email History
                </button>
                <button 
                    @click="activeTab = 'helpdesk'"
                    :class="activeTab === 'helpdesk' ? 'border-brand-teal text-brand-teal' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors relative"
                >
                    <i class="fas fa-headset mr-2"></i>Helpdesk
                    @if(isset($supportTickets) && $supportTickets->where('status', 'open')->count() > 0)
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            {{ $supportTickets->where('status', 'open')->count() }}
                        </span>
                    @endif
                </button>
                <button 
                    @click="activeTab = 'sharing'"
                    :class="activeTab === 'sharing' ? 'border-brand-teal text-brand-teal' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                >
                    <i class="fas fa-share-alt mr-2"></i>Account Sharing
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Overview Tab -->
            <div x-show="activeTab === 'overview'" x-transition>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-brand-magenta-light to-brand-purple-light rounded-lg p-6 border border-brand-magenta">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-brand-magenta mb-1">Total Invested</p>
                                <p class="text-2xl font-bold text-brand-magenta-dark">
                                    @php
                                        $totalInvested = collect($investments)->flatten()->sum('amount');
                                    @endphp
                                    {!! money($totalInvested) !!}
                                </p>
                            </div>
                            <i class="fas fa-chart-pie text-brand-magenta text-3xl"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-brand-orange-light to-brand-teal-light rounded-lg p-6 border border-brand-orange">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-brand-orange mb-1">Total Paid</p>
                                <p class="text-2xl font-bold text-brand-orange-dark">
                                    @php
                                        $allPayouts = collect($projectPayouts)->flatten();
                                        $totalPaid = $allPayouts->where('paid', 1)->sum('amount');
                                    @endphp
                                    {!! money($totalPaid) !!}
                                </p>
                            </div>
                            <i class="fas fa-check-circle text-brand-orange text-3xl"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-brand-purple-light to-brand-teal-light rounded-lg p-6 border border-brand-purple">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-brand-purple mb-1">Active Projects</p>
                                <p class="text-2xl font-bold text-brand-purple-dark">{{ count($investments) }}</p>
                            </div>
                            <i class="fas fa-folder-open text-brand-purple text-3xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-brand-teal-light to-brand-purple-light rounded-lg border border-brand-teal p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-3 text-gray-900">Welcome to Your Dashboard</h3>
                    <p class="text-gray-700 mb-4">
                        Your central hub for managing investments, accessing documents, tracking payouts, and getting support. 
                        Everything you need is just a click away.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="#investments" @click="activeTab = 'investments'" class="p-4 bg-white rounded-lg border border-brand-teal hover:border-brand-teal-dark hover:shadow-md transition-all">
                            <div class="flex items-center">
                                <div class="h-10 w-10 bg-brand-teal-light rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-chart-line text-brand-teal"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm">Investments</p>
                                    <p class="text-xs text-gray-600">View portfolio</p>
                                </div>
                            </div>
                        </a>
                        <a href="#documents" @click="activeTab = 'documents'" class="p-4 bg-white rounded-lg border border-green-100 hover:border-green-300 hover:shadow-md transition-all">
                            <div class="flex items-center">
                                <div class="h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-file-alt text-green-600"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm">Documents</p>
                                    <p class="text-xs text-gray-600">Download files</p>
                                </div>
                            </div>
                        </a>
                        <a href="#helpdesk" @click="activeTab = 'helpdesk'" class="p-4 bg-white rounded-lg border border-purple-100 hover:border-purple-300 hover:shadow-md transition-all">
                            <div class="flex items-center">
                                <div class="h-10 w-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-headset text-purple-600"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm">Get Help</p>
                                    <p class="text-xs text-gray-600">Support tickets</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                @if(count($investments) > 0)
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
                        <div class="space-y-3" x-data="{ expandedUpdates: {} }">
                            @php
                                $recentUpdates = collect();
                                foreach ($projectUpdates as $updates) {
                                    $recentUpdates = $recentUpdates->merge($updates->items());
                                }
                                $recentUpdates = $recentUpdates->sortByDesc('sent_on')->take(1);
                                // Load project relationships for all updates
                                foreach ($recentUpdates as $update) {
                                    if (!$update->relationLoaded('project') && $update->project_id) {
                                        $update->load('project');
                                    }
                                }
                            @endphp
                            @if($recentUpdates->count() > 0)
                                @foreach($recentUpdates as $update)
                                    @php
                                        $project = $update->project;
                                        $projectName = 'Unknown Project';
                                        if ($project) {
                                            $projectName = $project->name ?? ('Project #' . ($project->project_id ?? $project->id));
                                        } elseif ($update->project_id) {
                                            $projectName = 'Project #' . $update->project_id;
                                        }
                                    @endphp
                                    <div class="bg-gray-50 rounded-lg border border-gray-200" x-data="{ expanded: false }">
                                        <button 
                                            @click="expanded = !expanded"
                                            class="w-full flex items-start p-3 hover:bg-gray-100 transition-colors text-left"
                                            type="button"
                                        >
                                            <i class="fas fa-bullhorn text-brand-purple mt-1 mr-3 flex-shrink-0"></i>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900">{{ $projectName }}</p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    @if($update->sent_on)
                                                        @php
                                                            $sentOn = $update->sent_on;
                                                            if ($sentOn) {
                                                                if (is_string($sentOn)) {
                                                                    try {
                                                                        $sentOn = \Carbon\Carbon::parse($sentOn);
                                                                    } catch (\Exception $e) {
                                                                        $sentOn = null;
                                                                    }
                                                                }
                                                                if ($sentOn && ($sentOn instanceof \Carbon\Carbon || $sentOn instanceof \DateTime)) {
                                                                    echo $sentOn->format('d M Y');
                                                                } else {
                                                                    echo 'Invalid date';
                                                                }
                                                            }
                                                        @endphp
                                                    @endif
                                                </p>
                                            </div>
                                            <i class="fas fa-chevron-down text-gray-400 ml-2 mt-1 transition-transform" :class="{ 'rotate-180': expanded }"></i>
                                        </button>
                                        <div x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="px-3 pb-3">
                                            <div class="ml-8 pt-2 border-t border-gray-200">
                                                <div class="text-sm text-gray-700 prose prose-sm max-w-none">
                                                    {!! nl2br(e($update->comment ?? $update->comment_preview ?? '')) !!}
                                                </div>
                                                @if($update->images && $update->images->count() > 0)
                                                    <div class="mt-4 grid grid-cols-2 gap-3">
                                                        @foreach($update->images as $image)
                                                            <a href="{{ $image->url }}" target="_blank" class="border border-gray-200 rounded-lg overflow-hidden bg-gray-50 hover:shadow-md transition-shadow block">
                                                                @if($image->is_image)
                                                                    <img src="{{ $image->thumbnail_url ?? $image->url }}" alt="{{ $image->description ?? '' }}" class="w-full h-24 object-cover" onerror="this.onerror=null;this.src='{{ $image->url }}';">
                                                                @else
                                                                    <div class="flex flex-col items-center justify-center h-24 bg-white">
                                                                        <i class="{{ $image->icon }} text-2xl mb-1"></i>
                                                                        <span class="text-xs text-gray-600 text-center px-2 truncate w-full">{{ Str::limit($image->file_name, 15) }}</span>
                                                                    </div>
                                                                @endif
                                                                @if($image->description)
                                                                    <div class="px-2 py-1 text-xs text-gray-600 border-t border-gray-200">{{ $image->description }}</div>
                                                                @endif
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-sm text-gray-500">No recent activity</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Investments Tab -->
            <div x-show="activeTab === 'investments'" x-transition style="display: none;">
                @foreach ($investments as $projectId => $projectInvestments)
                    @php 
                        $firstInv = $projectInvestments->first();
                        $project = $firstInv && $firstInv->project ? $firstInv->project : null;
                        // If project is null but we have project_id, try to load it with legacy connection
                        if (!$project && $firstInv && $firstInv->project_id) {
                            $projectIdValue = (int) $firstInv->project_id;
                            $project = \App\Models\Project::on('legacy')
                                ->where('id', $projectIdValue)
                                ->first();
                            
                            // If still null, try loading by external project_id as fallback
                            if (!$project) {
                                $project = \App\Models\Project::on('legacy')
                                    ->where('project_id', $projectIdValue)
                                    ->first();
                            }
                        }
                    @endphp
                    @php $timeline = $projectTimelines[$projectId] ?? null; @endphp
                    
                    @if($project)
                    <div class="mb-8 bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-brand-magenta to-brand-purple p-6 text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-2xl font-bold mb-2">{{ $project->name ?? 'Project #' . ($project->project_id ?? $project->id) }}</h3>
                                    <p class="text-white opacity-90">Project ID: {{ $project->project_id ?? 'N/A' }}</p>
                                </div>
                                <button
                                    class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors"
                                    onclick="document.getElementById('support-modal-{{ $projectId }}').showModal()"
                                >
                                    <i class="fas fa-headset mr-2"></i>Support
                                </button>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="overflow-x-auto mb-6">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Type</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($projectInvestments as $inv)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-lg font-semibold text-gray-900">
                                                    {!! money($inv->amount) !!}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                    {{ human_date($inv->paid_on) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-brand-teal-light text-brand-teal-dark">
                                                        {{ $inv->type_label }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @php $updates = $projectUpdates[$projectId] ?? null; @endphp
                            @if($updates && $updates->count())
                                <div class="mb-6 bg-brand-purple-light border-l-4 border-brand-purple p-4 rounded">
                                    <h4 class="font-semibold mb-3 text-brand-purple-dark">Project Updates</h4>
                                    <div class="space-y-3" x-data="{ expandedUpdates: {} }">
                                        @foreach($updates->take(1) as $update)
                                            <div class="bg-white p-3 rounded border border-brand-purple-light" x-data="{ expanded: false }">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex-1">
                                                        <p class="text-xs text-gray-500 mb-1">
                                                            @if($update->sent_on)
                                                                @php
                                                                    $sentOn = $update->sent_on;
                                                                    if ($sentOn) {
                                                                        if (is_string($sentOn)) {
                                                                            try {
                                                                                $sentOn = \Carbon\Carbon::parse($sentOn);
                                                                            } catch (\Exception $e) {
                                                                                $sentOn = null;
                                                                            }
                                                                        }
                                                                        if ($sentOn && ($sentOn instanceof \Carbon\Carbon || $sentOn instanceof \DateTime)) {
                                                                            echo $sentOn->format('d M Y');
                                                                        } else {
                                                                            echo 'Invalid date';
                                                                        }
                                                                    }
                                                                @endphp
                                                            @endif
                                                        </p>
                                                        <div x-show="!expanded">
                                                            <p class="text-sm text-gray-900">{!! nl2br(e(Str::limit($update->comment_preview ?? $update->comment ?? '', 150))) !!}</p>
                                                        </div>
                                                        <div x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                                                            <div class="text-sm text-gray-900 prose prose-sm max-w-none">
                                                                {!! nl2br(e($update->comment ?? $update->comment_preview ?? '')) !!}
                                                            </div>
                                                            @if($update->images && $update->images->count() > 0)
                                                                <div class="mt-4 grid grid-cols-2 gap-3">
                                                                    @foreach($update->images as $image)
                                                                        <a href="{{ $image->url }}" target="_blank" class="border border-gray-200 rounded-lg overflow-hidden bg-gray-50 hover:shadow-md transition-shadow block">
                                                                            @if($image->is_image)
                                                                                <img src="{{ $image->thumbnail_url ?? $image->url }}" alt="{{ $image->description ?? '' }}" class="w-full h-24 object-cover" onerror="this.onerror=null;this.src='{{ $image->url }}';">
                                                                            @else
                                                                                <div class="flex flex-col items-center justify-center h-24 bg-white">
                                                                                    <i class="{{ $image->icon }} text-2xl mb-1"></i>
                                                                                    <span class="text-xs text-gray-600 text-center px-2 truncate w-full">{{ Str::limit($image->file_name, 15) }}</span>
                                                                                </div>
                                                                            @endif
                                                                            @if($image->description)
                                                                                <div class="px-2 py-1 text-xs text-gray-600 border-t border-gray-200">{{ $image->description }}</div>
                                                                            @endif
                                                                        </a>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <button 
                                                        class="ml-3 text-brand-teal hover:text-brand-teal-dark text-xs font-medium whitespace-nowrap" 
                                                        @click="expanded = !expanded"
                                                        type="button"
                                                        x-text="expanded ? 'Read less' : 'Read more'"
                                                    ></button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($updates->count() > 3 && $project && $project->project_id)
                                        <a href="{{ route('public.projects.show', $project->project_id) }}" class="mt-3 inline-block text-sm text-brand-teal hover:underline">
                                            View all {{ $updates->count() }} updates
                                        </a>
                                    @endif
                                </div>
                            @endif

                        </div>
                    </div>
                    @else
                    <!-- Project not found - show investments anyway -->
                    <div class="mb-8 bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-600 to-gray-700 p-6 text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-2xl font-bold mb-2">Project Not Found</h3>
                                    <p class="text-gray-300">Project ID: {{ $projectId ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-gray-600 mb-4">The project associated with these investments could not be loaded. Please contact support if this issue persists.</p>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($projectInvestments as $investment)
                                            <tr>
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{!! money($investment->amount) !!}</td>
                                                <td class="px-4 py-3 text-sm text-gray-500">
                                                    @if($investment->paid_on)
                                                        @php
                                                            $paidOn = $investment->paid_on;
                                                            if ($paidOn) {
                                                                if (is_string($paidOn)) {
                                                                    try {
                                                                        $paidOn = \Carbon\Carbon::parse($paidOn);
                                                                    } catch (\Exception $e) {
                                                                        $paidOn = null;
                                                                    }
                                                                }
                                                                if ($paidOn && ($paidOn instanceof \Carbon\Carbon || $paidOn instanceof \DateTime)) {
                                                                    echo $paidOn->format('d M Y');
                                                                } else {
                                                                    echo 'Invalid date';
                                                                }
                                                            }
                                                        @endphp
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-sm">
                                                    <span class="px-2 py-1 rounded text-xs font-medium {{ $investment->type == 1 ? 'bg-brand-teal-light text-brand-teal-dark' : 'bg-brand-purple-light text-brand-purple-dark' }}">
                                                        {{ $investment->type_label }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($project)
                    <dialog id="support-modal-{{ $projectId }}" class="rounded-lg w-full max-w-2xl p-0">
                        <form method="POST" action="{{ route('investor.support.store') }}">
                            @csrf
                            <input type="hidden" name="project_id" value="{{ $project->project_id ?? $projectId }}">
                            <div class="p-6 border-b">
                                <h4 class="text-xl font-semibold">Support Request · {{ $project->name ?? 'Project #' . $projectId }}</h4>
                                <p class="text-sm text-gray-500 mt-1">Tell us what you need help with; the team will reply by email.</p>
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Subject</label>
                                    <input type="text" name="subject" required class="w-full border rounded px-3 py-2" placeholder="e.g. Questions about Q3 payout" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Details</label>
                                    <textarea name="message" rows="5" required class="w-full border rounded px-3 py-2" placeholder="Give us as much detail as possible..."></textarea>
                                </div>
                            </div>
                            <div class="p-4 border-t flex justify-end gap-3 bg-gray-50 rounded-b-lg">
                                <button type="button" class="px-4 py-2 text-sm text-gray-600" onclick="document.getElementById('support-modal-{{ $projectId }}').close()">Cancel</button>
                                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-brand-magenta rounded hover:bg-brand-magenta-dark">Send request</button>
                            </div>
                        </form>
                    </dialog>
                    @endif
                @endforeach
            </div>

            <!-- Documents Tab -->
            <div x-show="activeTab === 'documents'" x-transition style="display: none;">
                <div class="space-y-6">
                    <!-- Personal Documents Section -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Personal Documents</h3>
                                <p class="text-sm text-gray-500 mt-1">Your individual documents like share certificates, loan agreements, and statements</p>
                            </div>
                        </div>
                        
                        @if(isset($accountDocuments) && $accountDocuments->count() > 0)
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                @foreach($accountDocuments as $document)
                                    <a href="{{ $document->url }}" target="_blank" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-brand-teal transition-all group">
                                        @php
                                            $iconClass = 'fas fa-file-alt text-brand-teal';
                                            $category = strtolower($document->category ?? '');
                                            if (str_contains($category, 'share') || str_contains($category, 'certificate')) {
                                                $iconClass = 'fas fa-certificate text-yellow-600';
                                            } elseif (str_contains($category, 'loan')) {
                                                $iconClass = 'fas fa-file-signature text-green-600';
                                            } elseif (str_contains($category, 'statement')) {
                                                $iconClass = 'fas fa-file-invoice text-purple-600';
                                            }
                                        @endphp
                                        <i class="{{ $iconClass }} text-4xl mb-2 group-hover:scale-110 transition-transform"></i>
                                        <span class="text-xs text-gray-600 group-hover:text-gray-900 text-center font-medium">
                                            {{ Str::limit($document->name ?? 'Document', 20) }}
                                        </span>
                                        @if($document->category)
                                            <span class="text-xs text-gray-400 mt-1">{{ ucfirst(str_replace('_', ' ', $document->category)) }}</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                <i class="fas fa-file-alt text-gray-400 text-5xl mb-4"></i>
                                <p class="text-gray-600 font-medium mb-2">No personal documents available</p>
                                <p class="text-sm text-gray-500">Your share certificates, loan agreements, and other personal documents will appear here once they are uploaded.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Project Documents Section -->
                    @if(count($investments) > 0)
                        @foreach ($investments as $projectId => $projectInvestments)
                            @php 
                                $firstInv = $projectInvestments->first();
                                $project = $firstInv && $firstInv->project ? $firstInv->project : null;
                                // If project is null but we have project_id, try to load it with legacy connection
                                if (!$project && $firstInv && $firstInv->project_id) {
                                    $projectIdValue = (int) $firstInv->project_id;
                                    $project = \App\Models\Project::on('legacy')
                                        ->where('id', $projectIdValue)
                                        ->first();
                                    
                                    // If still null, try loading by external project_id as fallback
                                    if (!$project) {
                                        $project = \App\Models\Project::on('legacy')
                                            ->where('project_id', $projectIdValue)
                                            ->first();
                                    }
                                }
                            @endphp
                            @php 
                                $allDocuments = $projectDocuments[$projectId] ?? collect();
                                // Filter to only show investment memorandums
                                $documents = $allDocuments->filter(function($doc) {
                                    $name = strtolower($doc->name ?? '');
                                    return str_contains($name, 'memorandum') || str_contains($name, 'investment memorandum');
                                });
                            @endphp
                            @php $documentLogs = $projectDocumentLogs[$projectId] ?? collect(); @endphp
                            
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-900">{{ $project ? ($project->name ?? 'Project #' . ($project->project_id ?? $project->id)) : 'Unknown Project' }}</h3>
                                        <p class="text-sm text-gray-500 mt-1">Investment memorandums for this project</p>
                                    </div>
                                </div>
                                
                                @if($documents->count() > 0)
                                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-4">
                                        @foreach($documents as $document)
                                            <a href="{{ $document->url }}" target="_blank" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-brand-teal transition-all group">
                                                <i class="{{ $document->icon }} text-4xl mb-2 group-hover:scale-110 transition-transform {{ $document->status_type === 'pdf' ? 'text-red-500' : ($document->status_type === 'word' ? 'text-brand-teal' : 'text-gray-500') }}"></i>
                                                <span class="text-xs text-gray-600 group-hover:text-gray-900 text-center font-medium">
                                                    {{ Str::limit($document->name ?? 'Document', 20) }}
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                    <div class="flex items-center gap-4 pt-4 border-t">
                                        @if($project)
                                            <form method="POST" action="{{ route('investor.documents.email', $project->project_id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="px-4 py-2 bg-brand-magenta text-white rounded-lg hover:bg-brand-magenta-dark text-sm font-medium">
                                                    <i class="fas fa-envelope mr-2"></i>Email me these documents
                                                </button>
                                            </form>
                                        @endif
                                        @if($documentLogs->count())
                                            <span class="text-xs text-gray-500">
                                                Last emailed: 
                                                @php
                                                    $sentAt = $documentLogs->first()->sent_at ?? null;
                                                    if ($sentAt) {
                                                        if (is_string($sentAt)) {
                                                            try {
                                                                $sentAt = \Carbon\Carbon::parse($sentAt);
                                                            } catch (\Exception $e) {
                                                                $sentAt = null;
                                                            }
                                                        }
                                                        if ($sentAt && ($sentAt instanceof \Carbon\Carbon || $sentAt instanceof \DateTime)) {
                                                            echo $sentAt->format('d M Y H:i');
                                                        } else {
                                                            echo 'Invalid date';
                                                        }
                                                    } else {
                                                        echo 'Never';
                                                    }
                                                @endphp
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                        <i class="fas fa-folder-open text-gray-400 text-5xl mb-4"></i>
                                        <p class="text-gray-600 font-medium mb-2">No investment memorandums available</p>
                                        <p class="text-sm text-gray-500">Investment memorandums for this project will appear here once they are available.</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                <i class="fas fa-folder-open text-gray-400 text-5xl mb-4"></i>
                                <p class="text-gray-600 font-medium mb-2">No investments found</p>
                                <p class="text-sm text-gray-500">Project documents will appear here once you have investments in active projects.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payouts Tab -->
            <div x-show="activeTab === 'payouts'" x-transition style="display: none;">
                @foreach ($investments as $projectId => $projectInvestments)
                    @php 
                        $firstInv = $projectInvestments->first();
                        $project = $firstInv && $firstInv->project ? $firstInv->project : null;
                        // If project is null but we have project_id, try to load it with legacy connection
                        if (!$project && $firstInv && $firstInv->project_id) {
                            $projectIdValue = (int) $firstInv->project_id;
                            $project = \App\Models\Project::on('legacy')
                                ->where('id', $projectIdValue)
                                ->first();
                            
                            // If still null, try loading by external project_id as fallback
                            if (!$project) {
                                $project = \App\Models\Project::on('legacy')
                                    ->where('project_id', $projectIdValue)
                                    ->first();
                            }
                        }
                    @endphp
                    @php $payouts = $projectPayouts[$projectId] ?? collect(); @endphp
                    @php $totalPaid = $payouts->where('paid', 1)->sum('amount'); @endphp
                    
                    @if($payouts->count())
                        <div class="mb-6 bg-white border border-gray-200 rounded-lg overflow-hidden">
                            <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-4 text-white">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-xl font-bold">{{ $project ? ($project->name ?? 'Project #' . ($project->project_id ?? $project->id)) : 'Unknown Project' }}</h3>
                                    <div class="text-right">
                                        <p class="text-sm text-green-100">Total Paid</p>
                                        <p class="text-2xl font-bold">{!! money($totalPaid) !!}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Due On</th>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Amount</th>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($payouts as $payout)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        @php
                                                            $dueOn = optional($payout->quarterlyUpdate)->due_on;
                                                            if ($dueOn) {
                                                                if (is_string($dueOn)) {
                                                                    try {
                                                                        $dueOn = \Carbon\Carbon::parse($dueOn);
                                                                    } catch (\Exception $e) {
                                                                        $dueOn = null;
                                                                    }
                                                                }
                                                                if ($dueOn && ($dueOn instanceof \Carbon\Carbon || $dueOn instanceof \DateTime)) {
                                                                    echo $dueOn->format('d M Y');
                                                                } else {
                                                                    echo '—';
                                                                }
                                                            } else {
                                                                echo '—';
                                                            }
                                                        @endphp
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-lg font-semibold text-gray-900">
                                                        {!! money($payout->amount ?? 0) !!}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @if($payout->paid)
                                                            <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800">
                                                                @if($payout->paid_on)
                                                                    @php
                                                                        $paidOn = $payout->paid_on;
                                                                        if ($paidOn) {
                                                                            if (is_string($paidOn)) {
                                                                                try {
                                                                                    $paidOn = \Carbon\Carbon::parse($paidOn);
                                                                                } catch (\Exception $e) {
                                                                                    $paidOn = null;
                                                                                }
                                                                            }
                                                                            if ($paidOn && ($paidOn instanceof \Carbon\Carbon || $paidOn instanceof \DateTime)) {
                                                                                echo 'Paid ' . $paidOn->format('d M Y');
                                                                            } else {
                                                                                echo 'Paid (Invalid date)';
                                                                            }
                                                                        }
                                                                    @endphp
                                                                @endif
                                                            </span>
                                                        @else
                                                            <span class="px-3 py-1 text-sm font-medium rounded-full bg-yellow-100 text-yellow-800">
                                                                Pending
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Email History Tab -->
            <div x-show="activeTab === 'email-history'" x-transition style="display: none;">
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold">Email History</h3>
                        <p class="text-sm text-gray-600 mt-1">All emails sent to you from the system</p>
                    </div>
                    @php
                        $hasEmailHistory = isset($emailHistory) && 
                                          ($emailHistory instanceof \Illuminate\Pagination\LengthAwarePaginator ? $emailHistory->total() > 0 : $emailHistory->count() > 0);
                    @endphp
                    @if($hasEmailHistory)
                        <div class="divide-y divide-gray-200">
                            @foreach($emailHistory as $email)
                                <div class="border-b border-gray-200 last:border-b-0" x-data="{ expanded: false }">
                                    <button 
                                        @click="expanded = !expanded"
                                        class="w-full px-6 py-4 hover:bg-gray-50 transition-colors text-left"
                                        type="button"
                                    >
                                        <div class="flex items-start gap-4">
                                            <div class="flex-shrink-0 mt-1">
                                                <i class="{{ $email->icon ?? 'fas fa-envelope' }} text-xl"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ 
                                                                ($email->email_type ?? '') === 'document' ? 'bg-brand-teal-light text-brand-teal-dark' : 
                                                                (($email->email_type ?? '') === 'project_update' ? 'bg-green-100 text-green-800' : 
                                                                (($email->email_type ?? '') === 'support_ticket' ? 'bg-purple-100 text-purple-800' : 
                                                                (($email->email_type ?? '') === 'payout' ? 'bg-emerald-100 text-emerald-800' : 
                                                                'bg-gray-100 text-gray-800'))) 
                                                            }}">
                                                                {{ $email->type_label ?? 'Email' }}
                                                            </span>
                                                            @if(isset($email->project) && $email->project)
                                                                <span class="text-xs text-gray-500">• {{ $email->project->name ?? 'Unknown Project' }}</span>
                                                            @endif
                                                        </div>
                                                        <h4 class="text-sm font-semibold text-gray-900 mb-1">
                                                            {{ $email->subject ?? 'No subject' }}
                                                        </h4>
                                                        <p class="text-xs text-gray-500">
                                                            To: {{ $email->recipient ?? 'N/A' }}
                                                        </p>
                                                    </div>
                                                    <div class="flex-shrink-0 text-right flex items-center gap-3">
                                                        <div>
                                                            <p class="text-xs text-gray-500 whitespace-nowrap">
                                                                @php
                                                                    $sentAt = $email->sent_at ?? null;
                                                                    if ($sentAt) {
                                                                        if (is_string($sentAt)) {
                                                                            try {
                                                                                $sentAt = \Carbon\Carbon::parse($sentAt);
                                                                            } catch (\Exception $e) {
                                                                                $sentAt = null;
                                                                            }
                                                                        }
                                                                        if ($sentAt && ($sentAt instanceof \Carbon\Carbon || $sentAt instanceof \DateTime)) {
                                                                            echo $sentAt->format('d M Y, H:i');
                                                                        } else {
                                                                            echo 'Unknown date';
                                                                        }
                                                                    } else {
                                                                        echo 'Unknown date';
                                                                    }
                                                                @endphp
                                                            </p>
                                                            @if(isset($email->sent_at) && $email->sent_at)
                                                                @php
                                                                    try {
                                                                        $diffForHumans = is_string($email->sent_at) ? \Carbon\Carbon::parse($email->sent_at)->diffForHumans() : $email->sent_at->diffForHumans();
                                                                        echo '<p class="text-xs text-gray-400 mt-1">' . $diffForHumans . '</p>';
                                                                    } catch (\Exception $e) {
                                                                        // Silently fail
                                                                    }
                                                                @endphp
                                                            @endif
                                                        </div>
                                                        <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="{ 'rotate-180': expanded }"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </button>
                                    <div x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="px-6 pb-4">
                                        <div class="ml-10 pt-2 border-t border-gray-200">
                                            @if(($email->email_type ?? '') === 'document')
                                                <div class="text-sm text-gray-700">
                                                    <p class="font-medium mb-2">Documents included in this email:</p>
                                                    @if(isset($email->documents) && $email->documents->count() > 0)
                                                        <ul class="list-disc list-inside space-y-1 text-gray-600">
                                                            @foreach($email->documents as $doc)
                                                                <li>{{ $doc->name ?? 'Document' }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <p class="text-gray-600">{{ $email->content ?? 'Documents were sent to you via email.' }}</p>
                                                    @endif
                                                </div>
                                            @elseif(($email->email_type ?? '') === 'project_update')
                                                <div class="text-sm text-gray-700 prose prose-sm max-w-none">
                                                    {!! nl2br(e($email->content ?? '')) !!}
                                                </div>
                                                @if(isset($email->images) && $email->images->count() > 0)
                                                    <div class="mt-4 grid grid-cols-2 gap-3">
                                                        @foreach($email->images as $image)
                                                            <a href="{{ $image->url }}" target="_blank" class="border border-gray-200 rounded-lg overflow-hidden bg-gray-50 hover:shadow-md transition-shadow block">
                                                                @if(isset($image->is_image) && $image->is_image)
                                                                    <img src="{{ $image->thumbnail_url ?? $image->url }}" alt="{{ $image->description ?? '' }}" class="w-full h-24 object-cover" onerror="this.onerror=null;this.src='{{ $image->url }}';">
                                                                @else
                                                                    <div class="flex flex-col items-center justify-center h-24 bg-white">
                                                                        <i class="{{ $image->icon ?? 'fas fa-file text-gray-400' }} text-2xl mb-1"></i>
                                                                        <span class="text-xs text-gray-600 text-center px-2 truncate w-full">{{ Str::limit($image->file_name ?? 'File', 15) }}</span>
                                                                    </div>
                                                                @endif
                                                                @if(isset($image->description) && $image->description)
                                                                    <div class="px-2 py-1 text-xs text-gray-600 border-t border-gray-200">{{ $image->description }}</div>
                                                                @endif
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @elseif(($email->email_type ?? '') === 'support_ticket')
                                                <div class="text-sm text-gray-700">
                                                    @if(isset($email->ticket_id))
                                                        <p class="font-medium mb-2">Ticket ID: <span class="font-mono">{{ $email->ticket_id }}</span></p>
                                                    @endif
                                                    <div class="prose prose-sm max-w-none">
                                                        {!! nl2br(e($email->content ?? '')) !!}
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-sm text-gray-700 prose prose-sm max-w-none">
                                                    {!! nl2br(e($email->content ?? 'Email content not available.')) !!}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if(isset($emailHistory) && $emailHistory instanceof \Illuminate\Pagination\LengthAwarePaginator && $emailHistory->hasPages())
                            <div class="px-6 py-4 border-t border-gray-200">
                                {{ $emailHistory->links() }}
                            </div>
                        @endif
                    @else
                        <div class="p-12 text-center">
                            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-600 font-medium mb-2">No email history yet</p>
                            <p class="text-sm text-gray-500">Emails sent to you from the system will appear here, including:</p>
                            <ul class="text-sm text-gray-500 mt-3 space-y-1">
                                <li>• Project update notifications</li>
                                <li>• Document delivery emails</li>
                                <li>• Support ticket confirmations</li>
                                <li>• Payout notifications</li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Helpdesk Tab -->
            <div x-show="activeTab === 'helpdesk'" x-transition style="display: none;" x-data="helpdeskData()">
                <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">Support Tickets</h3>
                            <p class="text-sm text-gray-600 mt-1">Create a support ticket or view existing ones</p>
                        </div>
                        <button 
                            @click="showCreateForm = !showCreateForm"
                            class="px-4 py-2 bg-brand-magenta text-white rounded-lg hover:bg-brand-magenta-dark font-medium"
                        >
                            <i class="fas fa-plus mr-2"></i>New Ticket
                        </button>
                    </div>

                    <!-- Create Ticket Form -->
                    <div x-show="showCreateForm" class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <h4 class="font-semibold mb-3">Create Support Ticket</h4>
                        <form @submit.prevent="createTicket" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Project (Optional)</label>
                                <select x-model="newTicket.project_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Select a project...</option>
                                    @foreach($investments as $projectId => $projectInvestments)
                                        @php 
                                            $firstInv = $projectInvestments->first();
                                            $project = $firstInv && $firstInv->project ? $firstInv->project : null;
                                        @endphp
                                        @if($project && $project->project_id)
                                        <option value="{{ $project->project_id }}">{{ $project->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Subject</label>
                                <input 
                                    type="text" 
                                    x-model="newTicket.subject" 
                                    required
                                    placeholder="e.g. Questions about Q3 payout"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Message</label>
                                <textarea 
                                    x-model="newTicket.message" 
                                    required
                                    rows="5"
                                    placeholder="Give us as much detail as possible..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                ></textarea>
                            </div>
                            <div class="flex gap-2">
                                <button 
                                    type="submit" 
                                    :disabled="creatingTicket"
                                    class="px-4 py-2 bg-brand-magenta text-white rounded-md hover:bg-brand-magenta-dark disabled:opacity-50 font-medium"
                                >
                                    <span x-show="!creatingTicket">Submit Ticket</span>
                                    <span x-show="creatingTicket">Creating...</span>
                                </button>
                                <button 
                                    type="button" 
                                    @click="showCreateForm = false"
                                    class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                        <div x-show="successMessage" class="mt-4 p-3 bg-green-50 border border-green-200 rounded-md text-sm text-green-800" x-html="successMessage"></div>
                    </div>

                    <!-- Tickets List -->
                    <div x-show="tickets.length === 0 && !loading" class="text-center py-12">
                        <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">No support tickets yet</p>
                        <p class="text-sm text-gray-400 mt-2">Create your first ticket to get started</p>
                    </div>

                    <div x-show="loading" class="text-center py-12">
                        <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
                        <p class="text-gray-500 mt-2">Loading tickets...</p>
                    </div>

                    <div class="space-y-4">
                        <template x-for="ticket in tickets" :key="ticket.id">
                            <div 
                                @click="selectedTicket = ticket"
                                :class="selectedTicket?.id === ticket.id ? 'border-brand-teal bg-brand-teal-light' : 'border-gray-200 hover:border-gray-300'"
                                class="p-4 bg-white border rounded-lg cursor-pointer transition-all"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h4 class="font-semibold text-gray-900" x-text="ticket.subject"></h4>
                                            <span 
                                                :class="ticket.status === 'open' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800'"
                                                class="px-2 py-0.5 text-xs font-medium rounded-full"
                                                x-text="ticket.status"
                                            ></span>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-2" x-text="ticket.message?.substring(0, 100) + '...'"></p>
                                        <div class="flex items-center gap-4 text-xs text-gray-500">
                                            <span><i class="fas fa-ticket-alt mr-1"></i>Ticket ID: <strong x-text="ticket.ticket_id"></strong></span>
                                            <span x-text="'Created: ' + formatDate(ticket.created_on)"></span>
                                            <span x-show="ticket.project" x-text="'Project: ' + ticket.project?.name"></span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <span 
                                            x-show="ticket.replies && ticket.replies.length > 0"
                                            class="px-2 py-1 text-xs font-medium bg-brand-teal-light text-brand-teal-dark rounded-full"
                                            x-text="ticket.replies.length + ' replies'"
                                        ></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Ticket Chat View -->
                    <div x-show="selectedTicket" class="mt-6 bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-gray-900" x-text="selectedTicket?.subject"></h4>
                            <button 
                                @click="selectedTicket = null"
                                class="text-gray-400 hover:text-gray-600"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <div class="max-h-96 overflow-y-auto space-y-4 mb-4">
                            <template x-for="reply in (selectedTicket?.replies || [])" :key="reply.id">
                                <div 
                                    :class="reply.is_from_support ? 'ml-12 bg-brand-purple-light' : 'mr-12 bg-gray-50'"
                                    class="p-3 rounded-lg"
                                >
                                    <div class="flex items-start justify-between mb-1">
                                        <span 
                                            :class="reply.is_from_support ? 'text-brand-purple-dark font-semibold' : 'text-gray-800 font-semibold'"
                                            class="text-sm"
                                            x-text="reply.is_from_support ? 'Support Team' : 'You'"
                                        ></span>
                                        <span class="text-xs text-gray-500" x-text="formatDate(reply.created_on)"></span>
                                    </div>
                                    <p class="text-sm text-gray-700 whitespace-pre-wrap" x-text="reply.message"></p>
                                </div>
                            </template>
                        </div>

                        <form @submit.prevent="sendReply(selectedTicket.ticket_id)" class="flex gap-2">
                            <input 
                                type="text"
                                x-model="replyMessages[selectedTicket.ticket_id]"
                                placeholder="Type your reply..."
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md"
                            />
                            <button 
                                type="submit"
                                :disabled="sendingReply === selectedTicket.ticket_id"
                                class="px-4 py-2 bg-brand-magenta text-white rounded-md hover:bg-brand-magenta-dark disabled:opacity-50"
                            >
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Account Sharing Tab -->
            <div x-show="activeTab === 'sharing'" x-transition style="display: none;" x-data="sharingData()">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Share Access Section -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">Share Your Access</h3>
                            <i class="fas fa-share-alt text-brand-teal"></i>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Allow another account to view your investments, documents, and payouts.</p>
                        
                        <form @submit.prevent="shareAccess" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Email Address</label>
                                <input 
                                    type="email" 
                                    x-model="shareEmail" 
                                    required
                                    placeholder="Enter email address"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-brand-teal focus:border-brand-teal"
                                />
                                <p class="text-xs text-gray-500 mt-1">The account must already exist in the system</p>
                            </div>
                            <button 
                                type="submit" 
                                :disabled="sharing"
                                class="w-full px-4 py-2 bg-brand-magenta text-white rounded-md hover:bg-brand-magenta-dark disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                            >
                                <span x-show="!sharing">Share Access</span>
                                <span x-show="sharing">Sharing...</span>
                            </button>
                        </form>

                        <div x-show="shareSuccess" class="mt-4 p-3 bg-green-50 border border-green-200 rounded-md text-sm text-green-800">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span x-text="shareMessage"></span>
                        </div>
                        <div x-show="shareError" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md text-sm text-red-800">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span x-text="shareErrorMessage"></span>
                        </div>

                        <!-- Shared Accounts List -->
                        <div class="mt-6 pt-6 border-t">
                            <h4 class="text-sm font-semibold mb-3">Accounts You've Shared With</h4>
                            <div x-show="sharedByMe.length === 0" class="text-sm text-gray-500 text-center py-4">
                                No shared accounts yet
                            </div>
                            <div class="space-y-2">
                                <template x-for="share in sharedByMe" :key="share.id">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                        <div>
                                            <p class="text-sm font-medium" x-text="share.shared_account?.name || share.shared_account?.email"></p>
                                            <p class="text-xs text-gray-500" x-text="share.shared_account?.email"></p>
                                            <p class="text-xs text-gray-400 mt-1" x-text="'Shared on ' + formatDate(share.accepted_on || share.invited_on)"></p>
                                        </div>
                                        <button 
                                            @click="revokeShare(share.id)"
                                            class="px-3 py-1 text-xs text-red-600 hover:text-red-800 font-medium"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Shared With Me Section -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">Shared With Me</h3>
                            <i class="fas fa-users text-green-500"></i>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Accounts that have shared their investments with you.</p>
                        
                        <div x-show="sharedWithMe.length === 0" class="text-sm text-gray-500 text-center py-8">
                            <i class="fas fa-inbox text-3xl text-gray-300 mb-2"></i>
                            <p>No shared access yet</p>
                        </div>
                        <div class="space-y-3">
                            <template x-for="share in sharedWithMe" :key="share.id">
                                <div class="p-4 bg-gradient-to-r from-brand-teal-light to-brand-purple-light border border-brand-teal rounded-lg">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <p class="text-sm font-semibold" x-text="share.primary_account?.name || share.primary_account?.email"></p>
                                                <span class="px-2 py-0.5 text-xs font-medium bg-brand-teal-light text-brand-teal-dark rounded">Shared</span>
                                            </div>
                                            <p class="text-xs text-gray-600 mb-2" x-text="share.primary_account?.email"></p>
                                            <p class="text-xs text-gray-500" x-text="'Access granted on ' + formatDate(share.accepted_on || share.invited_on)"></p>
                                        </div>
                                        <button 
                                            @click="removeSharedAccess(share.id)"
                                            class="px-3 py-1 text-xs text-gray-600 hover:text-gray-800 font-medium"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status Widget -->
    @if(isset($systemStatus) && $systemStatus)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 mb-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    @if($systemStatus->status_type === 'error')
                        <i class="fas fa-exclamation-circle text-red-500 text-2xl"></i>
                    @elseif($systemStatus->status_type === 'warning')
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl"></i>
                    @elseif($systemStatus->status_type === 'success')
                        <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                    @elseif($systemStatus->status_type === 'maintenance')
                        <i class="fas fa-tools text-orange-500 text-2xl"></i>
                    @else
                        <i class="fas fa-info-circle text-brand-teal text-2xl"></i>
                    @endif
                </div>
                <div class="ml-4 flex-1">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-base font-semibold text-gray-900">
                            {{ $systemStatus->title }}
                        </h3>
                        <span class="text-xs text-gray-500">
                            @if($systemStatus->created_on)
                                {{ \Carbon\Carbon::parse($systemStatus->created_on)->format('d M Y, H:i') }}
                            @elseif($systemStatus->updated_on)
                                {{ \Carbon\Carbon::parse($systemStatus->updated_on)->format('d M Y, H:i') }}
                            @endif
                        </span>
                    </div>
                    <div class="text-sm text-gray-700">
                        {!! $systemStatus->message !!}
                    </div>
                    
                    @php
                        try {
                            $statusUpdates = $systemStatus->updates ?? collect();
                        } catch (\Exception $e) {
                            $statusUpdates = collect();
                        }
                    @endphp
                    
                    @if($statusUpdates->isNotEmpty())
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <h4 class="text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wide">Updates</h4>
                            <div class="space-y-2">
                                @foreach($statusUpdates->take(5) as $update)
                                    <div class="text-xs {{ $update->is_fixed ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }} border rounded p-2">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="font-medium text-gray-900">
                                                        {{ $update->account->name ?? 'System' }}
                                                    </span>
                                                    <span class="text-gray-500">
                                                        @php
                                                            $createdOn = $update->created_on ?? null;
                                                            if ($createdOn) {
                                                                if (is_string($createdOn)) {
                                                                    try {
                                                                        $createdOn = \Carbon\Carbon::parse($createdOn);
                                                                    } catch (\Exception $e) {
                                                                        $createdOn = null;
                                                                    }
                                                                }
                                                                if ($createdOn && ($createdOn instanceof \Carbon\Carbon || $createdOn instanceof \DateTime)) {
                                                                    echo $createdOn->format('d M Y, H:i');
                                                                }
                                                            }
                                                        @endphp
                                                    </span>
                                                    @if($update->is_fixed)
                                                        <span class="px-1.5 py-0.5 text-xs font-medium bg-green-100 text-green-800 rounded flex items-center gap-1">
                                                            <i class="fas fa-check-circle text-xs"></i> Fixed
                                                        </span>
                                                        @if($update->fixedBy)
                                                            <span class="text-gray-500 text-xs">
                                                                by {{ $update->fixedBy->name }}
                                                            </span>
                                                        @endif
                                                    @endif
                                                </div>
                                                <p class="text-gray-700">{{ $update->message }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Update Modal -->
    <div x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center" x-data="updateModal()">
        <div class="absolute inset-0 bg-black opacity-50" @click="close"></div>
        <div class="relative bg-white rounded-lg shadow-lg max-w-lg w-full p-6 z-10">
            <button @click="close" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
            <template x-if="loading">
                <div class="text-center py-8">Loading...</div>
            </template>
            <template x-if="!loading && update">
                <div>
                    <div class="mb-2 text-xs text-gray-500" x-text="update.sent_on"></div>
                    <div class="font-bold mb-2">Project Update</div>
                    <div class="prose mb-2" x-html="update.comment"></div>
                    <template x-if="update.images && update.images.length">
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <template x-for="img in update.images" :key="img.url">
                                <a :href="img.url" target="_blank" class="border border-gray-200 rounded-lg overflow-hidden bg-gray-50 hover:shadow-md transition-shadow block">
                                    <template x-if="img.is_image">
                                        <img :src="img.thumbnail_url || img.url" alt="" class="w-full h-24 object-cover" @error="$el.src = img.url">
                                    </template>
                                    <template x-if="!img.is_image">
                                        <div class="flex flex-col items-center justify-center h-24 bg-white">
                                            <i :class="img.icon || 'fas fa-file text-gray-400'" class="text-2xl mb-1"></i>
                                            <span class="text-xs text-gray-600 text-center px-2 truncate w-full" x-text="(img.file_name || 'File').substring(0, 15)"></span>
                                        </div>
                                    </template>
                                    <div class="px-2 py-1 text-[11px] text-gray-600 border-t border-gray-200" x-show="img.description" x-text="img.description"></div>
                                </a>
                            </template>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function updateModal() {
    return {
        open: false,
        loading: false,
        update: null,
        showUpdate(id) {
            this.open = true;
            this.loading = true;
            fetch(`/updates/${id}`)
                .then(r => r.json())
                .then(data => {
                    this.update = data;
                    this.loading = false;
                });
        },
        close() {
            this.open = false;
            this.update = null;
        }
    }
}

function helpdeskData() {
    return {
        tickets: @json($supportTickets ?? []),
        selectedTicket: null,
        showCreateForm: false,
        newTicket: {
            subject: '',
            message: '',
            project_id: ''
        },
        replyMessages: {},
        creatingTicket: false,
        sendingReply: null,
        ticketCreated: false,
        successMessage: '',
        loading: false,
        
        async createTicket() {
            this.creatingTicket = true;
            try {
                const response = await fetch('{{ route("investor.support.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.newTicket)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.successMessage = `Support ticket created successfully! Your ticket ID is: <strong>${data.ticket.ticket_id}</strong>`;
                    this.ticketCreated = true;
                    this.newTicket = { subject: '', message: '', project_id: '' };
                    await this.loadTickets();
                    // Auto-select the new ticket
                    setTimeout(() => {
                        this.selectedTicket = data.ticket;
                        const chatContainer = document.querySelector('[x-show="selectedTicket?.id === ticket.id"]');
                        if (chatContainer) {
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                        }
                    }, 100);
                } else {
                    alert('Error creating ticket: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Error creating ticket: ' + error.message);
            } finally {
                this.creatingTicket = false;
            }
        },
        
        async sendReply(ticketId) {
            if (!this.replyMessages[ticketId] || !this.replyMessages[ticketId].trim()) return;
            
            this.sendingReply = ticketId;
            try {
                const response = await fetch(`/investor/support/tickets/${this.selectedTicket.ticket_id}/reply`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: this.replyMessages[ticketId] })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.replyMessages[ticketId] = '';
                    await this.loadTickets();
                    // Scroll to bottom
                    setTimeout(() => {
                        const chatContainer = document.querySelector('[x-show="selectedTicket?.id === ticket.id"] .max-h-96');
                        if (chatContainer) {
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                        }
                    }, 100);
                } else {
                    alert('Error sending reply: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Error sending reply: ' + error.message);
            } finally {
                this.sendingReply = null;
            }
        },
        
        async loadTickets() {
            this.loading = true;
            try {
                const response = await fetch('{{ route("investor.support.index") }}', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                this.tickets = data;
                
                // Restore selected ticket if it exists
                if (this.selectedTicket) {
                    const found = this.tickets.find(t => t.id === this.selectedTicket.id);
                    if (found) {
                        this.selectedTicket = found;
                    }
                }
            } catch (error) {
                console.error('Error loading tickets:', error);
            } finally {
                this.loading = false;
            }
        },
        
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);
            
            if (minutes < 1) return 'Just now';
            if (minutes < 60) return `${minutes}m ago`;
            if (hours < 24) return `${hours}h ago`;
            if (days < 7) return `${days}d ago`;
            
            return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }
    }
}

function sharingData() {
    return {
        shareEmail: '',
        sharing: false,
        shareSuccess: false,
        shareError: false,
        shareMessage: '',
        shareErrorMessage: '',
        sharedWithMe: [],
        sharedByMe: [],
        
        async init() {
            await this.loadShares();
        },
        
        async loadShares() {
            try {
                const response = await fetch('{{ route("investor.account-shares.index") }}', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                this.sharedWithMe = data.shared_with_me || [];
                this.sharedByMe = data.shared_by_me || [];
            } catch (error) {
                console.error('Error loading shares:', error);
            }
        },
        
        async shareAccess() {
            this.sharing = true;
            this.shareSuccess = false;
            this.shareError = false;
            
            try {
                const response = await fetch('{{ route("investor.account-shares.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: this.shareEmail })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.shareSuccess = true;
                    this.shareMessage = data.message || 'Access shared successfully!';
                    this.shareEmail = '';
                    await this.loadShares();
                } else {
                    this.shareError = true;
                    this.shareErrorMessage = data.message || 'Failed to share access.';
                }
            } catch (error) {
                this.shareError = true;
                this.shareErrorMessage = 'Error sharing access: ' + error.message;
            } finally {
                this.sharing = false;
            }
        },
        
        async revokeShare(shareId) {
            if (!confirm('Are you sure you want to revoke access?')) return;
            
            try {
                const response = await fetch(`/investor/account-shares/${shareId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await this.loadShares();
                } else {
                    alert('Error revoking access: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Error revoking access: ' + error.message);
            }
        },
        
        async removeSharedAccess(shareId) {
            if (!confirm('Are you sure you want to remove this shared access?')) return;
            
            try {
                const response = await fetch(`/investor/account-shares/${shareId}/remove`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await this.loadShares();
                } else {
                    alert('Error removing access: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Error removing access: ' + error.message);
            }
        },
        
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
        }
    }
}
</script>
@endpush

@endif

@endsection
