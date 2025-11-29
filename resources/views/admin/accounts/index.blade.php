@extends('layouts.admin')

@section('title', 'Accounts')

@section('content')
<div class="container mx-auto px-4 ">
    <h1 class="text-2xl font-bold mb-4">Accounts</h1>
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
    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-800">
            <thead class="bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
            <tr>
                <th class="px-4 py-2">ID</th>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Type</th>
                <th class="px-4 py-2">Total Paid</th>
                <th class="px-4 py-2">Total Unpaid</th>
                <th></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach ($accounts as $account)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 whitespace-nowrap">{{ $account->id }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">
                        <a href="{{ route('admin.accounts.show', $account->id) }}" class="text-blue-600 hover:underline">
                            {{ $account->name }}
                        </a>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $account->type_name }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $account->total_paid }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $account->total_unpaid }}</td>
                    <td>
                        <form action="{{ route('admin.accounts.masquerade', $account->id) }}" method="POST" onsubmit="return confirm('Masquerade as this user?')">
                            @csrf
                            <button type="submit" class="text-sm text-blue-600 hover:underline">Masquerade</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
    <div class="mt-4">
        {{ $accounts->links() }}
    </div>
@endsection
