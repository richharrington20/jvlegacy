@extends('layouts.admin')

@section('title', 'System Status')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-sm text-gray-500">Manage system status messages displayed on the login page</p>
            </div>
            <a href="{{ route('admin.system-status.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                <i class="fas fa-plus mr-2"></i>Create Status
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Show on Login</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($statuses as $status)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $status->title }}</div>
                                <div class="text-xs text-gray-500 mt-1 max-w-md truncate">{!! strip_tags($status->message) !!}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    {{ $status->status_type === 'error' ? 'bg-red-100 text-red-800' : 
                                       ($status->status_type === 'warning' ? 'bg-yellow-100 text-yellow-800' : 
                                       ($status->status_type === 'success' ? 'bg-green-100 text-green-800' : 
                                       ($status->status_type === 'maintenance' ? 'bg-orange-100 text-orange-800' : 
                                       'bg-blue-100 text-blue-800'))) }}">
                                    {{ \App\Models\SystemStatus::TYPE_MAP[$status->status_type] ?? ucfirst($status->status_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($status->is_active)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($status->show_on_login)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Yes</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">No</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $status->created_on ? $status->created_on->format('d M Y H:i') : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.system-status.edit', $status->id) }}" class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded hover:bg-blue-200 transition-colors">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.system-status.toggle', $status->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-2 py-1 text-xs font-medium {{ $status->is_active ? 'text-yellow-700 bg-yellow-100 hover:bg-yellow-200' : 'text-green-700 bg-green-100 hover:bg-green-200' }} rounded transition-colors">
                                            <i class="fas fa-{{ $status->is_active ? 'pause' : 'play' }} mr-1"></i>{{ $status->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.system-status.destroy', $status->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this status?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded hover:bg-red-200 transition-colors">
                                            <i class="fas fa-trash mr-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <i class="fas fa-info-circle text-4xl mb-3"></i>
                                    <p class="text-sm">No system status messages found.</p>
                                    <a href="{{ route('admin.system-status.create') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-800">Create your first status message</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $statuses->links() }}
        </div>
    </div>
@endsection

