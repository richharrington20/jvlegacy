@extends('layouts.admin')

@section('title', 'Edit System Status')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.system-status.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-900 font-medium">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to System Status
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit System Status</h1>

        @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.system-status.update', $status->id) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" value="{{ old('title', $status->title) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Type</label>
                    <select name="status_type" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        @foreach(\App\Models\SystemStatus::TYPE_MAP as $type => $label)
                            <option value="{{ $type }}" @selected(old('status_type', $status->status_type) == $type)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message (Full WYSIWYG Editor) <span class="text-red-500">*</span></label>
                    <div id="message-editor" class="bg-white border border-gray-300 rounded-lg" style="min-height: 300px;"></div>
                    <textarea name="message" id="message-input" class="hidden">{{ old('message', $status->message) }}</textarea>
                    <p class="text-xs text-red-600 mt-1 hidden" id="message-error">Please enter a message for the system status.</p>
                </div>

                <div class="flex items-center space-x-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $status->is_active) ? 'checked' : '' }} class="mr-2">
                        <span class="text-sm text-gray-700">Active</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="show_on_login" value="1" {{ old('show_on_login', $status->show_on_login) ? 'checked' : '' }} class="mr-2">
                        <span class="text-sm text-gray-700">Show on Login Page</span>
                    </label>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Update Status
                    </button>
                    <a href="{{ route('admin.system-status.index') }}" class="px-6 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <!-- Quill.js CDN -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var quill = new Quill('#message-editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        [{ 'font': [] }],
                        [{ 'size': [] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'script': 'sub'}, { 'script': 'super' }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        [{ 'direction': 'rtl' }],
                        [{ 'align': [] }],
                        ['link', 'image', 'video'],
                        ['blockquote', 'code-block'],
                        ['clean']
                    ]
                },
                placeholder: 'Enter your system status message here...'
            });

            // Set initial content
            quill.root.innerHTML = {!! json_encode(old('message', $status->message)) !!};

            // Update hidden textarea before form submit
            var form = document.querySelector('form');
            var messageInput = document.getElementById('message-input');
            var messageError = document.getElementById('message-error');
            
            form.addEventListener('submit', function(e) {
                var content = quill.root.innerHTML;
                var textContent = quill.getText().trim();
                
                // Check if editor has meaningful content
                if (!textContent || textContent === '') {
                    e.preventDefault();
                    e.stopPropagation();
                    messageError.classList.remove('hidden');
                    quill.focus();
                    // Scroll to editor
                    document.getElementById('message-editor').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }
                
                // Hide error if content exists
                messageError.classList.add('hidden');
                
                // Update hidden textarea with HTML content
                messageInput.value = content;
                
                // Remove any validation attributes that might cause issues
                messageInput.removeAttribute('required');
                
                // Show loading state
                var submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
                }
                
                return true;
            });
            
            // Clear error when user starts typing
            quill.on('text-change', function() {
                var textContent = quill.getText().trim();
                if (textContent && textContent !== '') {
                    messageError.classList.add('hidden');
                }
            });
        });
    </script>
    @endpush
@endsection

