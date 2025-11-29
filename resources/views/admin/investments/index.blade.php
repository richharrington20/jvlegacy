{{-- resources/views/admin/investments/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Investments')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Investments</h2>
                <p class="text-sm text-gray-600 mt-1">Manage and track all investment records</p>
            </div>
            <a href="{{ route('admin.investments.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                <i class="fas fa-plus mr-2"></i>
                Create Investment
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="GET" class="mb-4 flex flex-wrap gap-4 items-end">

            <div class="w-full md:w-48">
                <label class="block text-sm font-medium mb-1">Project</label>
                <select name="project_id" class="w-full px-3 py-2 border border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 rounded-md">
                    <option value="">All Projects</option>
                    @foreach ($projects as $proj)
                        @if( $proj->project_id )
                        <option value="{{ $proj->project_id }}" @selected(request('project_id') == $proj->project_id)>
                            {{ $proj->project_id }} – {{ $proj->name }}
                        </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-48">
                <label class="block text-sm font-medium mb-1">Paid</label>
                <select name="paid" class="w-full px-3 py-2 border border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 rounded-md">
                    <option value="">Any</option>
                    <option value="1" @selected(request('paid') === '1')>Yes</option>
                    <option value="0" @selected(request('paid') === '0')>No</option>
                </select>
            </div>

            <div class="w-full md:w-48">
                <label class="block text-sm font-medium mb-1">Search Name</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Investor name..." class="w-full px-3 py-2 border border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 rounded-md">
            </div>

            <div class="w-full md:w-auto flex gap-2">
                <button type="submit" class="h-10 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">Filter</button>
                <a href="{{ route('admin.investments.index') }}" class="h-10 px-4 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 flex items-center justify-center">Clear</a>
            </div>

        </form>
        <div class="w-full md:w-auto mt-2 md:mt-6">
            <a href="{{ route('admin.investments.export', request()->query()) }}"
               class="inline-block p-3 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1">
                Export CSV
            </a>
        </div>

    </div>

        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Project</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Account</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Transfer</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Pay In</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Paid</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Paid On</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reserved Until</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($investments as $inv)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $inv->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($inv->project && $inv->project->project_id)
                                    <a href="{{ route('admin.projects.show', $inv->project->project_id) }}" class="text-blue-600 hover:text-blue-900 hover:underline font-medium">
                                        {{ $inv->project->project_id }} – {{ $inv->project->name ?? '—' }}
                                    </a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($inv->account && $inv->account->id)
                                    <a href="{{ route('admin.accounts.show', ['id' => $inv->account->id]) }}" class="text-blue-600 hover:text-blue-900 hover:underline">
                                        {!! $inv->account->type_icon ?? '' !!}
                                        {{ $inv->account->name ?? '—' }}
                                    </a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $inv->transfer_id ?: '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $inv->pay_in_id ?: '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{!! money($inv->amount) !!}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $inv->type == 1 ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $inv->type_label ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($inv->paid)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Paid
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ human_date($inv->paid_on) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ human_date($inv->reserved_until) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('admin.investments.edit', $inv->id) }}" class="text-blue-600 hover:text-blue-900 hover:underline font-medium">
                                    <i class="fas fa-edit mr-1"></i>
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <i class="fas fa-inbox text-4xl mb-3"></i>
                                    <p class="text-sm">No investments found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $investments->links() }}
    </div>


    <div class="mt-4">
        {{ $investments->links() }}
    </div>

@endsection
