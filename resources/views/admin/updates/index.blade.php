{{-- resources/views/admin/investments/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Updates')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-sm text-gray-500">Create and manage project updates for investors</p>
            </div>
        </div>

        <!-- New Update Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Post an Update</h3>
            <form method="POST" action="{{ route('admin.updates.store') }}" id="update-form" class="space-y-4" enctype="multipart/form-data">
                @csrf
                <div class="flex flex-wrap gap-4">
                    <div class="w-full">
                        <label class="block text-sm font-medium mb-1 text-gray-700">Project <span class="text-red-500">*</span></label>
                        <select name="project_id" id="project-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="" hidden>Select Project</option>
                            @foreach ($projects as $proj)
                                @if ($proj->project_id)
                                    <option value="{{ $proj->project_id }}" @selected($selectedProjectId == $proj->project_id)>
                                        {{ $proj->project_id }} – {{ $proj->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full">
                        <label class="block text-sm font-medium mb-1 text-gray-700">Files & Images (optional - multiple allowed)</label>
                        <input type="file" name="images[]" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv" multiple id="image-input" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">You can select multiple files (images, PDFs, Word docs, Excel files, etc.). Images will be automatically resized. You can add descriptions and reorder them after upload.</p>
                        <div id="image-preview-container" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
                        <div id="image-descriptions-container" class="mt-4 space-y-2"></div>
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
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
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

            // Auto-select project from URL
            const urlParams = new URLSearchParams(window.location.search);
            const projectId = urlParams.get('project_id');
            if (projectId) {
                document.getElementById('project-select').value = projectId;
            }

            // Handle multiple file previews (images and documents)
            const imageInput = document.getElementById('image-input');
            const previewContainer = document.getElementById('image-preview-container');
            const descriptionsContainer = document.getElementById('image-descriptions-container');
            let imageFiles = [];

            function getFileIcon(file) {
                const extension = file.name.split('.').pop().toLowerCase();
                const mimeType = file.type;
                
                if (mimeType.startsWith('image/') || ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(extension)) {
                    return '<i class="fas fa-image text-blue-500 text-3xl"></i>';
                } else if (extension === 'pdf' || mimeType.includes('pdf')) {
                    return '<i class="fas fa-file-pdf text-red-500 text-3xl"></i>';
                } else if (['doc', 'docx'].includes(extension) || mimeType.includes('word')) {
                    return '<i class="fas fa-file-word text-blue-600 text-3xl"></i>';
                } else if (['xls', 'xlsx'].includes(extension) || mimeType.includes('excel') || mimeType.includes('spreadsheet')) {
                    return '<i class="fas fa-file-excel text-green-600 text-3xl"></i>';
                } else if (['txt', 'csv'].includes(extension)) {
                    return '<i class="fas fa-file-alt text-gray-500 text-3xl"></i>';
                } else {
                    return '<i class="fas fa-file text-gray-400 text-3xl"></i>';
                }
            }

            function isImageFile(file) {
                const extension = file.name.split('.').pop().toLowerCase();
                return file.type.startsWith('image/') || ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(extension);
            }

            imageInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                imageFiles = files;
                
                previewContainer.innerHTML = '';
                descriptionsContainer.innerHTML = '';

                files.forEach((file, index) => {
                    const div = document.createElement('div');
                    div.className = 'relative border border-gray-300 rounded-lg overflow-hidden bg-white';
                    
                    if (isImageFile(file)) {
                        // Show image preview
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            div.innerHTML = `
                                <div class="relative">
                                    <img src="${e.target.result}" class="w-full h-32 object-cover" alt="Preview">
                                </div>
                                <div class="p-2 bg-gray-50">
                                    <p class="text-xs text-gray-600 truncate">${file.name}</p>
                                    <input type="text" name="image_descriptions[]" placeholder="File description (optional)" 
                                           class="mt-1 w-full px-2 py-1 text-xs border border-gray-300 rounded">
                                </div>
                            `;
                            previewContainer.appendChild(div);
                        };
                        reader.readAsDataURL(file);
                    } else {
                        // Show file icon for non-images
                        div.innerHTML = `
                            <div class="flex flex-col items-center justify-center h-32 bg-gray-50">
                                ${getFileIcon(file)}
                            </div>
                            <div class="p-2 bg-gray-50">
                                <p class="text-xs text-gray-600 truncate text-center">${file.name}</p>
                                <input type="text" name="image_descriptions[]" placeholder="File description (optional)" 
                                       class="mt-1 w-full px-2 py-1 text-xs border border-gray-300 rounded">
                            </div>
                        `;
                        previewContainer.appendChild(div);
                    }
                });
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
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-600">{{ human_date($update->sent_on) }}</span>
                                @if($update->sent == 1)
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800" title="Emails sent to investors">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Emailed
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800" title="Emails not yet sent">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        Not sent
                                    </span>
                                @endif
                            </div>
                        </td>
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
                                <form method="POST" action="{{ route('admin.updates.resend', $update->id) }}" class="inline" onsubmit="return confirm('Resend this update email to all investors?');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded hover:bg-green-200 transition-colors">
                                        <i class="fas fa-redo mr-1"></i>
                                        Resend
                                    </button>
                                </form>
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
