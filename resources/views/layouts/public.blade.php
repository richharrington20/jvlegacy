<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'JaeVee')</title>
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script src="{{ asset('js/app.js') }}"></script>
    @endif
    
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased" style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <header class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm backdrop-blur-sm bg-white/95">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <img src="{{ asset('logo.png') }}" alt="JaeVee" class="h-8 w-auto">
                <span class="font-bold text-xl text-gray-900">JaeVee</span>
            </a>
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-700">
                <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">Home</a>
                <a href="{{ route('investor.login') }}" class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-md hover:shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i>Investor Login
                </a>
            </nav>
            <button class="md:hidden text-gray-700" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-gray-100 bg-white">
            <nav class="px-6 py-4 space-y-3">
                <a href="{{ route('home') }}" class="block text-gray-700 hover:text-blue-600 font-medium">Home</a>
                <a href="{{ route('investor.login') }}" class="block px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold text-center">Investor Login</a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-gray-300 mt-20">
        <div class="max-w-7xl mx-auto px-6 py-12 grid gap-8 md:grid-cols-4">
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <img src="{{ asset('logo.png') }}" alt="JaeVee" class="h-6 w-auto opacity-90">
                    <span class="text-white font-bold text-lg">JaeVee</span>
                </div>
                <p class="text-sm text-gray-400 leading-relaxed">Institutional co-investing for serious property investors.</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-4">Company</p>
                <ul class="space-y-3">
                    <li><a href="#" class="text-sm hover:text-white transition-colors">About</a></li>
                    <li><a href="#" class="text-sm hover:text-white transition-colors">Careers</a></li>
                    <li><a href="#" class="text-sm hover:text-white transition-colors">Contact</a></li>
                </ul>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-4">Investing</p>
                <ul class="space-y-3">
                    <li><a href="{{ route('investor.login') }}" class="text-sm hover:text-white transition-colors">Investor Login</a></li>
                </ul>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-4">Compliance</p>
                <ul class="space-y-3">
                    <li><a href="#" class="text-sm hover:text-white transition-colors">Terms</a></li>
                    <li><a href="#" class="text-sm hover:text-white transition-colors">Privacy</a></li>
                    <li><a href="#" class="text-sm hover:text-white transition-colors">Risk Warning</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-700 text-center text-xs py-6 text-gray-400">
            © {{ now()->year }} JaeVee. Capital at risk. Investments are illiquid and not covered by the FSCS.
        </div>
    </footer>
</body>
</html>


