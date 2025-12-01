@extends('layouts.public')

@section('title', 'Invest alongside JaeVee')

@section('content')
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-blue-600 via-indigo-700 to-purple-800 text-white overflow-hidden">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 0%, transparent 50%);"></div>
        
        <div class="relative max-w-7xl mx-auto px-6 py-24 lg:py-32">
            <div class="grid gap-12 lg:grid-cols-2 items-center">
                <div class="space-y-6">
                    <p class="text-sm uppercase tracking-[0.3em] text-blue-200 font-semibold">Institutional-grade co-investing</p>
                    <h1 class="text-5xl lg:text-6xl font-bold leading-tight">
                        Focused on fulfilling our existing commitments.
                    </h1>
                    <p class="text-xl text-blue-100 leading-relaxed">
                        As of now, JaeVee is no longer acquiring new development projects. We are concentrating entirely
                        on completing current projects and delivering for our existing investors.
                    </p>
                    <div class="flex flex-wrap gap-4 pt-4">
                        <a href="{{ route('investor.login') }}" class="px-8 py-4 border-2 border-white/30 text-white font-bold rounded-xl hover:bg-white/10 backdrop-blur-sm transition-all">
                            <i class="fas fa-sign-in-alt mr-2"></i>Already Invested? Sign In
                        </a>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl p-8 lg:p-10 shadow-2xl">
                    <p class="text-sm text-blue-200 uppercase tracking-wider font-semibold mb-6">Why investors stay</p>
                    <ul class="space-y-6">
                        <li class="flex gap-4 items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-green-400 rounded-full flex items-center justify-center mt-0.5">
                                <i class="fas fa-check text-green-900 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-white font-medium">Full transparency on every project milestone, payout, and document.</p>
                            </div>
                        </li>
                        <li class="flex gap-4 items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-green-400 rounded-full flex items-center justify-center mt-0.5">
                                <i class="fas fa-check text-green-900 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-white font-medium">Access both equity and mezzanine debt with curated SPVs.</p>
                            </div>
                        </li>
                        <li class="flex gap-4 items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-green-400 rounded-full flex items-center justify-center mt-0.5">
                                <i class="fas fa-check text-green-900 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-white font-medium">Direct support team—no ticket backlog, no bots.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Live Opportunities / Status Section -->
    <section class="py-20 bg-white" id="opportunities">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-12">
                <div>
                    <p class="text-sm text-gray-500 uppercase tracking-wider font-semibold mb-2">Platform update</p>
                    <h2 class="text-4xl font-bold text-gray-900">Current investment opportunities</h2>
                </div>
            </div>

            <div class="max-w-3xl mx-auto bg-gray-50 border border-gray-200 rounded-2xl p-8 text-center">
                <p class="text-lg text-gray-900 font-semibold mb-4">
                    As of now, JaeVee is no longer acquiring new development projects.
                </p>
                <p class="text-gray-700 mb-3">
                    This decision allows us to focus entirely on fulfilling our existing commitments with the same level of dedication
                    and excellence our investors have come to expect.
                </p>
                <p class="text-gray-700 mb-6">
                    We remain fully committed to completing all current projects and delivering value to our existing stakeholders.
                </p>
                <p class="text-gray-800 font-medium mb-6">
                    If you are an investor, you can continue to access your dashboard below.
                </p>
                <a href="{{ route('investor.login') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700">
                    Investor dashboard
                </a>
            </div>
        </div>
    </section>

    <!-- Why JaeVee Section -->
    <section class="py-20 bg-gradient-to-br from-gray-50 to-blue-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid gap-12 lg:grid-cols-3 mb-16">
                <div>
                    <p class="text-sm text-gray-500 uppercase tracking-wider font-semibold mb-2">Why JaeVee</p>
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Designed for serious investors</h2>
                    <p class="text-gray-600 leading-relaxed">
                        We only partner with developers that pass rigorous due diligence.
                        Investors see the same data rooms, legal docs, and update cadence
                        we rely on internally.
                    </p>
                </div>
                <div class="lg:col-span-2 grid gap-6 sm:grid-cols-2">
                    <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                        </div>
                        <p class="text-xs uppercase text-gray-500 tracking-wider font-semibold mb-2">Always transparent</p>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Live dashboards, not PDF reports</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Track construction progress, funding phases, and payouts in real time.
                            Every document—shareholders' agreement, loan agreement, certificates—is one click away.
                        </p>
                    </div>
                    <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-handshake text-green-600 text-xl"></i>
                        </div>
                        <p class="text-xs uppercase text-gray-500 tracking-wider font-semibold mb-2">Aligned incentives</p>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Co-invest alongside us</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            We participate in every SPV, so our returns depend on the same milestones as yours.
                            No "listings" marketplace—just curated developments with skin in the game.
                        </p>
                    </div>
                    <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-headset text-purple-600 text-xl"></i>
                        </div>
                        <p class="text-xs uppercase text-gray-500 tracking-wider font-semibold mb-2">Direct access</p>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Talk to the actual team</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Need clarity on a payout, document, or timeline? Raise a support request from any project card
                            and the humans running the deal reply—no outsourced help desk.
                        </p>
                    </div>
                    <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all">
                        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-shield-alt text-amber-600 text-xl"></i>
                        </div>
                        <p class="text-xs uppercase text-gray-500 tracking-wider font-semibold mb-2">Regulated flow</p>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Bank-grade tooling</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Digital KYC/AML, segregated client accounts, and institutional reporting
                            baked in from day one—because trust isn't a feature, it's the baseline.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-20 bg-white" id="faq">
        <div class="max-w-4xl mx-auto px-6">
            <div class="text-center mb-12">
                <p class="text-sm text-gray-500 uppercase tracking-wider font-semibold mb-2">Questions investors ask first</p>
                <h2 class="text-4xl font-bold text-gray-900">FAQ</h2>
            </div>
            <div class="space-y-4">
                <details class="group border-2 border-gray-100 rounded-xl p-6 hover:border-blue-200 transition-all bg-white">
                    <summary class="font-bold text-gray-900 cursor-pointer flex items-center justify-between">
                        <span>Who can invest on the platform?</span>
                        <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-gray-600 mt-4 leading-relaxed">
                        You must self-certify as a high net-worth or sophisticated investor under UK regulations.
                        Onboarding is digital and takes around 5 minutes.
                    </p>
                </details>
                <details class="group border-2 border-gray-100 rounded-xl p-6 hover:border-blue-200 transition-all bg-white">
                    <summary class="font-bold text-gray-900 cursor-pointer flex items-center justify-between">
                        <span>How are payouts handled?</span>
                        <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-gray-600 mt-4 leading-relaxed">
                        Equity payouts occur at exit; mezzanine interest is settled at completion.
                        Every payout event shows up inside your dashboard, with notifications and downloadable statements.
                    </p>
                </details>
                <details class="group border-2 border-gray-100 rounded-xl p-6 hover:border-blue-200 transition-all bg-white">
                    <summary class="font-bold text-gray-900 cursor-pointer flex items-center justify-between">
                        <span>Can I sell my position?</span>
                        <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-gray-600 mt-4 leading-relaxed">
                        Secondary liquidity isn't guaranteed. If you need to exit early, contact support and we'll
                        review options within the SPV docs.
                    </p>
                </details>
            </div>
        </div>
    </section>
@endsection
