@extends('layouts.public')

@section('title', 'Current Opportunities')

@section('content')
    <section class="bg-slate-900 text-white py-14">
        <div class="max-w-5xl mx-auto px-6 text-center">
            <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Opportunities</p>
            <h1 class="text-4xl font-semibold mt-4">Invest in live JaeVee projects</h1>
            <p class="text-slate-300 mt-4 text-sm">
                Every listing below is an SPV we’re co-investing in. Filter by stage, review the timeline,
                and click through for full details.
            </p>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-6 space-y-6">
            @foreach($projects as $project)
                <a href="{{ route('public.projects.show', $project->project_id) }}" class="block border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition">
                    <div class="flex flex-wrap justify-between gap-4">
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-wide">SPV{{ $project->project_id }}</p>
                            <h2 class="text-2xl font-semibold text-slate-900 mt-1">{{ $project->name }}</h2>
                            <p class="text-sm text-slate-500 mt-1">{{ $project->property->investment_strategy ?? 'Property development' }}</p>
                        </div>
                        <div class="text-right text-sm text-slate-500">
                            <p>Status: <span class="text-slate-900 font-medium">{{ \App\Models\Project::STATUS_MAP[$project->status] ?? 'In progress' }}</span></p>
                            <p>Term: {{ optional($project->property)->investment_turnaround_time ?? '—' }} months</p>
                        </div>
                    </div>
                    <p class="text-slate-600 text-sm mt-4 line-clamp-2">{{ strip_tags(Str::limit($project->description ?? '', 220)) }}</p>
                </a>
            @endforeach
        </div>
        <div class="mt-10 max-w-6xl mx-auto px-6">
            {{ $projects->links() }}
        </div>
    </section>
@endsection


