@extends('layouts.admin')

@section('title', 'Email Template Details')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.email-templates.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-1"></i>Back to Templates
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $template->name }}</h2>
                <p class="text-sm text-gray-500 font-mono mt-1">{{ $template->key }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.email-templates.edit', $template->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                    <i class="fas fa-edit mr-2"></i>Edit Template
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <h3 class="text-lg font-semibold mb-3">Template Information</h3>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Subject</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $template->subject }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $template->updated_on ? $template->updated_on->format('d M Y H:i') : 'Never' }}</dd>
                    </div>
                </dl>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-3">Available Variables</h3>
                @if(count($variables) > 0)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <ul class="space-y-2">
                            @foreach($variables as $key => $description)
                                <li class="text-sm">
                                    <code class="text-xs font-mono bg-white px-2 py-1 rounded border border-gray-300">{{ '{{' . $key . '}}' }}</code>
                                    <span class="text-gray-600 ml-2">- {{ $description }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="text-sm text-gray-500">No variables available for this template</p>
                @endif
            </div>
        </div>

        <!-- Preview Section -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Preview</h3>
                <div class="flex gap-2">
                    <button onclick="loadPreview()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium text-sm">
                        <i class="fas fa-sync mr-2"></i>Refresh Preview
                    </button>
                    <button onclick="showTestEmailModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium text-sm">
                        <i class="fas fa-paper-plane mr-2"></i>Send Test Email
                    </button>
                </div>
            </div>
            
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-2 flex gap-2">
                    <button onclick="showPreviewTab('html')" id="preview-html-tab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-t border-b-2 border-blue-500">
                        HTML Preview
                    </button>
                    <button onclick="showPreviewTab('text')" id="preview-text-tab" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">
                        Plain Text
                    </button>
                    <button onclick="showPreviewTab('subject')" id="preview-subject-tab" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">
                        Subject
                    </button>
                </div>
                <div class="p-4 bg-white">
                    <div id="preview-html" class="preview-content">
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                            <p>Loading preview...</p>
                        </div>
                    </div>
                    <div id="preview-text" class="preview-content hidden">
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                            <p>Loading preview...</p>
                        </div>
                    </div>
                    <div id="preview-subject" class="preview-content hidden">
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                            <p>Loading preview...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Template Content -->
        <div>
            <h3 class="text-lg font-semibold mb-4">Template Content</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">HTML Body</label>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 max-h-96 overflow-auto">
                        <pre class="text-xs text-gray-700 whitespace-pre-wrap">{{ $template->body_html ?? '(empty)' }}</pre>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Plain Text Body</label>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 max-h-96 overflow-auto">
                        <pre class="text-xs text-gray-700 whitespace-pre-wrap">{{ $template->body_text ?? '(empty)' }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Email Modal -->
    <div id="test-email-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-semibold mb-4">Send Test Email</h3>
                <form method="POST" action="{{ route('admin.email-templates.send-test', $template->id) }}" id="test-email-form">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Test Email Address</label>
                        <input type="email" name="test_email" required class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="your@email.com">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                            Send Test
                        </button>
                        <button type="button" onclick="closeTestEmailModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showPreviewTab(tab) {
            document.querySelectorAll('.preview-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('[id^="preview-"]').forEach(el => {
                if (el.id.endsWith('-tab')) {
                    el.classList.remove('bg-white', 'border-b-2', 'border-blue-500', 'text-gray-700');
                    el.classList.add('text-gray-500');
                }
            });
            
            document.getElementById('preview-' + tab).classList.remove('hidden');
            document.getElementById('preview-' + tab + '-tab').classList.add('bg-white', 'border-b-2', 'border-blue-500', 'text-gray-700');
            document.getElementById('preview-' + tab + '-tab').classList.remove('text-gray-500');
        }

        function loadPreview() {
            fetch('{{ route('admin.email-templates.preview', $template->id) }}')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('preview-html').innerHTML = '<iframe srcdoc="' + escapeHtml(data.html) + '" class="w-full h-96 border border-gray-200 rounded"></iframe>';
                    document.getElementById('preview-text').innerHTML = '<pre class="whitespace-pre-wrap text-sm text-gray-700 font-mono">' + escapeHtml(data.text) + '</pre>';
                    document.getElementById('preview-subject').innerHTML = '<p class="text-sm text-gray-700">' + escapeHtml(data.subject) + '</p>';
                })
                .catch(error => {
                    console.error('Error loading preview:', error);
                    document.getElementById('preview-html').innerHTML = '<p class="text-red-600">Error loading preview</p>';
                });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function showTestEmailModal() {
            document.getElementById('test-email-modal').classList.remove('hidden');
        }

        function closeTestEmailModal() {
            document.getElementById('test-email-modal').classList.add('hidden');
        }

        // Load preview on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadPreview();
            showPreviewTab('html');
        });
    </script>
    @endpush
@endsection

