@extends('layouts.public')

@section('title', 'Invest alongside JaeVee')

@section('content')
    <section class="py-24 bg-white">
        <div class="max-w-3xl mx-auto px-6 text-center">
            <p class="text-sm text-gray-500 uppercase tracking-wider font-semibold mb-3">Platform update</p>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                Current investment opportunities
            </h1>

            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-8">
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
@endsection
