@extends('layouts.admin')

@section('title', 'Edit Update')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.updates.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Updates</a>
    </div>

    <div class="bg-white rounded shadow p-6">
        <h2 class="text-2xl font-bold mb-6">Edit Update #{{ $update->id }}</h2>

        <form method="POST" action="{{ route('admin.updates.update', $update->id) }}" id="update-form" class="space-y-4" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="flex flex-wrap gap-4">
                <div class="w-full md:w-1/2">
                    <label class="block text-sm font-medium mb-1">Project <span class="text-red-500">*</span></label>
                    <select name="project_id" class="w-full px-3 py-2 border border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 rounded-md" required>
                        <option value="" hidden>Select Project</option>
                        @foreach ($projects as $proj)
                            @if ($proj->project_id)
                                <option value="{{ $proj->project_id }}" @selected($update->project_id == $proj->project_id)>
                                    {{ $proj->project_id }} – {{ $proj->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-1/2">
                    <label class="block text-sm font-medium mb-1">Category</label>
                    <input type="number" name="category" value="{{ $update->category ?? 3 }}" class="w-full px-3 py-2 border border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 rounded-md">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Update Content <span class="text-red-500">*</span></label>
                <div id="quill-editor" class="bg-white border border-gray-300 rounded" style="min-height: 200px;">{!! $update->comment !!}</div>
                <input type="hidden" name="comment" id="comment-input" value="{{ $update->comment }}">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Image (optional)</label>
                <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <p class="text-xs text-gray-500 mt-1">Upload an image to include in the update (JPG, PNG, GIF). Leave blank to keep existing image.</p>
                @if($update->image_path ?? false)
                    <div class="mt-2">
                        <p class="text-xs text-gray-600">Current image:</p>
                        <img src="{{ $update->image_path }}" alt="Update image" class="mt-1 max-w-xs rounded">
                    </div>
                @endif
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Update</button>
                <a href="{{ route('admin.updates.show', $update->id) }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">Cancel</a>
            </div>
        </form>
    </div>

    @push('scripts')
    <!-- Quill.js CDN -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var quill = new Quill('#quill-editor', {
                theme: 'snow',
                placeholder: 'Write your project update here...'
            });
            var form = document.getElementById('update-form');
            form.addEventListener('submit', function (e) {
                document.getElementById('comment-input').value = quill.root.innerHTML;
            });
        });
    </script>
    @endpush
@endsection

