@extends('layouts.admin')

@section('title', 'Email Log Details')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.email-logs.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-1"></i>Back to Email Log
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

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold">Email Log #{{ $emailLog->id }}</h2>
            <div class="flex gap-2">
                @if($emailLog->postmark_message_id)
                    <form method="POST" action="{{ route('admin.email-logs.update-status', $emailLog->id) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                            <i class="fas fa-sync mr-2"></i>Refresh Status
                        </button>
                    </form>
                @endif
                @if($emailLog->canResend())
                    <form method="POST" action="{{ route('admin.email-logs.resend', $emailLog->id) }}" class="inline" onsubmit="return confirm('Resend this email?');">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                            <i class="fas fa-redo mr-2"></i>Resend
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Basic Information -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Basic Information</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucwords(str_replace('_', ' ', $emailLog->email_type)) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Recipient</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $emailLog->recipient_email }}
                            @if($emailLog->recipient_name)
                                <span class="text-gray-500">({{ $emailLog->recipient_name }})</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Subject</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $emailLog->subject }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'sent' => 'bg-blue-100 text-blue-800',
                                    'delivered' => 'bg-green-100 text-green-800',
                                    'bounced' => 'bg-red-100 text-red-800',
                                    'spam_complaint' => 'bg-orange-100 text-orange-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                ];
                                $color = $statusColors[$emailLog->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-3 py-1 text-sm font-medium rounded-full {{ $color }}">
                                {{ ucfirst(str_replace('_', ' ', $emailLog->status)) }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Timestamps -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Timestamps</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Sent At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $emailLog->sent_at ? $emailLog->sent_at->format('d M Y H:i:s') : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Delivered At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $emailLog->delivered_at ? $emailLog->delivered_at->format('d M Y H:i:s') : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Opened At</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $emailLog->opened_at ? $emailLog->opened_at->format('d M Y H:i:s') : '—' }}
                            @if($emailLog->open_count > 0)
                                <span class="text-gray-500">({{ $emailLog->open_count }} time{{ $emailLog->open_count !== 1 ? 's' : '' }})</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Clicked At</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $emailLog->clicked_at ? $emailLog->clicked_at->format('d M Y H:i:s') : '—' }}
                            @if($emailLog->click_count > 0)
                                <span class="text-gray-500">({{ $emailLog->click_count }} time{{ $emailLog->click_count !== 1 ? 's' : '' }})</span>
                            @endif
                        </dd>
                    </div>
                    @if($emailLog->bounced_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Bounced At</dt>
                            <dd class="mt-1 text-sm text-red-600">{{ $emailLog->bounced_at->format('d M Y H:i:s') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Postmark Information -->
        @if($emailLog->postmark_message_id)
            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h3 class="text-lg font-semibold mb-3">Postmark Information</h3>
                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Message ID</dt>
                        <dd class="text-sm text-gray-900 font-mono">{{ $emailLog->postmark_message_id }}</dd>
                    </div>
                    @if($postmarkStatus)
                        <div class="mt-4 pt-4 border-t border-gray-300">
                            <h4 class="text-sm font-semibold mb-2">Latest Status from Postmark</h4>
                            <pre class="text-xs bg-white p-3 rounded border border-gray-200 overflow-auto max-h-64">{{ json_encode($postmarkStatus, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @endif
                </dl>
            </div>
        @endif

        <!-- Related Entities -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-4">Related Entities</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @if($emailLog->project)
                    <div class="p-3 bg-gray-50 rounded border border-gray-200">
                        <dt class="text-xs font-medium text-gray-500">Project</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <a href="{{ route('admin.projects.show', $emailLog->project->project_id) }}" class="text-blue-600 hover:underline">
                                {{ $emailLog->project->name }}
                            </a>
                        </dd>
                    </div>
                @endif
                @if($emailLog->update)
                    <div class="p-3 bg-gray-50 rounded border border-gray-200">
                        <dt class="text-xs font-medium text-gray-500">Update</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <a href="{{ route('admin.updates.show', $emailLog->update->id) }}" class="text-blue-600 hover:underline">
                                Update #{{ $emailLog->update->id }}
                            </a>
                        </dd>
                    </div>
                @endif
                @if($emailLog->recipientAccount)
                    <div class="p-3 bg-gray-50 rounded border border-gray-200">
                        <dt class="text-xs font-medium text-gray-500">Recipient Account</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <a href="{{ route('admin.accounts.show', $emailLog->recipientAccount->id) }}" class="text-blue-600 hover:underline">
                                {{ $emailLog->recipientAccount->email }}
                            </a>
                        </dd>
                    </div>
                @endif
            </div>
        </div>

        <!-- Error Message -->
        @if($emailLog->error_message)
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <h3 class="text-lg font-semibold text-red-800 mb-2">Error Message</h3>
                <p class="text-sm text-red-700">{{ $emailLog->error_message }}</p>
            </div>
        @endif

        <!-- Email Content -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-4">Email Content</h3>
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-2 flex gap-2">
                    <button onclick="showTab('html')" id="html-tab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-t border-b-2 border-blue-500">
                        HTML
                    </button>
                    <button onclick="showTab('text')" id="text-tab" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">
                        Plain Text
                    </button>
                </div>
                <div class="p-4 bg-white">
                    <div id="html-content" class="email-content">
                        @if($emailLog->html_content)
                            <iframe srcdoc="{{ htmlspecialchars($emailLog->html_content) }}" class="w-full h-96 border border-gray-200 rounded"></iframe>
                        @else
                            <p class="text-gray-500">No HTML content available</p>
                        @endif
                    </div>
                    <div id="text-content" class="email-content hidden">
                        @if($emailLog->text_content)
                            <pre class="whitespace-pre-wrap text-sm text-gray-700 font-mono">{{ $emailLog->text_content }}</pre>
                        @else
                            <p class="text-gray-500">No plain text content available</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showTab(tab) {
            document.querySelectorAll('.email-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('[id$="-tab"]').forEach(el => {
                el.classList.remove('bg-white', 'border-b-2', 'border-blue-500', 'text-gray-700');
                el.classList.add('text-gray-500');
            });
            
            document.getElementById(tab + '-content').classList.remove('hidden');
            document.getElementById(tab + '-tab').classList.add('bg-white', 'border-b-2', 'border-blue-500', 'text-gray-700');
            document.getElementById(tab + '-tab').classList.remove('text-gray-500');
        }
    </script>
    @endpush
@endsection

