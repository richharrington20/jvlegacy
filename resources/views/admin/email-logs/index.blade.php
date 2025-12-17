@extends('layouts.admin')

@section('title', 'Email Log')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Global Email Log</h1>
                <p class="text-sm text-gray-500 mt-1">View all emails sent through the system with delivery status</p>
            </div>
            <form method="POST" action="{{ route('admin.email-logs.bulk-update-status') }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                    <i class="fas fa-sync mr-2"></i>Refresh Status from Postmark
                </button>
            </form>
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

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" action="{{ route('admin.email-logs.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="bounced" {{ request('status') === 'bounced' ? 'selected' : '' }}>Bounced</option>
                        <option value="spam_complaint" {{ request('status') === 'spam_complaint' ? 'selected' : '' }}>Spam Complaint</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Type</label>
                    <select name="email_type" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                        <option value="">All Types</option>
                        @foreach($emailTypes as $type)
                            <option value="{{ $type }}" {{ request('email_type') === $type ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Recipient Email</label>
                    <input type="text" name="recipient_email" value="{{ request('recipient_email') }}" placeholder="Search email..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                </div>
                <div class="md:col-span-5 flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm">
                        <i class="fas fa-filter mr-2"></i>Apply Filters
                    </button>
                    <a href="{{ route('admin.email-logs.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium text-sm">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Email Log Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Recipient</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Sent At</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($emailLogs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $log->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                    {{ ucwords(str_replace('_', ' ', $log->email_type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div>{{ $log->recipient_email }}</div>
                                @if($log->recipient_name)
                                    <div class="text-xs text-gray-500">{{ $log->recipient_name }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div class="max-w-md truncate">{{ $log->subject }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'sent' => 'bg-blue-100 text-blue-800',
                                        'delivered' => 'bg-green-100 text-green-800',
                                        'bounced' => 'bg-red-100 text-red-800',
                                        'spam_complaint' => 'bg-orange-100 text-orange-800',
                                        'failed' => 'bg-red-100 text-red-800',
                                    ];
                                    $color = $statusColors[$log->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $color }}">
                                    {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $log->sent_at ? $log->sent_at->format('d M Y H:i') : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.email-logs.show', $log->id) }}" class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded hover:bg-gray-200 transition-colors">
                                        <i class="fas fa-eye mr-1"></i>
                                        View
                                    </a>
                                    @if($log->canResend())
                                        <form method="POST" action="{{ route('admin.email-logs.resend', $log->id) }}" class="inline" onsubmit="return confirm('Resend this email?');">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded hover:bg-green-200 transition-colors">
                                                <i class="fas fa-redo mr-1"></i>
                                                Resend
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                                <p class="text-lg font-medium">No emails found</p>
                                <p class="text-sm mt-1">Try adjusting your filters</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $emailLogs->links() }}
        </div>
    </div>
@endsection

