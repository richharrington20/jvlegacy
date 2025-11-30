@extends('layouts.admin')

@section('title', 'Project: ' . $project->name)

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.projects.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-900 font-medium">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Projects
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $project->name }}</h1>
                <p class="text-sm text-gray-600 mt-1">Project ID: {{ $project->project_id }}</p>
                <p class="text-sm text-gray-600">Status: {{ \App\Models\Project::STATUS_MAP[$project->status] ?? 'Unknown' }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.projects.edit', $project->project_id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                    <i class="fas fa-edit mr-2"></i>Edit Project
                </a>
                <a href="{{ route('admin.investments.create') }}?project_id={{ $project->project_id }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                    <i class="fas fa-plus mr-2"></i>Add Investment
                </a>
                <a href="{{ route('admin.updates.index') }}?project_id={{ $project->project_id }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium">
                    <i class="fas fa-bullhorn mr-2"></i>Post Update
                </a>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-600 mb-1">Total Invested</div>
            <div class="text-2xl font-bold text-gray-900">{!! money($totalInvested) !!}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $investmentCount }} investment{{ $investmentCount !== 1 ? 's' : '' }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-600 mb-1">Total Paid</div>
            <div class="text-2xl font-bold text-green-600">{!! money($totalPaid) !!}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $paidCount }} paid</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-600 mb-1">Total Unpaid</div>
            <div class="text-2xl font-bold text-red-600">{!! money($totalUnpaid) !!}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $unpaidCount }} unpaid</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-600 mb-1">Progress</div>
            <div class="text-2xl font-bold text-blue-600">{{ $project->progress ?? 0 }}%</div>
            <div class="text-xs text-gray-500 mt-1">Status: {{ \App\Models\Project::STATUS_MAP[$project->status] ?? 'Unknown' }}</div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- Investors Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-900">Investors ({{ $investors->count() }})</h2>
            <a href="{{ route('admin.investments.create') }}?project_id={{ $project->project_id }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                <i class="fas fa-plus mr-2"></i>Add Investment
            </a>
        </div>

        @if($investors->isEmpty())
            <p class="text-gray-500">No investors found for this project.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Invested</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Investments</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($investors as $investor)
                            @php
                                $investorInvestments = $investments->where('account_id', $investor->id);
                                $totalAmount = $investorInvestments->sum('amount');
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.accounts.show', $investor->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                        {!! $investor->type_icon ?? '' !!}
                                        {{ $investor->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $investor->email }}</td>
                                <td class="px-4 py-3 font-semibold">{!! money($totalAmount) !!}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $investorInvestments->count() }}</td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('admin.projects.resend_documents', $project->project_id) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="account_id" value="{{ $investor->id }}">
                                        <button type="submit" class="text-xs bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                            Resend Docs
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200">
                <form method="POST" action="{{ route('admin.projects.resend_documents', $project->project_id) }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 font-semibold">
                        Resend Documents to All Investors
                    </button>
                </form>
                <p class="text-sm text-gray-600 mt-2">This will send all project documents to all {{ $investors->count() }} investors.</p>
            </div>
        @endif
    </div>

    <!-- Updates Section -->
    <div class="bg-white rounded shadow p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-900">Project Updates</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.updates.index') }}?project_id={{ $project->project_id }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium">
                    <i class="fas fa-plus mr-2"></i>Post Update
                </a>
                <a href="{{ route('admin.updates.index', ['project_id' => $project->project_id]) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    View All →
                </a>
            </div>
        </div>

        @if($updates->isEmpty())
            <p class="text-gray-500">No updates posted yet.</p>
        @else
            <div class="space-y-4">
                @foreach($updates->take(10) as $update)
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs text-gray-500">{{ $update->sent_on ? $update->sent_on->format('d M Y H:i') : 'Not sent' }}</span>
                                    <a href="{{ route('admin.updates.show', $update->id) }}" class="text-xs text-blue-600 hover:underline">
                                        View Details →
                                    </a>
                                </div>
                                <div class="prose prose-sm max-w-none">
                                    {!! \Illuminate\Support\Str::limit(strip_tags($update->comment), 200) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($updates->hasMorePages())
                <div class="mt-4">
                    {{ $updates->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- Documents Section -->
    <div class="bg-white rounded shadow p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-900">Documents</h2>
            @if($project->investorDocuments->isNotEmpty())
                <form method="POST" action="{{ route('admin.projects.resend_documents', $project->project_id) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        <i class="fas fa-envelope mr-2"></i>Resend All Documents
                    </button>
                </form>
            @endif
        </div>

        @if($project->investorDocuments->isEmpty())
            <p class="text-gray-500">No documents available for this project.</p>
        @else
            <div class="space-y-2">
                @foreach($project->investorDocuments as $document)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <div>
                            <span class="font-medium text-gray-900">{{ $document->name ?? 'Document' }}</span>
                            <span class="text-xs text-gray-500 ml-2">
                                Created: {{ $document->created_on ? $document->created_on->format('d M Y') : '—' }}
                            </span>
                        </div>
                        <a href="{{ $document->url }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm hover:underline">
                            View/Download →
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Document Email Logs -->
    @if($documentLogs->isNotEmpty())
        <div class="bg-white rounded shadow p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Document Email History</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sent To</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Document</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sent At</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($documentLogs->take(20) as $log)
                            <tr>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.accounts.show', $log->account_id) }}" class="text-blue-600 hover:underline">
                                        {{ $log->recipient }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $log->document_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $log->sent_at ? $log->sent_at->format('d M Y H:i') : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection

