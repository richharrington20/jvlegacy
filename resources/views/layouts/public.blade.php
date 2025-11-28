<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'JaeVee')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <header class="bg-white border-b border-slate-100 sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="font-semibold text-slate-900 text-lg">JaeVee</a>
            <nav class="flex items-center gap-6 text-sm text-slate-600">
                <a href="{{ route('home') }}" class="hover:text-slate-900">Home</a>
                <a href="{{ route('public.projects.index') }}" class="hover:text-slate-900">Opportunities</a>
                <a href="#faq" class="hover:text-slate-900">FAQ</a>
                <a href="{{ route('investor.login') }}" class="px-4 py-2 rounded-lg bg-slate-900 text-white text-xs uppercase tracking-wide">
                    Investor login
                </a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="bg-slate-900 text-slate-400 text-sm mt-16">
        <div class="max-w-6xl mx-auto px-6 py-10 grid gap-8 md:grid-cols-4">
            <div>
                <h4 class="text-white font-semibold">JaeVee</h4>
                <p class+=""text-xs text-slate-500 mt-2">Institutional co-investing for serious property investors.</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">Company</p>
                <ul class="space-y-2 mt-3">
                    <li><a href="#" class="hover:text-white">About</a></li>
                    <li><a href="#" class="hover:text-white">Careers</a></li>
                    <li><a href="#" class="hover:text-white">Contact</a></li>
                </ul>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">Investing</p>
                <ul class="space-y-2 mt-3">
                    <li><a href="{{ route('public.projects.index') }}" class="hover:text-white">Projects</a></li>
                    <li><a href="{{ route('investor.login') }}" class="hover:text-white">Investor login</a></li>
                </ul>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">Compliance</p>
                <ul class="space-y-2 mt-3">
                    <li><a href="#" class="hover:text-white">Terms</a></li>
                    <li><a href="#" class="hover:text-white">Privacy</a></li>
                    <li><a href="#" class="hover:text-white">Risk warning</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 text-center text-xs py-4">
            © {{ now()->year }} JaeVee. Capital at risk. Investments are illiquid and not covered by the FSCS.
        </div>
    </footer>
</body>
</html>


