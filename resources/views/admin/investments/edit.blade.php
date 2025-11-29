@extends('layouts.admin')

@section('title', 'Edit Investment')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.investments.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-900 font-medium">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Investments
        </a>
    </div>

    <div class="mb-4">
        <h2 class="text-2xl font-bold text-gray-900">Edit Investment #{{ $investment->id }}</h2>
        <p class="text-sm text-gray-600 mt-1">Update investment details</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.investments.update', $investment->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Project <span class="text-red-500">*</span></label>
                    <select name="project_id" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        <option value="">Select Project</option>
                        @foreach ($projects as $project)
                            @if($project->project_id)
                                <option value="{{ $project->project_id }}" @selected(($investment->project_id_display ?? $investment->project->project_id ?? null) == $project->project_id)>
                                    {{ $project->project_id }} – {{ $project->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('project_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Account (Investor) <span class="text-red-500">*</span></label>
                    <select name="account_id" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        <option value="">Select Account</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}" @selected($investment->account_id == $account->id)>
                                #{{ $account->id }} – {{ $account->name }} ({{ $account->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('account_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Amount (£) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" step="0.01" min="0" value="{{ number_format($investment->amount / 100, 2, '.', '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    @error('amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Type</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="0" @selected($investment->type == 0)>Equity</option>
                        <option value="1" @selected($investment->type == 1)>Debt</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Transfer ID</label>
                    <input type="number" name="transfer_id" value="{{ $investment->transfer_id ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Pay In ID</label>
                    <input type="number" name="pay_in_id" value="{{ $investment->pay_in_id ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="paid" value="1" @checked($investment->paid) class="mr-2">
                        <span class="text-sm font-medium">Mark as Paid</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-4 mt-6">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Update Investment
                </button>
                <a href="{{ route('admin.investments.index') }}" class="px-6 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                    Cancel
                </a>
                <form method="POST" action="{{ route('admin.investments.destroy', $investment->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this investment?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Delete
                    </button>
                </form>
            </div>
        </form>
    </div>

    @if ($errors->any())
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection

