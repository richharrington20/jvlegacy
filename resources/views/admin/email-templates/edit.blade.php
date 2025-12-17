@extends('layouts.admin')

@section('title', 'Edit Email Template')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Edit Email Template</h2>
            <p class="text-sm text-gray-500 font-mono">{{ $template->key }}</p>
        </div>
        <a href="{{ route('admin.email-templates.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
            ← Back to templates
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.email-templates.update', $template->id) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name', $template->name) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <input type="text" name="subject" value="{{ old('subject', $template->subject) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
            </div>

            @if(count($variables) > 0)
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="text-sm font-semibold text-blue-900 mb-2">Available Variables</h4>
                    <p class="text-xs text-blue-700 mb-2">Use these variables in your template with <code class="bg-white px-1 rounded">{{ '{{variable_name}}' }}</code> syntax:</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($variables as $key => $description)
                            <div class="text-xs">
                                <code class="bg-white px-2 py-1 rounded border border-blue-200">{{ '{{' . $key . '}}' }}</code>
                                <span class="text-blue-600 ml-1">- {{ $description }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">HTML body</label>
                    <textarea name="body_html" rows="20" class="w-full px-3 py-2 border border-gray-300 rounded-md font-mono text-xs" placeholder="Enter HTML email content...">{{ old('body_html', $template->body_html) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        Full HTML email content. Use inline styles where necessary. Variables: <code>{{ '{{variable_name}}' }}</code>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plain text body</label>
                    <textarea name="body_text" rows="20" class="w-full px-3 py-2 border border-gray-300 rounded-md font-mono text-xs" placeholder="Enter plain text email content...">{{ old('body_text', $template->body_text) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        Fallback text version for clients that do not render HTML. Variables: <code>{{ '{{variable_name}}' }}</code>
                    </p>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-semibold">
                    Save template
                </button>
                <a href="{{ route('admin.email-templates.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection


