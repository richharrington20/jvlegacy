@extends('layouts.admin')

@section('title', 'Create Investment')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.investments.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-900 font-medium">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Investments
        </a>
    </div>

    <div class="mb-4">
        <h2 class="text-2xl font-bold text-gray-900">Create New Investment</h2>
        <p class="text-sm text-gray-600 mt-1">Add a new investment record for an investor</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.investments.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Project <span class="text-red-500">*</span></label>
                    <select name="project_id" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        <option value="">Select Project</option>
                        @foreach ($projects as $project)
                            @if($project->project_id)
                                <option value="{{ $project->project_id }}">
                                    {{ $project->project_id }} – {{ $project->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('project_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Account (Investor) <span class="text-red-500">*</span></label>
                    <select name="account_id" id="account-select" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        <option value="">Start typing to search (min. 2 characters)...</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Type at least 2 characters to search by name, email, or account ID
                    </p>
                    <input type="hidden" name="selected_account_id" id="selected-account-id">
                    @error('account_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Amount (£) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    @error('amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Type</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        <option value="1" selected>Debt</option>
                        <option value="2">Mezzanine</option>
                    </select>
                    @error('type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Transfer ID</label>
                    <input type="number" name="transfer_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Pay In ID</label>
                    <input type="number" name="pay_in_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="paid" value="1" class="mr-2">
                        <span class="text-sm font-medium">Mark as Paid</span>
                    </label>
                </div>
            </div>

            <!-- Account Documents Section (shown after account is selected) -->
            <div id="account-documents-section" class="hidden mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Documents</h3>
                <p class="text-sm text-gray-600 mb-4">Upload documents for this investor account</p>
                
                <div id="document-upload-form" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Document Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="doc-name" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Category</label>
                            <select name="category" id="doc-category" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                <option value="general">General</option>
                                <option value="kyc">KYC</option>
                                <option value="contract">Contract</option>
                                <option value="statement">Statement</option>
                                <option value="certificate">Certificate</option>
                                <option value="agreement">Agreement</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">File</label>
                        <input type="file" name="file" id="doc-file" class="w-full px-3 py-2 border border-gray-300 rounded-md" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <p class="text-xs text-gray-500 mt-1">Max file size: 10MB (optional - only upload if needed)</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea name="description" id="doc-description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_private" value="1" checked class="mr-2">
                            <span class="text-sm font-medium">Private (only visible to this account)</span>
                        </label>
                    </div>
                    <button type="button" id="upload-document-btn" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        <i class="fas fa-upload mr-2"></i>Upload Document
                    </button>
                </div>
                
                <div id="documents-list" class="mt-6 space-y-2">
                    <!-- Documents will be loaded here via AJAX -->
                </div>
            </div>

            <div class="flex gap-4 mt-6">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Create Investment
                </button>
                <a href="{{ route('admin.investments.index') }}" class="px-6 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
        
        <script>
        // Ensure empty number inputs don't send values
        document.querySelector('form[action="{{ route('admin.investments.store') }}"]').addEventListener('submit', function(e) {
            const transferId = this.querySelector('input[name="transfer_id"]');
            const payInId = this.querySelector('input[name="pay_in_id"]');
            
            // Clear value if empty or 0
            if (transferId && (!transferId.value || transferId.value === '0' || transferId.value === 0)) {
                transferId.value = '';
            }
            if (payInId && (!payInId.value || payInId.value === '0' || payInId.value === 0)) {
                payInId.value = '';
            }
        });
        </script>
    </div>

    @if ($errors->any())
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection

@push('scripts')
<!-- jQuery (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for account search
    $('#account-select').select2({
        placeholder: 'Start typing to search (min. 2 characters)...',
        allowClear: true,
        ajax: {
            url: '{{ route("admin.investments.search-accounts") }}',
            type: 'GET',
            dataType: 'json',
            delay: 300,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            data: function (params) {
                return {
                    q: params.term || '', // search term
                    page: params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results || [],
                    pagination: {
                        more: false // Disable pagination for now
                    }
                };
            },
            cache: false,
            error: function(xhr, status, error) {
                console.error('Search error:', error, xhr.responseText);
            }
        },
        minimumInputLength: 2,
        language: {
            inputTooShort: function() {
                return 'Please enter at least 2 characters to search';
            },
            searching: function() {
                return 'Searching...';
            },
            noResults: function() {
                return 'No accounts found';
            },
            loadingMore: function() {
                return 'Loading more results...';
            }
        },
        templateResult: function(account) {
            if (account.loading) {
                return '<div class="text-gray-500 py-2">Searching...</div>';
            }
            if (!account.id) {
                return account.text;
            }
            return $('<div class="py-2">').html(
                '<div class="font-medium text-gray-900">' + (account.text || account.name || 'Account #' + account.id) + '</div>' +
                '<div class="text-xs text-gray-500 mt-1">' + (account.email || '') + '</div>'
            );
        },
        templateSelection: function(account) {
            return account.text || account.name || account.id || 'Select an account...';
        }
    });

    // Show documents section when account is selected
    $('#account-select').on('change', function() {
        const accountId = $(this).val();
        if (accountId) {
            $('#selected-account-id').val(accountId);
            $('#account-documents-section').removeClass('hidden');
            loadAccountDocuments(accountId);
        } else {
            $('#account-documents-section').addClass('hidden');
            $('#selected-account-id').val('');
        }
    });

    // Handle document upload
    $('#upload-document-btn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const accountId = $('#account-select').val();
        if (!accountId) {
            alert('Please select an account first');
            return;
        }

        // Check if file is selected
        const fileInput = $('#doc-file')[0];
        if (!fileInput.files || !fileInput.files[0]) {
            alert('Please select a file to upload, or leave the document section empty if you don\'t need to upload a document.');
            return;
        }

        const formData = new FormData();
        formData.append('name', $('#doc-name').val());
        formData.append('category', $('#doc-category').val());
        formData.append('file', fileInput.files[0]);
        formData.append('description', $('#doc-description').val());
        formData.append('is_private', $('#document-upload-form input[name="is_private"]').is(':checked') ? 1 : 0);
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: `/admin/accounts/${accountId}/documents`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    alert('Document uploaded successfully!');
                    $('#doc-name').val('');
                    $('#doc-category').val('general');
                    $('#doc-file').val('');
                    $('#doc-description').val('');
                    $('#document-upload-form input[name="is_private"]').prop('checked', true);
                    loadAccountDocuments(accountId);
                } else {
                    alert('Error: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                let errorMsg = 'Error uploading document';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                } else if (xhr.responseText) {
                    try {
                        const error = JSON.parse(xhr.responseText);
                        errorMsg = error.message || errorMsg;
                    } catch(e) {
                        errorMsg = xhr.responseText.substring(0, 200);
                    }
                }
                alert(errorMsg);
            }
        });
    });

    function loadAccountDocuments(accountId) {
        $.ajax({
            url: `/admin/accounts/${accountId}/documents`,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(documents) {
                const container = $('#documents-list');
                container.empty();
                
                if (!documents || documents.length === 0) {
                    container.html('<p class="text-sm text-gray-500">No documents uploaded yet.</p>');
                    return;
                }

                documents.forEach(function(doc) {
                    const fileIcon = doc.file_type === 'pdf' ? 'fa-file-pdf' : 
                                    (doc.file_type && ['jpg', 'jpeg', 'png', 'gif'].includes(doc.file_type.toLowerCase())) ? 'fa-file-image' : 
                                    'fa-file-alt';
                    const docHtml = `
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                            <div class="flex items-center space-x-3">
                                <i class="fas ${fileIcon} text-blue-500"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">${doc.name || 'Untitled'}</p>
                                    <p class="text-xs text-gray-500">${doc.category || 'General'} • ${formatFileSize(doc.file_size || 0)}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <a href="${doc.url}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-download mr-1"></i>View
                                </a>
                                <button onclick="deleteDocument(${doc.id}, ${accountId})" class="text-red-600 hover:text-red-800 text-sm">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </button>
                            </div>
                        </div>
                    `;
                    container.append(docHtml);
                });
            },
            error: function(xhr) {
                $('#documents-list').html('<p class="text-sm text-red-500">Error loading documents. ' + (xhr.responseJSON?.error || '') + '</p>');
            }
        });
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    window.deleteDocument = function(docId, accountId) {
        if (!confirm('Are you sure you want to delete this document?')) {
            return;
        }

        $.ajax({
            url: `/admin/accounts/${accountId}/documents/${docId}`,
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    alert('Document deleted successfully!');
                    loadAccountDocuments(accountId);
                } else {
                    alert('Error: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                let errorMsg = 'Error deleting document';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert(errorMsg);
            }
        });
    };
});
</script>
@endpush

