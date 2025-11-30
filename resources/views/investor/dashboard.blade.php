@extends('layouts.app')

@section('content')
<div class="mx-auto mt-10" x-data="{ activeTab: 'overview' }">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Welcome, {{ $account->name }}</h1>
            <p class="text-gray-600 mt-1">Manage your investments and track your portfolio</p>
        </div>
        <button onclick="document.getElementById('profile-edit-form').classList.toggle('hidden')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
            <i class="fas fa-user-edit mr-2"></i>Edit Profile
        </button>
    </div>

    <!-- Masquerading Banner -->
    @if (session()->has('masquerading_as'))
        <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-blue-800 font-semibold">Masquerading as account #{{ session('masquerading_as') }}</p>
                    <p class="text-sm text-blue-700">Any actions you take will affect this investor.</p>
                </div>
                <form method="POST" action="{{ route('admin.investor.stopMasquerade') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Stop Masquerading
                    </button>
                </form>
            </div>
        </div>
    @endif

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
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Changes</button>
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
                    :class="activeTab === 'overview' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                >
                    <i class="fas fa-home mr-2"></i>Overview
                </button>
                <button 
                    @click="activeTab = 'investments'"
                    :class="activeTab === 'investments' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                >
                    <i class="fas fa-chart-line mr-2"></i>Investments
                </button>
                <button 
                    @click="activeTab = 'documents'"
                    :class="activeTab === 'documents' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                >
                    <i class="fas fa-file-alt mr-2"></i>Documents
                </button>
                <button 
                    @click="activeTab = 'payouts'"
                    :class="activeTab === 'payouts' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                >
                    <i class="fas fa-pound-sign mr-2"></i>Payouts
                </button>
                <button 
                    @click="activeTab = 'notifications'"
                    :class="activeTab === 'notifications' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors relative"
                >
                    <i class="fas fa-bell mr-2"></i>Notifications
                    @if($notifications->where('read_at', null)->count() > 0)
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            {{ $notifications->where('read_at', null)->count() }}
                        </span>
                    @endif
                </button>
                <button 
                    @click="activeTab = 'helpdesk'"
                    :class="activeTab === 'helpdesk' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors relative"
                >
                    <i class="fas fa-headset mr-2"></i>Helpdesk
                    @if(isset($supportTickets) && $supportTickets->where('status', 'open')->count() > 0)
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            {{ $supportTickets->where('status', 'open')->count() }}
                        </span>
                    @endif
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Overview Tab -->
            <div x-show="activeTab === 'overview'" x-transition>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-600 mb-1">Total Invested</p>
                                <p class="text-2xl font-bold text-blue-900">
                                    @php
                                        $totalInvested = collect($investments)->flatten()->sum('amount');
                                    @endphp
                                    {!! money($totalInvested) !!}
                                </p>
                            </div>
                            <i class="fas fa-chart-pie text-blue-400 text-3xl"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-600 mb-1">Total Paid</p>
                                <p class="text-2xl font-bold text-green-900">
                                    @php
                                        $allPayouts = collect($projectPayouts)->flatten();
                                        $totalPaid = $allPayouts->where('paid', 1)->sum('amount');
                                    @endphp
                                    {!! money($totalPaid) !!}
                                </p>
                            </div>
                            <i class="fas fa-check-circle text-green-400 text-3xl"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-6 border border-purple-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-purple-600 mb-1">Active Projects</p>
                                <p class="text-2xl font-bold text-purple-900">{{ count($investments) }}</p>
                            </div>
                            <i class="fas fa-folder-open text-purple-400 text-3xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg border border-blue-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-3 text-gray-900">Welcome to Your Dashboard</h3>
                    <p class="text-gray-700 mb-4">
                        Your central hub for managing investments, accessing documents, tracking payouts, and getting support. 
                        Everything you need is just a click away.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="#investments" @click="activeTab = 'investments'" class="p-4 bg-white rounded-lg border border-blue-100 hover:border-blue-300 hover:shadow-md transition-all">
                            <div class="flex items-center">
                                <div class="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-chart-line text-blue-600"></i>
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
                        <div class="space-y-3">
                            @php
                                $recentUpdates = collect();
                                foreach ($projectUpdates as $updates) {
                                    $recentUpdates = $recentUpdates->merge($updates->items());
                                }
                                $recentUpdates = $recentUpdates->sortByDesc('sent_on')->take(3);
                            @endphp
                            @if($recentUpdates->count() > 0)
                                @foreach($recentUpdates as $update)
                                    <div class="flex items-start p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <i class="fas fa-bullhorn text-blue-500 mt-1 mr-3"></i>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">Project Update</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ $update->sent_on ? $update->sent_on->format('d M Y') : '' }}</p>
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
                    @php $project = $projectInvestments->first()->project; @endphp
                    @php $timeline = $projectTimelines[$projectId] ?? null; @endphp
                    
                    <div class="mb-8 bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-2xl font-bold mb-2">{{ $project->name ?? 'Unknown Project' }}</h3>
                                    <p class="text-blue-100">Project ID: {{ $project->project_id ?? 'N/A' }}</p>
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
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
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
                                <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                                    <h4 class="font-semibold mb-3 text-blue-900">Project Updates</h4>
                                    <div class="space-y-3">
                                        @foreach($updates->take(3) as $update)
                                            <div class="bg-white p-3 rounded border border-blue-100">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex-1">
                                                        <p class="text-xs text-gray-500 mb-1">{{ $update->sent_on ? $update->sent_on->format('d M Y') : '' }}</p>
                                                        <p class="text-sm text-gray-900">{!! nl2br(e(Str::limit($update->comment_preview ?? '', 150))) !!}</p>
                                                    </div>
                                                    <button 
                                                        class="ml-3 text-blue-600 hover:text-blue-800 text-xs font-medium" 
                                                        @click="showUpdate({{ $update->id }})"
                                                        type="button"
                                                    >Read more</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($updates->count() > 3)
                                        <a href="{{ route('public.projects.show', $project->project_id) }}" class="mt-3 inline-block text-sm text-blue-600 hover:underline">
                                            View all {{ $updates->count() }} updates
                                        </a>
                                    @endif
                                </div>
                            @endif

                            @if($timeline)
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="font-semibold text-gray-900">Payment Timeline</h4>
                                        <div class="text-right">
                                            <p class="text-xs text-gray-500 uppercase">Forecast Payout</p>
                                            <p class="text-lg font-bold text-gray-900">
                                                @if($timeline['expected_payout'])
                                                    {{ $timeline['expected_payout']->format('d M Y') }}
                                                @else
                                                    TBC
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        @foreach($timeline['stages'] as $stage)
                                            <div class="flex items-center justify-between px-3 py-2 bg-white rounded border {{ $stage['completed'] ? 'border-green-300' : 'border-gray-200' }}">
                                                <div>
                                                    <p class="text-sm font-medium {{ $stage['completed'] ? 'text-green-800' : 'text-gray-700' }}">{{ $stage['label'] }}</p>
                                                    <p class="text-xs text-gray-500">{{ $stage['date'] ? $stage['date']->format('d M Y') : 'Pending' }}</p>
                                                </div>
                                                @if($stage['completed'])
                                                    <i class="fas fa-check-circle text-green-600"></i>
                                                @else
                                                    <i class="fas fa-circle text-gray-300"></i>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <dialog id="support-modal-{{ $projectId }}" class="rounded-lg w-full max-w-2xl p-0">
                        <form method="POST" action="{{ route('investor.support.store', $project->project_id) }}">
                            @csrf
                            <div class="p-6 border-b">
                                <h4 class="text-xl font-semibold">Support Request · {{ $project->name }}</h4>
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
                                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded hover:bg-blue-700">Send request</button>
                            </div>
                        </form>
                    </dialog>
                @endforeach
            </div>

            <!-- Documents Tab -->
            <div x-show="activeTab === 'documents'" x-transition style="display: none;">
                @foreach ($investments as $projectId => $projectInvestments)
                    @php $project = $projectInvestments->first()->project; @endphp
                    @php $documents = $projectDocuments[$projectId] ?? collect(); @endphp
                    @php $documentLogs = $projectDocumentLogs[$projectId] ?? collect(); @endphp
                    
                    @if($documents->count())
                        <div class="mb-6 bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-xl font-semibold mb-4">{{ $project->name ?? 'Unknown Project' }}</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-4">
                                @foreach($documents as $document)
                                    <a href="{{ $document->url }}" target="_blank" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-blue-300 transition-all group">
                                        <i class="{{ $document->icon }} text-4xl mb-2 group-hover:scale-110 transition-transform {{ $document->status_type === 'pdf' ? 'text-red-500' : ($document->status_type === 'word' ? 'text-blue-500' : 'text-gray-500') }}"></i>
                                        <span class="text-xs text-gray-600 group-hover:text-gray-900 text-center font-medium">
                                            {{ Str::limit($document->name ?? 'Document', 20) }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                            <div class="flex items-center gap-4 pt-4 border-t">
                                <form method="POST" action="{{ route('investor.documents.email', $project->project_id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                        <i class="fas fa-envelope mr-2"></i>Email me these documents
                                    </button>
                                </form>
                                @if($documentLogs->count())
                                    <span class="text-xs text-gray-500">
                                        Last emailed: {{ $documentLogs->first()->sent_at?->format('d M Y H:i') ?? 'Never' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Payouts Tab -->
            <div x-show="activeTab === 'payouts'" x-transition style="display: none;">
                @foreach ($investments as $projectId => $projectInvestments)
                    @php $project = $projectInvestments->first()->project; @endphp
                    @php $payouts = $projectPayouts[$projectId] ?? collect(); @endphp
                    @php $totalPaid = $payouts->where('paid', 1)->sum('amount'); @endphp
                    
                    @if($payouts->count())
                        <div class="mb-6 bg-white border border-gray-200 rounded-lg overflow-hidden">
                            <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-4 text-white">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-xl font-bold">{{ $project->name ?? 'Unknown Project' }}</h3>
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
                                                        {{ optional($payout->quarterlyUpdate)->due_on ? $payout->quarterlyUpdate->due_on->format('d M Y') : '—' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-lg font-semibold text-gray-900">
                                                        {!! money($payout->amount ?? 0) !!}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @if($payout->paid)
                                                            <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800">
                                                                Paid {{ $payout->paid_on ? $payout->paid_on->format('d M Y') : '' }}
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

            <!-- Notifications Tab -->
            <div x-show="activeTab === 'notifications'" x-transition style="display: none;">
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Notifications</h3>
                        @if($notifications->count())
                            <form method="POST" action="{{ route('investor.notifications.read_all') }}">
                                @csrf
                                <button type="submit" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    Mark all read
                                </button>
                            </form>
                        @endif
                    </div>
                    @if($notifications->isEmpty())
                        <div class="p-12 text-center">
                            <i class="fas fa-bell-slash text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500">No notifications yet.</p>
                        </div>
                    @else
                        <ul class="divide-y divide-gray-200">
                            @foreach($notifications as $notification)
                                <li class="px-6 py-4 hover:bg-gray-50 transition-colors {{ $notification->read_at ? 'bg-gray-50' : 'bg-white' }}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <p class="text-sm {{ $notification->read_at ? 'text-gray-500' : 'text-gray-900 font-medium' }}">
                                                {{ $notification->message }}
                                            </p>
                                            <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at?->format('d M Y H:i') }}</p>
                                            @if($notification->link)
                                                <a href="{{ $notification->link }}" class="text-xs text-blue-600 hover:underline mt-1 inline-block">View</a>
                                            @endif
                                        </div>
                                        @if(!$notification->read_at)
                                            <form method="POST" action="{{ route('investor.notifications.read', $notification->id) }}" class="ml-4">
                                                @csrf
                                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                                    Mark read
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

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
</script>
@endpush

@endsection
