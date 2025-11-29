{{-- resources/views/admin/investments/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Investments')

@section('content')
    <h2 class="text-xl font-bold mb-4">Recent Investments</h2>

    <div class="bg-white p-4 rounded shadow mb-6">
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

    <div class="mt-4">
        {{ $investments->links() }}
    </div>


    <div class="bg-white rounded shadow" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <table class="divide-y divide-gray-200 text-sm text-gray-800" style="min-width: 1200px; width: 100%;">
            <thead class="bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
            <tr>
                <th class="px-4 py-2">ID</th>
                <th class="px-4 py-2">Project</th>
                <th class="px-4 py-2">Account</th>
                <th class="px-4 py-2">Transfer</th>
                <th class="px-4 py-2">Pay In</th>
                <th class="px-4 py-2">Amount</th>
                <th class="px-4 py-2">Type</th>
                <th class="px-4 py-2">Paid</th>
                <th class="px-4 py-2">Paid On</th>
                <th class="px-4 py-2">Reserved Until</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse ($investments as $inv)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 whitespace-nowrap">{{ $inv->id }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $inv->project_id }} – {{ $inv->project->name ?? '—' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">
                        <a href="{{ route ('admin.accounts.show' , [ 'id' => $inv->account->id ]) }}">
                            {!! $inv->account->type_icon ?? '' !!}
                            {{ $inv->account->name ?? '—' }}
                        </a>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $inv->transfer_id }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $inv->pay_in_id }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{!! money($inv->amount) !!}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $inv->type_label }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $inv->paid ? 'Yes' : 'No' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ human_date($inv->paid_on) }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ human_date($inv->reserved_until) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-4 py-2 text-center text-gray-500">No investments found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>


    <div class="mt-4">
        {{ $investments->links() }}
    </div>

@endsection
