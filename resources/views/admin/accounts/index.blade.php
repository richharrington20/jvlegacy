@extends('layouts.admin')

@section('title', 'Accounts')

@section('content')
<div class="mb-6">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900">Accounts</h1>
        <p class="text-sm text-gray-600 mt-1">Manage investor and company accounts</p>
    </div>
    <form method="GET" class="mb-4 flex flex-col sm:flex-row sm:items-center sm:space-x-4">
        <input type="text" name="search" id="search" placeholder="Search by company name"
               value="{{ request('search') }}"
               class="border border-gray-300 rounded px-3 py-2 mb-2 sm:mb-0 w-full sm:w-64" />

        <select name="type_filter" id="type_filter"
                class="border border-gray-300 rounded px-3 py-2 w-full sm:w-48">
            <option value="">All Types</option>
            @foreach ($accountTypes as $type)
                <option value="{{ $type->id }}" @selected(request('type_filter') == $type->id)>
                    {{ $type->name }}
                </option>
            @endforeach
        </select>

        <button type="submit"
                class="bg-blue-600 text-white rounded px-4 py-2 mt-2 sm:mt-0 hover:bg-blue-700">
            Filter
        </button>
    </form>
    <div class="mt-4">
        {{ $accounts->links() }}
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Paid</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Unpaid</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($accounts as $account)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $account->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('admin.accounts.show', $account->id) }}" class="text-blue-600 hover:text-blue-900 hover:underline font-medium">
                                {{ $account->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">{{ $account->type_name }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">{{ $account->total_paid }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600">{{ $account->total_unpaid }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <form action="{{ route('admin.accounts.masquerade', $account->id) }}" method="POST" class="inline" onsubmit="return confirm('Masquerade as this user?');">
                                @csrf
                                <button type="submit" class="text-blue-600 hover:text-blue-900 hover:underline font-medium">
                                    <i class="fas fa-user-secret mr-1"></i>
                                    Masquerade
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
    <div class="mt-4">
        {{ $accounts->links() }}
    </div>
@endsection
