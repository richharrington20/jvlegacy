@extends('layouts.admin')

@section('title', 'Create Project')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.projects.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Projects</a>
    </div>

    <div class="mb-4">
        <h2 class="text-2xl font-bold text-gray-900">Create New Project</h2>
        <p class="text-sm text-gray-600 mt-1">Add a new investment project to the system</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.projects.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Project Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Account (Owner) <span class="text-red-500">*</span></label>
                    <select name="account_id" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        <option value="">Select Account</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>
                                #{{ $account->id }} – {{ $account->name }} ({{ $account->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('account_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Initial Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    @foreach(\App\Models\Project::STATUS_MAP as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', \App\Models\Project::STATUS_NOT_SUBMITTED) == $key)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Project Image (optional)</label>
                <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <p class="text-xs text-gray-500 mt-1">Upload a project image (JPG, PNG, GIF - max 5MB)</p>
                @error('image')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-4 mt-6">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Create Project
                </button>
                <a href="{{ route('admin.projects.index') }}" class="px-6 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                    Cancel
                </a>
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

