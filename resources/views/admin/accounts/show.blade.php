@extends('layouts.admin')

@section('title', $account->name)

@section('content')
<div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Invested -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Invested</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{!! money($totalInvested) !!}</p>
                </div>
                <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-pound-sign text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Paid -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Paid</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{!! money($totalPaid) !!}</p>
                </div>
                <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Outstanding -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Outstanding</p>
                    <p class="text-3xl font-bold text-amber-600 mt-2">{!! money($totalUnpaid) !!}</p>
                </div>
                <div class="h-12 w-12 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Investment Count -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Investments</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($investmentCount) }}</p>
                </div>
                <div class="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Details & Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Account Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Account Details Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Account Information</h2>
                    <button onclick="document.getElementById('edit-form').classList.toggle('hidden')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        <i class="fas fa-edit mr-2"></i>Edit Details
                    </button>
                </div>
                
                <div id="view-details" class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase mb-1">Account Name</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $account->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase mb-1">Email</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $account->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase mb-1">Account Type</p>
                        <p class="text-sm font-semibold text-gray-900">{{ ucfirst($account->type->name ?? 'Unknown') }}</p>
                    </div>
                    @if($account->person)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase mb-1">First Name</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $account->person->first_name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase mb-1">Last Name</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $account->person->last_name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase mb-1">Telephone</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $account->person->telephone_number ?? '—' }}</p>
                        </div>
                    @elseif($account->company)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase mb-1">Company Name</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $account->company->name ?? '—' }}</p>
                        </div>
                    @endif
                </div>

                <form id="edit-form" method="POST" action="{{ route('admin.accounts.update', $account->id) }}" class="hidden space-y-4 mt-6 pt-6 border-t border-gray-200">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ $account->email }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>

                    @if($account->person)
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">First Name</label>
                                <input type="text" name="first_name" value="{{ $account->person->first_name ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Last Name</label>
                                <input type="text" name="last_name" value="{{ $account->person->last_name ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Telephone</label>
                            <input type="text" name="telephone_number" value="{{ $account->person->telephone_number ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    @elseif($account->company)
                        <div>
                            <label class="block text-sm font-medium mb-1">Company Name</label>
                            <input type="text" name="company_name" value="{{ $account->company->name ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    @endif

                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">Save Changes</button>
                        <button type="button" onclick="document.getElementById('edit-form').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Investment Portfolio -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Investment Portfolio</h2>
                
                @if($projectInvestments->isEmpty())
                    <div class="text-center py-12 px-4">
                        <div class="max-w-md mx-auto">
                            <div class="mb-4">
                                <i class="fas fa-chart-line text-6xl text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Investments Yet</h3>
                            <p class="text-gray-600 mb-6">This investor hasn't made any investments yet.</p>
                            
                            <div class="flex gap-3 justify-center">
                                <a href="{{ route('admin.investments.create') }}?account_id={{ $account->id }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                                    <i class="fas fa-plus mr-2"></i>Create Investment
                                </a>
                                <a href="{{ route('admin.projects.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-semibold">
                                    Browse Projects
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($projectInvestments as $projectData)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        @if($projectData['project_id'])
                                            <h3 class="text-base font-semibold text-gray-900 mb-1">
                                                <a href="{{ route('admin.projects.show', $projectData['project_id']) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                                    {{ $projectData['project_name'] }}
                                                </a>
                                            </h3>
                                            <p class="text-xs text-gray-500">Project ID: {{ $projectData['project_id'] }}</p>
                                        @else
                                            <h3 class="text-base font-semibold text-gray-900 mb-1">{{ $projectData['project_name'] }}</h3>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xl font-bold text-gray-900">{!! money($projectData['total_invested']) !!}</p>
                                        <p class="text-xs text-gray-500">Total Invested</p>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-4 gap-3">
                                    <div class="bg-green-50 rounded p-2 border border-green-200">
                                        <p class="text-xs text-green-700 font-medium">Paid</p>
                                        <p class="text-sm font-bold text-green-900">{!! money($projectData['total_paid']) !!}</p>
                                    </div>
                                    <div class="bg-red-50 rounded p-2 border border-red-200">
                                        <p class="text-xs text-red-700 font-medium">Unpaid</p>
                                        <p class="text-sm font-bold text-red-900">{!! money($projectData['total_unpaid']) !!}</p>
                                    </div>
                                    <div class="bg-blue-50 rounded p-2 border border-blue-200">
                                        <p class="text-xs text-blue-700 font-medium">Count</p>
                                        <p class="text-sm font-bold text-blue-900">{{ $projectData['investment_count'] }}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded p-2 border border-gray-200">
                                        <p class="text-xs text-gray-700 font-medium">Status</p>
                                        <p class="text-sm font-bold {{ $projectData['unpaid_count'] > 0 ? 'text-orange-600' : 'text-green-600' }}">
                                            {{ $projectData['unpaid_count'] > 0 ? 'Pending' : 'Complete' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                <div class="space-y-3">
                    <form action="{{ route('admin.accounts.masquerade', $account->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium text-sm">
                            <i class="fas fa-user-secret mr-2"></i>View as Investor
                        </button>
                    </form>
                    <a href="{{ route('admin.investments.create') }}?account_id={{ $account->id }}" class="block w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm text-center">
                        <i class="fas fa-plus mr-2"></i>Create Investment
                    </a>
                </div>
            </div>

            <!-- Account Settings -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Account Settings</h2>
                
                <form action="{{ route('admin.accounts.updateType', $account->id) }}" method="POST" class="mb-4">
                    @csrf
                    <label for="type_id" class="block text-sm font-medium text-gray-700 mb-2">Account Type</label>
                    <div class="flex gap-2">
                        <select name="type_id" id="type_id" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            @foreach(\App\Models\AccountType::on('legacy')->orderBy('name')->get() as $type)
                                <option value="{{ $type->id }}" @selected($account->type_id == $type->id)>{{ $type->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                            <i class="fas fa-save"></i>
                        </button>
                    </div>
                </form>
                
                <form action="{{ route('admin.accounts.updatePassword', $account->id) }}" method="POST">
                    @csrf
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Change Password</label>
                    <input type="password" name="password" id="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 text-sm" placeholder="New password" required>
                    <input type="password" name="password_confirmation" class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 text-sm" placeholder="Confirm password" required>
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm">
                        <i class="fas fa-key mr-2"></i>Update Password
                    </button>
                </form>
                
                @if(session('status'))
                    <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Email History -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Email History</h2>
            <span class="text-sm text-gray-500">{{ $emailHistory->count() }} email(s) sent</span>
        </div>
        
        @if($emailHistory->isEmpty())
            <div class="text-center py-8">
                <i class="fas fa-envelope text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">No emails have been sent to this account yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent By</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($emailHistory as $email)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    {{ $email->sent_at ? $email->sent_at->format('d M Y, H:i') : '—' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    @if($email->project)
                                        <a href="{{ route('admin.projects.show', $email->project->project_id) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                            {{ $email->project->name }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $email->document_name ?? 'Multiple Documents' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    {{ $email->recipient ?? $account->email }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    @if($email->sent_by)
                                        @php
                                            $sentByAccount = \App\Models\Account::find($email->sent_by);
                                        @endphp
                                        {{ $sentByAccount->name ?? 'System' }}
                                    @else
                                        System
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
