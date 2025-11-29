{{-- resources/views/admin/investments/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Updates')

@section('content')
    <div class="mb-6">
        <div class="mb-4">
            <h2 class="text-2xl font-bold text-gray-900">Project Updates</h2>
            <p class="text-sm text-gray-600 mt-1">Create and manage project updates for investors</p>
        </div>

        <!-- New Update Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Post an Update</h3>
        <form method="POST" action="{{ route('admin.updates.store') }}" id="update-form" class="mb-6 space-y-4" enctype="multipart/form-data">
            @csrf
            <div class="flex flex-wrap gap-4">
                <div class="w-full md:w-1/2">
                    <label class="block text-sm font-medium mb-1">Project <span class="text-red-500">*</span></label>
                    <select name="project_id" class="w-full px-3 py-2 border border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 rounded-md" required>
                        <option value="" hidden>Select Project</option>
                        @foreach ($projects as $proj)
                            @if ($proj->project_id)
                                <option value="{{ $proj->project_id }}">{{ $proj->project_id }} – {{ $proj->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-1/2">
                    <input type="number" hidden name="category" value="3" class="w-full px-3 py-2 border border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 rounded-md">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Update Content <span class="text-red-500">*</span></label>
                <div id="quill-editor" class="bg-white border border-gray-300 rounded" style="min-height: 150px;"></div>
                <input type="hidden" name="comment" id="comment-input">
            </div>
            <div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Post Update</button>
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

    @if ($errors->any())
        <div class="mb-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <div>
        <form method="GET" class="mb-4 flex flex-wrap gap-4 items-end">

            <div class="w-full md:w-48">
                <label class="block text-sm font-medium mb-1">Project</label>
                <select name="project_id" class="w-full px-3 py-2 border border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 rounded-md">
                    <option value="">All Projects</option>
                    @foreach ($projects as $proj)
                        @if ($proj->project_id)
                            <option value="{{ $proj->project_id }}" @selected(request('project_id') == $proj->project_id)>
                                {{ $proj->project_id }} – {{ $proj->name }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-48">
                <label class="block text-sm font-medium mb-1">Category</label>
                <input type="number" name="category" value="{{ request('category') }}" class="w-full px-3 py-2 border border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 rounded-md">
            </div>

            <div class="w-full md:w-auto flex gap-2">
                <button type="submit" class="h-10 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">Filter</button>
                <a href="{{ route('admin.updates.index') }}" class="h-10 px-4 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 flex items-center justify-center">Clear</a>
            </div>

        </form>
    </div>

    <div class="mt-4">
        {{ $updates->links() }}
    </div>

        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Project</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Content</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Sent</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($updates as $update)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $update->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($update->project_id)
                                <a href="{{ route('admin.projects.show', $update->project_id) }}" class="text-blue-600 hover:text-blue-900 hover:underline font-medium">
                                    {{ $update->project_id }} – {{ $update->project->name ?? '—' }}
                                </a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">{{ $update->category }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <div class="max-w-md truncate">{{ $update->comment_preview }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ human_date($update->sent_on) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.updates.show', $update->id) }}" class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded hover:bg-gray-200 transition-colors">
                                    <i class="fas fa-eye mr-1"></i>
                                    View
                                </a>
                                <a href="{{ route('admin.updates.edit', $update->id) }}" class="inline-flex items-center px-2 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded hover:bg-yellow-200 transition-colors">
                                    <i class="fas fa-edit mr-1"></i>
                                    Edit
                                </a>
                                <a href="{{ route('admin.updates.bulk_email_preflight', $update->id) }}" class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded hover:bg-blue-200 transition-colors">
                                    <i class="fas fa-envelope mr-1"></i>
                                    Email
                                </a>
                                <form method="POST" action="{{ route('admin.updates.destroy', $update->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this update?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded hover:bg-red-200 transition-colors">
                                        <i class="fas fa-trash mr-1"></i>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="text-gray-400">
                                <i class="fas fa-bullhorn text-4xl mb-3"></i>
                                <p class="text-sm">No updates found.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $updates->links() }}
    </div>
@endsection
