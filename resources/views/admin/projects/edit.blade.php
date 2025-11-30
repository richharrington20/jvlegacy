@extends('layouts.admin')

@section('title', 'Edit Project: ' . $project->name)

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.projects.show', $project->project_id) }}" class="inline-flex items-center text-blue-600 hover:text-blue-900 font-medium">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Project
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Project</h1>

        @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.projects.update', $project->project_id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project Name</label>
                    <input type="text" name="name" value="{{ old('name', $project->name) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account/Owner</label>
                    <select name="account_id" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" @selected(old('account_id', $project->account_id) == $account->id)>
                                {{ $account->name }} ({{ $account->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <div id="description-editor" style="height: 300px;"></div>
                    <textarea name="description" id="description" class="hidden">{{ old('description', $project->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        @foreach(\App\Models\Project::STATUS_MAP as $statusId => $statusName)
                            <option value="{{ $statusId }}" @selected(old('status', $project->status) == $statusId)>
                                {{ $statusName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Progress (%)</label>
                    <input type="number" name="progress" value="{{ old('progress', $project->progress ?? 0) }}" min="0" max="100" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project Image</label>
                    @if($project->image_path)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $project->image_path) }}" alt="Project image" class="max-w-xs rounded-lg">
                        </div>
                    @endif
                    <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Update Project
                    </button>
                    <a href="{{ route('admin.projects.show', $project->project_id) }}" class="px-6 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script>
        var quill = new Quill('#description-editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });

        // Set initial content
        quill.root.innerHTML = document.getElementById('description').value;

        // Update hidden textarea before form submit
        document.querySelector('form').addEventListener('submit', function() {
            document.getElementById('description').value = quill.root.innerHTML;
        });
    </script>
    @endpush
@endsection

