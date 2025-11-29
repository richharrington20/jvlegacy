<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard – @yield('title')</title>

    @vite('resources/css/app.css')

    <!-- Font Awesome for document icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />

</head>
<body class="bg-gray-100 text-sm text-gray-900">
@if (session()->has('masquerading_as'))
    <div class="bg-yellow-100 text-yellow-800 px-4 py-2 text-sm text-center">
        You are currently masquerading as account #{{ auth('investor')->id() }}.
        <form action="{{ route('admin.investor.stopMasquerade') }}" method="POST" class="inline-block ml-2">
            @csrf
            <button class="text-blue-600 underline">Logout</button>
        </form>
    </div>
@endif
<header class="bg-white shadow p-4 mb-6">
    <div class="max-w-6xl mx-auto flex flex-row justify-between">
                <div class="flex flex-row">
            <img src="{{asset('logo.png')}}" alt="Logo" class="h-8 mr-2">
            <h1 class="text-xl font-semibold">Dashboard</h1>
        </div>

        <nav class="flex flex-row gap-4">
            <form action="{{ route('investor.logout') }}" method="POST" class="inline-block">
                @csrf
                <button type="submit" class="text-red-600 underline">Logout</button>
            </form>
        </nav>
    </div>
</header>



<main class="max-w-6xl mx-auto px-4">
    @yield('content')
</main>

<!-- Alpine.js (global, for all pages) -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

@stack('scripts')
</body>
</html>
