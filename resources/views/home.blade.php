@extends('layouts.public')

@section('title', 'Invest alongside JaeVee')

@section('content')
    <section class="bg-slate-900 text-white">
        <div class="max-w-6xl mx-auto px-6 py-20 grid gap-10 lg:grid-cols-2">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Institutional-grade co-investing</p>
                <h1 class="text-4xl lg:text-5xl font-semibold mt-4 leading-tight">
                    Build a diversified property portfolio without lifting a brick.
                </h1>
                <p class="text-lg text-slate-300 mt-6">
                    Back UK developments alongside JaeVee and our developer partners. Stay updated in real time,
                    download agreements instantly, and know exactly when payouts land.
                </p>
                <div class="mt-10 flex flex-wrap gap-4">
                    <a href="#opportunities" class="px-6 py-3 bg-white text-slate-900 font-semibold rounded-lg hover:bg-slate-100">
                        View current opportunities
                    </a>
                    <a href="{{ route('investor.login') }}" class="px-6 py-3 border border-slate-400 text-white rounded-lg hover:bg-white/5">
                        Already invested? Sign in
                    </a>
                </div>
            </div>
            <div class="bg-slate-800/60 border border-white/10 rounded-2xl p-8">
                <p class="text-sm text-slate-400 uppercase tracking-wide">Why investors stay</p>
                <ul class="mt-6 space-y-5 text-sm text-slate-300">
                    <li class="flex gap-3">
                        <span class="text-lime-400 mt-1">●</span>
                        Full transparency on every project milestone, payout, and document.
                    </li>
                    <li class="flex gap-3">
                        <span class="text-lime-400 mt-1">●</span>
                        Access both equity and mezzanine debt with curated SPVs.
                    </li>
                    <li class="flex gap-3">
                        <span class="text-lime-400 mt-1">●</span>
                        Direct support team—no ticket backlog, no bots.
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <section class="py-16 bg-white" id="opportunities">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
                <div>
                    <p class="text-sm text-slate-500 uppercase tracking-wide">Live opportunities</p>
                    <h2 class="text-3xl font-semibold text-slate-900 mt-1">Projects accepting capital</h2>
                </div>
                <a href="{{ route('public.projects.index') }}" class="text-blue-700 hover:underline text-sm font-medium">
                    View all projects →
                </a>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                @forelse($highlightedProjects as $project)
                    <div class="border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition">
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            SPV{{ $project->project_id }}
                        </p>
                        <h3 class="text-xl font-semibold mt-1 text-slate-900">
                            {{ $project->name }}
                        </h3>
                        <p class="text-sm text-slate-500 mt-1">
                            {{ $project->property->investment_strategy ?? 'Property development' }}
                        </p>
                        <div class="mt-6 flex flex-wrap gap-3 text-sm">
                            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700">
                                Term: {{ optional($project->property)->investment_turnaround_time ?? '—' }} months
                            </span>
                            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700">
                                Status: {{ \App\Models\Project::STATUS_MAP[$project->status] ?? 'In progress' }}
                            </span>
                        </div>
                        <p class="text-slate-600 text-sm mt-6 line-clamp-3">
                            {{ strip_tags(Str::limit($project->description ?? 'Full project brief available in dashboard.', 180)) }}
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('public.projects.show', $project->project_id) }}" class="text-blue-700 font-semibold hover:underline text-sm">
                                View project →
                            </a>
                            <span class="text-slate-400 text-sm">Updated {{ optional($project->launched_on)->diffForHumans() ?? 'recently' }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 text-sm">No live opportunities today. Join the platform to be notified when the next one launches.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="py-16 bg-slate-50">
        <div class="max-w-5xl mx-auto px-6 grid gap-12 lg:grid-cols-3">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">Why JaeVee</p>
                <h2 class="text-3xl font-semibold text-slate-900 mt-1">Designed for serious investors</h2>
                <p class="text-slate-600 mt-4 text-sm">
                    We only partner with developers that pass rigorous due diligence.
                    Investors see the same data rooms, legal docs, and update cadence
                    we rely on internally.
                </p>
            </div>
            <div class="lg:col-span-2 grid gap-6 sm:grid-cols-2">
                <div class="bg-white border border-slate-100 rounded-2xl p-6">
                    <p class="text-xs uppercase text-slate-400 tracking-wide">Always transparent</p>
                    <h3 class="text-lg font-semibold text-slate-900 mt-2">Live dashboards, not PDF reports</h3>
                    <p class="text-sm text-slate-600 mt-2">
                        Track construction progress, funding phases, and payouts in real time.
                        Every document—shareholders’ agreement, loan agreement, certificates—is one click away.
                    </p>
                </div>
                <div class="bg-white border border-slate-100 rounded-2xl p-6">
                    <p class="text-xs uppercase text-slate-400 tracking-wide">Aligned incentives</p>
                    <h3 class="text-lg font-semibold text-slate-900 mt-2">Co-invest alongside us</h3>
                    <p class="text-sm text-slate-600 mt-2">
                        We participate in every SPV, so our returns depend on the same milestones as yours.
                        No “listings” marketplace—just curated developments with skin in the game.
                    </p>
                </div>
                <div class="bg-white border border-slate-100 rounded-2xl p-6">
                    <p class="text-xs uppercase text-slate-400 tracking-wide">Direct access</p>
                    <h3 class="text-lg font-semibold text-slate-900 mt-2">Talk to the actual team</h3>
                    <p class="text-sm text-slate-600 mt-2">
                        Need clarity on a payout, document, or timeline? Raise a support request from any project card
                        and the humans running the deal reply—no outsourced help desk.
                    </p>
                </div>
                <div class="bg-white border border-slate-100 rounded-2xl p-6">
                    <p class="text-xs uppercase text-slate-400 tracking-wide">Regulated flow</p>
                    <h3 class="text-lg font-semibold text-slate-900 mt-2">Bank-grade tooling</h3>
                    <p class="text-sm text-slate-600 mt-2">
                        Digital KYC/AML, segregated client accounts, and institutional reporting
                        baked in from day one—because trust isn’t a feature, it’s the baseline.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="max-w-4xl mx-auto px-6">
            <p class="text-sm text-slate-500 uppercase tracking-wide text-center">Questions investors ask first</p>
            <h2 class="text-3xl font-semibold text-center text-slate-900 mt-1">FAQ</h2>
            <div class="mt-10 space-y-6">
                <details class="border border-slate-100 rounded-xl p-4">
                    <summary class="font-semibold text-slate-900 cursor-pointer">Who can invest on the platform?</summary>
                    <p class="text-sm text-slate-600 mt-3">
                        You must self-certify as a high net-worth or sophisticated investor under UK regulations.
                        Onboarding is digital and takes around 5 minutes.
                    </p>
                </details>
                <details class="border border-slate-100 rounded-xl p-4">
                    <summary class="font-semibold text-slate-900 cursor-pointer">How are payouts handled?</summary>
                    <p class="text-sm text-slate-600 mt-3">
                        Equity payouts occur at exit; mezzanine interest is settled at completion.
                        Every payout event shows up inside your dashboard, with notifications and downloadable statements.
                    </p>
                </details>
                <details class="border border-slate-100 rounded-xl p-4">
                    <summary class="font-semibold text-slate-900 cursor-pointer">Can I sell my position?</summary>
                    <p class="text-sm text-slate-600 mt-3">
                        Secondary liquidity isn’t guaranteed. If you need to exit early, contact support and we’ll
                        review options within the SPV docs.
                    </p>
                </details>
            </div>
        </div>
    </section>
@endsection


