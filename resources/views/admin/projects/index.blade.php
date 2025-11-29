@extends('layouts.admin')

@section('title', 'Projects')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Projects</h2>
                <p class="text-sm text-gray-600 mt-1">Manage all investment projects</p>
            </div>
            <a href="{{ route('admin.projects.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                <i class="fas fa-plus mr-2"></i>
                Create Project
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="GET" class="mb-4 flex flex-wrap gap-4 items-end">
            <div class="w-full md:w-64">
                <label class="block text-sm font-medium mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Project name or ID..." class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div class="w-full md:w-48">
                <label class="block text-sm font-medium mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">All Statuses</option>
                    @foreach(\App\Models\Project::STATUS_MAP as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') == $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-auto flex gap-2">
                <button type="submit" class="h-10 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700">Filter</button>
                <a href="{{ route('admin.projects.index') }}" class="h-10 px-4 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 flex items-center justify-center">Clear</a>
            </div>
        </form>
    </div>

    <div class="mt-4">
        {{ $projects->links() }}
    </div>

        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Project ID</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Progress</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
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
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $project->status == \App\Models\Project::STATUS_SOLD || $project->status == \App\Models\Project::STATUS_LET ? 'bg-green-100 text-green-800' : ($project->status >= \App\Models\Project::STATUS_PENDING_EQUITY ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ \App\Models\Project::STATUS_MAP[$project->status] ?? 'Unknown' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-20 bg-gray-200 rounded-full h-2 mr-3">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all" style="width: {{ $project->progress ?? 0 }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700">{{ $project->progress ?? 0 }}%</span>
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

    <div class="mt-4">
        {{ $projects->links() }}
    </div>
@endsection

