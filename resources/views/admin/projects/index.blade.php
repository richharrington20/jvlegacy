@extends('layouts.admin')

@section('title', 'Projects')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Projects</h2>
                <p class="text-sm text-gray-500 mt-1.5">Manage all investment projects</p>
            </div>
            <a href="{{ route('admin.projects.create') }}" class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all font-medium shadow-sm hover:shadow-md">
                <i class="fas fa-plus mr-2"></i>
                Create Project
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="w-full md:w-64">
                    <label class="block text-sm font-medium mb-1 text-gray-700">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Project name or ID..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="w-full md:w-48">
                    <label class="block text-sm font-medium mb-1 text-gray-700">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Statuses</option>
                        @foreach(\App\Models\Project::STATUS_MAP as $key => $label)
                            <option value="{{ $key }}" @selected(request('status') == $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-auto flex gap-2">
                    <button type="submit" class="h-10 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors">Filter</button>
                    <a href="{{ route('admin.projects.index') }}" class="h-10 px-4 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center justify-center font-medium transition-colors">Clear</a>
                </div>
            </form>
        </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <p class="text-sm text-gray-600">
                Showing {{ $projects->firstItem() ?? 0 }} to {{ $projects->lastItem() ?? 0 }} of {{ $projects->total() }} results
            </p>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Project ID</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Progress</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($projects as $project)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            @if($project->project_id)
                                <a href="{{ route('admin.projects.show', $project->project_id) }}" class="text-blue-600 hover:text-blue-900 hover:underline">
                                    {{ $project->project_id }}
                                </a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($project->project_id)
                                <a href="{{ route('admin.projects.show', $project->project_id) }}" class="text-blue-600 hover:text-blue-900 hover:underline font-medium">
                                    {{ $project->name }}
                                </a>
                            @else
                                <span class="text-gray-900">{{ $project->name }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $project->status == \App\Models\Project::STATUS_SOLD || $project->status == \App\Models\Project::STATUS_LET ? 'bg-green-100 text-green-700' : ($project->status >= \App\Models\Project::STATUS_PENDING_EQUITY ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }}">
                                {{ \App\Models\Project::STATUS_MAP[$project->status] ?? 'Unknown' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-24 bg-gray-200 rounded-full h-2.5 mr-3 overflow-hidden">
                                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: {{ $project->progress ?? 0 }}%"></div>
                                </div>
                                <span class="text-sm font-semibold text-gray-700">{{ $project->progress ?? 0 }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ human_date($project->created_on) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($project->project_id)
                                <a href="{{ route('admin.projects.show', $project->project_id) }}" class="text-blue-600 hover:text-blue-900 hover:underline font-medium">
                                    <i class="fas fa-eye mr-1"></i>
                                    View
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="text-gray-400">
                                <i class="fas fa-folder-open text-4xl mb-3"></i>
                                <p class="text-sm">No projects found.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-600">
            {{ $projects->links() }}
        </div>
    </div>
@endsection

