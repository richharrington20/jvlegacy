@extends('layouts.public')

@section('title', $project->name . ' · JaeVee')

@section('content')
    <section class="bg-slate-900 text-white py-16">
        <div class="max-w-5xl mx-auto px-6">
            <a href="{{ route('public.projects.index') }}" class="text-slate-400 text-sm hover:text-white">← Back to opportunities</a>
            <p class="text-xs mt-4 uppercase tracking-[0.4em] text-slate-400">SPV{{ $project->project_id }}</p>
            <h1 class="text-4xl font-semibold mt-3">{{ $project->name }}</h1>
            <p class="text-slate-300 mt-3">{{ $project->property->investment_strategy ?? 'Property development' }}</p>
            <div class="mt-6 flex flex-wrap gap-4 text-sm text-slate-200">
                <span class="px-4 py-2 rounded-full bg-white/10 border border-white/10">Term: {{ optional($project->property)->investment_turnaround_time ?? '—' }} months</span>
                <span class="px-4 py-2 rounded-full bg-white/10 border border-white/10">Status: {{ \App\Models\Project::STATUS_MAP[$project->status] ?? 'In progress' }}</span>
                @if($project->expected_payout_date)
                    <span class="px-4 py-2 rounded-full bg-white/10 border border-white/10">Forecast payout: {{ $project->expected_payout_date->format('M Y') }}</span>
                @endif
            </div>
            <div class="mt-10">
                <a href="{{ route('investor.login') }}" class="inline-flex items-center px-6 py-3 bg-white text-slate-900 font-semibold rounded-lg">
                    Join the raise
                </a>
                <span class="text-slate-300 text-sm ml-4">Already in? Check your dashboard for documents & updates.</span>
            </div>
        </div>
    </section>

    <section class="py-14 bg-white">
        <div class="max-w-5xl mx-auto px-6 grid gap-12 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-10">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Investment overview</h2>
                    <div class="mt-4 prose prose-slate max-w-none">
                        {!! $project->description ?? '<p>Detailed overview available after logging in.</p>' !!}
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-3">Recent project updates</h3>
                    <div class="space-y-4">
                        @forelse($project->updates as $update)
                            <article class="border border-slate-100 rounded-xl p-4">
                                <p class="text-xs text-slate-500 uppercase">{{ optional($update->sent_on)->format('d M Y') }}</p>
                                <div class="prose prose-sm prose-slate mt-2 line-clamp-3">{!! $update->comment !!}</div>
                                <p class="text-xs text-slate-400 mt-3">Full update visible once you log in.</p>
                            </article>
                        @empty
                            <p class="text-sm text-slate-500">Updates will appear here once the project is underway.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <aside class="space-y-6">
                <div class="border border-slate-100 rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-slate-900">Key facts</h3>
                    <dl class="mt-4 space-y-3 text-sm text-slate-600">
                        <div class="flex justify-between">
                            <dt>Units</dt>
                            <dd>{{ $project->property->planned_total_no_of_units ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Exit strategy</dt>
                            <dd>{{ $project->property->exit_strategy ?? 'To be confirmed' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Location</dt>
                            <dd>{{ $project->property->local_authority ?? 'UK' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Timeline status</dt>
                            <dd>{{ \App\Models\Project::STATUS_MAP[$project->status] ?? 'In progress' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="border border-slate-100 rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-slate-900">Documents</h3>
                    <p class="text-sm text-slate-600 mt-2">Signed investors see the full pack inside the dashboard.</p>
                    <ul class="mt-4 space-y-2 text-sm text-slate-600">
                        @forelse($project->investorDocuments as $doc)
                            <li>{{ $doc->name }}</li>
                        @empty
                            <li>Shareholders agreement</li>
                            <li>Loan agreement</li>
                            <li>Development feasibility</li>
                        @endforelse
                    </ul>
                </div>
            </aside>
        </div>
    </section>
@endsection


