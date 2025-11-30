@extends('layouts.public')

@section('title', 'Current Opportunities')

@section('content')
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-blue-600 via-indigo-700 to-purple-800 text-white overflow-hidden">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 0%, transparent 50%);"></div>
        
        <div class="relative max-w-7xl mx-auto px-6 py-20 text-center">
            <p class="text-sm uppercase tracking-[0.3em] text-blue-200 font-semibold mb-4">Opportunities</p>
            <h1 class="text-5xl lg:text-6xl font-bold mb-6">Invest in live JaeVee projects</h1>
            <p class="text-xl text-blue-100 max-w-3xl mx-auto leading-relaxed">
                Every listing below is an SPV we're co-investing in. Filter by stage, review the timeline,
                and click through for full details.
            </p>
        </div>
    </section>

    <!-- Projects Grid -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            @if($projects->count() > 0)
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($projects as $project)
                        <a href="{{ route('public.projects.show', $project->project_id) }}" class="group block">
                            <div class="bg-white border-2 border-gray-100 rounded-2xl p-8 shadow-sm hover:shadow-xl transition-all hover:border-blue-200 h-full flex flex-col">
                                <!-- Project Header -->
                                <div class="mb-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-2">
                                                SPV{{ $project->project_id }}
                                            </p>
                                            <h3 class="text-2xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                                                {{ $project->name }}
                                            </h3>
                                            <p class="text-sm text-gray-600">
                                                {{ $project->property->investment_strategy ?? 'Property development' }}
                                            </p>
                                        </div>
                                        @if($project->image_path)
                                            <img src="{{ asset('storage/' . $project->image_path) }}" alt="{{ $project->name }}" class="w-20 h-20 rounded-lg object-cover ml-4 flex-shrink-0">
                                        @else
                                            <div class="w-20 h-20 rounded-lg bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center ml-4 flex-shrink-0">
                                                <i class="fas fa-building text-blue-600 text-2xl"></i>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Status Badges -->
                                <div class="flex flex-wrap gap-2 mb-6">
                                    <span class="px-4 py-1.5 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        {{ \App\Models\Project::STATUS_MAP[$project->status] ?? 'In progress' }}
                                    </span>
                                    <span class="px-4 py-1.5 rounded-full bg-gray-100 text-gray-700 text-xs font-semibold">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        Term: {{ optional($project->property)->investment_turnaround_time ?? '—' }} months
                                    </span>
                                </div>

                                <!-- Description -->
                                <p class="text-gray-600 text-sm leading-relaxed mb-6 line-clamp-3 flex-grow">
                                    {{ strip_tags(Str::limit($project->description ?? 'Full project brief available in dashboard.', 180)) }}
                                </p>

                                <!-- Footer -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100 mt-auto">
                                    <span class="text-blue-600 font-bold text-sm flex items-center gap-2 group-hover:gap-3 transition-all">
                                        View project <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                                    </span>
                                    @if($project->launched_on)
                                        <span class="text-gray-400 text-xs">
                                            <i class="far fa-clock mr-1"></i>{{ $project->launched_on->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($projects->hasPages())
                    <div class="mt-12 flex justify-center">
                        <div class="bg-white border border-gray-200 rounded-xl p-2 inline-flex gap-2 shadow-sm">
                            {{ $projects->links() }}
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-20">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                        <i class="fas fa-folder-open text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">No projects available</h3>
                    <p class="text-gray-600 max-w-md mx-auto">
                        There are currently no live opportunities. Check back soon or sign in to your investor account for updates.
                    </p>
                    <div class="mt-8">
                        <a href="{{ route('investor.login') }}" class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>Investor Login
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-br from-gray-50 to-blue-50">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Ready to invest?</h2>
            <p class="text-gray-600 text-lg mb-8">
                Sign in to your investor account to access full project details, documents, and investment tools.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('investor.login') }}" class="px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="fas fa-sign-in-alt mr-2"></i>Investor Login
                </a>
                <a href="{{ route('home') }}" class="px-8 py-4 border-2 border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-white hover:border-gray-400 transition-all">
                    <i class="fas fa-home mr-2"></i>Back to Home
                </a>
            </div>
        </div>
    </section>
@endsection
