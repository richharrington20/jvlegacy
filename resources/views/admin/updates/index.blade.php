{{-- resources/views/admin/investments/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Updates')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Project Updates</h2>
                <p class="text-sm text-gray-500 mt-1.5">Create and manage project updates for investors</p>
            </div>
        </div>

        <!-- New Update Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Post an Update</h3>
            <form method="POST" action="{{ route('admin.updates.store') }}" id="update-form" class="space-y-4" enctype="multipart/form-data">
                @csrf
                <div class="flex flex-wrap gap-4">
                    <div class="w-full md:w-1/2">
                        <label class="block text-sm font-medium mb-1 text-gray-700">Project <span class="text-red-500">*</span></label>
                        <select name="project_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="" hidden>Select Project</option>
                            @foreach ($projects as $proj)
                                @if ($proj->project_id)
                                    <option value="{{ $proj->project_id }}">{{ $proj->project_id }} – {{ $proj->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full md:w-1/2">
                        <label class="block text-sm font-medium mb-1 text-gray-700">Image (optional)</label>
                        <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700">Update Content <span class="text-red-500">*</span></label>
                    <div id="quill-editor" class="bg-white border border-gray-300 rounded-lg" style="min-height: 150px;"></div>
                    <input type="hidden" name="comment" id="comment-input">
                </div>
                <div>
                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all font-medium shadow-sm hover:shadow-md">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Post Update
                    </button>
                </div>
            </form>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="w-full md:w-48">
                    <label class="block text-sm font-medium mb-1 text-gray-700">Project</label>
                    <select name="project_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                    <label class="block text-sm font-medium mb-1 text-gray-700">Category</label>
                    <input type="number" name="category" value="{{ request('category') }}" placeholder="Category number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="w-full md:w-auto flex gap-2">
                    <button type="submit" class="h-10 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors">Filter</button>
                    <a href="{{ route('admin.updates.index') }}" class="h-10 px-4 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center justify-center font-medium transition-colors">Clear</a>
                </div>
            </form>
        </div>
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

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <p class="text-sm text-gray-600">
                Showing {{ $updates->firstItem() ?? 0 }} to {{ $updates->lastItem() ?? 0 }} of {{ $updates->total() }} results
            </p>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Project</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Content</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Sent</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
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

    <div class="mt-6">
        {{ $updates->links() }}
    </div>
@endsection
