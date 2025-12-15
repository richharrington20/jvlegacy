@extends('layouts.admin')

@php
use Illuminate\Support\Str;
@endphp

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

            @if($update->images && $update->images->count())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Files & Images</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach($update->images as $image)
                            <div class="border border-gray-200 rounded-lg overflow-hidden bg-gray-50 hover:shadow-md transition-shadow">
                                @if($image->is_image)
                                    <a href="{{ $image->url }}" target="_blank" class="block">
                                        <img src="{{ $image->thumbnail_url }}" 
                                             alt="{{ $image->file_name }}" 
                                             class="w-full h-32 object-cover"
                                             onerror="this.onerror=null; this.src='{{ $image->url }}';">
                                    </a>
                                @else
                                    <a href="{{ $image->url }}" target="_blank" class="block flex flex-col items-center justify-center h-32 bg-white hover:bg-gray-50 transition-colors">
                                        <i class="{{ $image->icon }} text-4xl mb-2"></i>
                                        <span class="text-xs text-gray-600 text-center px-2">{{ Str::limit($image->file_name, 20) }}</span>
                                    </a>
                                @endif
                                @if($image->description)
                                    <div class="px-3 py-2 text-xs text-gray-700 border-t border-gray-200">
                                        {{ $image->description }}
                                    </div>
                                @endif
                                <div class="px-3 py-1 text-xs text-gray-500 border-t border-gray-200 flex items-center justify-between">
                                    <span class="truncate">{{ $image->file_name }}</span>
                                    @if($image->file_size)
                                        <span class="ml-2 text-gray-400">({{ number_format($image->file_size / 1024, 1) }} KB)</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold mb-3">Actions</h3>
            <div class="flex gap-2">
                <a href="{{ route('admin.updates.bulk_email_preflight', $update->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Send Email to Investors</a>
                <form method="POST" action="{{ route('admin.updates.resend', $update->id) }}" class="inline" onsubmit="return confirm('Resend this update email to all investors?');">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        <i class="fas fa-redo mr-1"></i>Resend Update
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.updates.selective_email', $update->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Send Test Email</button>
                </form>
            </div>
        </div>
    </div>
@endsection

