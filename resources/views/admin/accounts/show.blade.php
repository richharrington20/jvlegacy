@extends('layouts.admin')

@section('title', 'Accounts - ' . $account->name )


@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold">Account Details</h1>
            <button onclick="document.getElementById('edit-form').classList.toggle('hidden')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Edit Details
            </button>
        </div>
        
        <div id="view-details">
        <div class="mb-2">
            <span class="font-semibold">Account Name:</span>
            <span>{{ $account->name }}</span>
        </div>
        <div class="mb-2">
            <span class="font-semibold">Account Email:</span>
            <span>{{ $account->email}}</span>
        </div>
        <div class="mb-2">
            <span class="font-semibold">Account Type:</span>
                <span>{{ ucfirst($account->type->name ?? 'Unknown') }}</span>
        </div>
            @if($account->person)
            <div class="mb-2">
                <span class="font-semibold">First Name:</span>
                    <span>{{ $account->person->first_name ?? '—' }}</span>
            </div>
            <div class="mb-2">
                <span class="font-semibold">Last Name:</span>
                    <span>{{ $account->person->last_name ?? '—' }}</span>
                </div>
                <div class="mb-2">
                    <span class="font-semibold">Telephone:</span>
                    <span>{{ $account->person->telephone_number ?? '—' }}</span>
            </div>
            @elseif($account->company)
            <div class="mb-2">
                <span class="font-semibold">Company Name:</span>
                    <span>{{ $account->company->name ?? '—' }}</span>
                </div>
            @endif
        </div>

        <form id="edit-form" method="POST" action="{{ route('admin.accounts.update', $account->id) }}" class="hidden space-y-4">
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

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Changes</button>
                <button type="button" onclick="document.getElementById('edit-form').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50">Cancel</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Admin Actions</h2>
        
        <div class="mb-4 pb-4 border-b border-gray-200">
            <form action="{{ route('admin.accounts.masquerade', $account->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-purple-600 text-white rounded px-4 py-2 hover:bg-purple-700 font-semibold">
                    View as Investor
                </button>
            </form>
            <p class="text-sm text-gray-600 mt-2">See exactly what this investor sees on their dashboard</p>
        </div>
        
        <form action="{{ route('admin.accounts.updateType', $account->id) }}" method="POST" class="mb-4 flex flex-col sm:flex-row sm:items-center gap-2">
            @csrf
            <label for="type_id" class="font-semibold">Change Account Type:</label>
            <select name="type_id" id="type_id" class="border border-gray-300 rounded px-3 py-2">
                @foreach(\App\Models\AccountType::on('legacy')->orderBy('name')->get() as $type)
                    <option value="{{ $type->id }}" @selected($account->type_id == $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-blue-600 text-white rounded px-4 py-2 hover:bg-blue-700">Update Type</button>
        </form>
        <form action="{{ route('admin.accounts.updatePassword', $account->id) }}" method="POST" class="flex flex-col sm:flex-row sm:items-center gap-2">
            @csrf
            <label for="password" class="font-semibold">Change Password:</label>
            <input type="password" name="password" id="password" class="border border-gray-300 rounded px-3 py-2" placeholder="New password" required>
            <input type="password" name="password_confirmation" class="border border-gray-300 rounded px-3 py-2" placeholder="Confirm password" required>
            <button type="submit" class="bg-blue-600 text-white rounded px-4 py-2 hover:bg-blue-700">Update Password</button>
        </form>
        @if(session('status'))
            <div class="mt-2 text-green-600">{{ session('status') }}</div>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Investment Portfolio</h2>
        
        @if($projectInvestments->isEmpty())
            <div class="text-center py-12 px-4">
                <div class="max-w-md mx-auto">
                    <div class="mb-4">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Investments Yet</h3>
                    <p class="text-gray-600 mb-6">This investor hasn't made any investments yet. Help them get started by exploring our available projects.</p>
                    
                    @if($availableProjects->isNotEmpty())
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                            <h4 class="font-semibold text-blue-900 mb-3">Available Investment Opportunities</h4>
                            <div class="space-y-3">
                                @foreach($availableProjects as $project)
                                    <div class="flex items-center justify-between bg-white rounded p-3 border border-blue-100">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $project->name }}</p>
                                            <p class="text-sm text-gray-600">Project ID: {{ $project->project_id }} • {{ \App\Models\Project::STATUS_MAP[$project->status] ?? 'Active' }}</p>
                                        </div>
                                        <a href="{{ route('admin.projects.show', $project->project_id) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                                            View Project
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <div class="flex gap-3 justify-center">
                        <a href="{{ route('admin.investments.create') }}?account_id={{ $account->id }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                            Create Investment
                        </a>
                        <a href="{{ route('admin.projects.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-semibold">
                            Browse All Projects
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="space-y-6">
                @foreach($projectInvestments as $projectData)
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                @if($projectData['project_id'])
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                        <a href="{{ route('admin.projects.show', $projectData['project_id']) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                            {{ $projectData['project_name'] }}
                                        </a>
                                    </h3>
                                    <p class="text-sm text-gray-600">Project ID: {{ $projectData['project_id'] }}</p>
                                @else
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $projectData['project_name'] }}</h3>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-gray-900">{!! money($projectData['total_invested']) !!}</p>
                                <p class="text-xs text-gray-500">Total Invested</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <div class="bg-green-50 rounded p-3 border border-green-200">
                                <p class="text-sm text-green-700 font-medium">Paid</p>
                                <p class="text-lg font-bold text-green-900">{!! money($projectData['total_paid']) !!}</p>
                                <p class="text-xs text-green-600">{{ $projectData['paid_count'] }} investment(s)</p>
                            </div>
                            <div class="bg-red-50 rounded p-3 border border-red-200">
                                <p class="text-sm text-red-700 font-medium">Unpaid</p>
                                <p class="text-lg font-bold text-red-900">{!! money($projectData['total_unpaid']) !!}</p>
                                <p class="text-xs text-red-600">{{ $projectData['unpaid_count'] }} investment(s)</p>
                            </div>
                            <div class="bg-blue-50 rounded p-3 border border-blue-200">
                                <p class="text-sm text-blue-700 font-medium">Total Investments</p>
                                <p class="text-lg font-bold text-blue-900">{{ $projectData['investment_count'] }}</p>
                                <p class="text-xs text-blue-600">across this project</p>
                            </div>
                            <div class="bg-gray-50 rounded p-3 border border-gray-200">
                                <p class="text-sm text-gray-700 font-medium">Status</p>
                                <p class="text-lg font-bold text-gray-900">
                                    @if($projectData['unpaid_count'] > 0)
                                        <span class="text-orange-600">Pending</span>
                                    @else
                                        <span class="text-green-600">Complete</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-600">
                                    {{ $projectData['paid_count'] }}/{{ $projectData['investment_count'] }} paid
                                </p>
                            </div>
                        </div>
                        
                        <details class="mt-4">
                            <summary class="cursor-pointer text-sm font-medium text-gray-700 hover:text-gray-900">
                                View Individual Investments ({{ $projectData['investment_count'] }})
                            </summary>
                            <div class="mt-4 overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Paid On</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($projectData['investments'] as $inv)
                                            <tr>
                                                <td class="px-3 py-2">{{ $inv->id }}</td>
                                                <td class="px-3 py-2">{!! money($inv->amount) !!}</td>
                                                <td class="px-3 py-2 whitespace-nowrap">{{ $inv->type_label }}</td>
                                                <td class="px-3 py-2">
                                @if($inv->paid)
                                    <span class="text-green-600 font-semibold">Yes</span>
                                @else
                                    <span class="text-red-600 font-semibold">No</span>
                                @endif
                            </td>
                                                <td class="px-3 py-2">{{ human_date($inv->paid_on) }}</td>
                        </tr>
                                        @endforeach
                </tbody>
            </table>
        </div>
                        </details>
                    </div>
                @endforeach
            </div>
        @endif
    </div>


</div>
@endsection
