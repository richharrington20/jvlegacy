@extends('layouts.admin')

@section('title', 'View Update')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.updates.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Updates</a>
    </div>

    <div class="bg-white rounded shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold">Update #{{ $update->id }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.updates.edit', $update->id) }}" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">Edit</a>
                <form method="POST" action="{{ route('admin.updates.destroy', $update->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this update? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
                </form>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Project</label>
                <p class="mt-1 text-gray-900">{{ $update->project_id }} – {{ $update->project->name ?? '—' }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <p class="mt-1 text-gray-900">{{ $update->category }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Sent On</label>
                <p class="mt-1 text-gray-900">{{ $update->sent_on ? $update->sent_on->format('d M Y H:i') : 'Not sent' }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                <div class="mt-1 p-4 bg-gray-50 rounded border border-gray-200 prose max-w-none">
                    {!! $update->comment !!}
                </div>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold mb-3">Actions</h3>
            <div class="flex gap-2">
                <a href="{{ route('admin.updates.bulk_email_preflight', $update->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Send Email to Investors</a>
                <form method="POST" action="{{ route('admin.updates.selective_email', $update->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Send Test Email</button>
                </form>
            </div>
        </div>
    </div>
@endsection

