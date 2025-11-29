<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – @yield('title')</title>

    <!-- Google Fonts - Inter (modern, clean font used in professional admin templates) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite('resources/css/app.css')

    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvZOOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdgNs3Y/WjMd0FDhL/g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-gray-50" style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-gray-900 via-gray-800 to-gray-900 border-r border-gray-700 flex flex-col fixed h-full z-30 shadow-2xl">
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-gray-700 bg-gradient-to-r from-blue-600 to-blue-500">
                <div class="flex items-center">
                    <div class="h-10 w-10 rounded-lg bg-white/20 flex items-center justify-center mr-3 backdrop-blur-sm">
                        <img src="{{asset('logo.png')}}" alt="Logo" class="h-7">
                    </div>
                    <span class="text-lg font-bold text-white tracking-tight">JaeVee System</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4">
                @php
                    $account = Auth::user();
                    $currentRoute = request()->route()->getName() ?? '';
                @endphp
                
                @if($account && in_array($account->type_id, [1,2,3]))
                    <div class="px-3 space-y-2">
                        <a href="{{ route('admin.investments.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ str_starts_with($currentRoute, 'admin.investments') ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/50' : 'text-gray-300 hover:bg-gray-700/50 hover:text-white' }}">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg mr-3 {{ str_starts_with($currentRoute, 'admin.investments') ? 'bg-white/20' : 'bg-gray-700/50 group-hover:bg-gray-600/50' }} transition-colors">
                                <i class="fas fa-chart-line text-lg {{ str_starts_with($currentRoute, 'admin.investments') ? 'text-white' : 'text-blue-400' }}"></i>
                            </div>
                            <span class="font-semibold">Investments</span>
                            @if(str_starts_with($currentRoute, 'admin.investments'))
                                <div class="ml-auto w-2 h-2 rounded-full bg-white"></div>
                            @endif
                        </a>
                        <a href="{{ route('admin.projects.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ str_starts_with($currentRoute, 'admin.projects') ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/50' : 'text-gray-300 hover:bg-gray-700/50 hover:text-white' }}">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg mr-3 {{ str_starts_with($currentRoute, 'admin.projects') ? 'bg-white/20' : 'bg-gray-700/50 group-hover:bg-gray-600/50' }} transition-colors">
                                <i class="fas fa-folder-open text-lg {{ str_starts_with($currentRoute, 'admin.projects') ? 'text-white' : 'text-green-400' }}"></i>
                            </div>
                            <span class="font-semibold">Projects</span>
                            @if(str_starts_with($currentRoute, 'admin.projects'))
                                <div class="ml-auto w-2 h-2 rounded-full bg-white"></div>
                            @endif
                        </a>
                        <a href="{{ route('admin.updates.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ str_starts_with($currentRoute, 'admin.updates') ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/50' : 'text-gray-300 hover:bg-gray-700/50 hover:text-white' }}">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg mr-3 {{ str_starts_with($currentRoute, 'admin.updates') ? 'bg-white/20' : 'bg-gray-700/50 group-hover:bg-gray-600/50' }} transition-colors">
                                <i class="fas fa-bullhorn text-lg {{ str_starts_with($currentRoute, 'admin.updates') ? 'text-white' : 'text-yellow-400' }}"></i>
                            </div>
                            <span class="font-semibold">Updates</span>
                            @if(str_starts_with($currentRoute, 'admin.updates'))
                                <div class="ml-auto w-2 h-2 rounded-full bg-white"></div>
                            @endif
                        </a>
                        <a href="{{ route('admin.accounts.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ str_starts_with($currentRoute, 'admin.accounts') ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/50' : 'text-gray-300 hover:bg-gray-700/50 hover:text-white' }}">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg mr-3 {{ str_starts_with($currentRoute, 'admin.accounts') ? 'bg-white/20' : 'bg-gray-700/50 group-hover:bg-gray-600/50' }} transition-colors">
                                <i class="fas fa-users text-lg {{ str_starts_with($currentRoute, 'admin.accounts') ? 'text-white' : 'text-purple-400' }}"></i>
                            </div>
                            <span class="font-semibold">Accounts</span>
                            @if(str_starts_with($currentRoute, 'admin.accounts'))
                                <div class="ml-auto w-2 h-2 rounded-full bg-white"></div>
                            @endif
                        </a>
                    </div>
                @elseif($account && $account->type_id == 8)
                    <div class="px-3 space-y-2">
                        <a href="{{ route('admin.accounts.show', $account->id) }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ str_starts_with($currentRoute, 'admin.accounts') ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/50' : 'text-gray-300 hover:bg-gray-700/50 hover:text-white' }}">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg mr-3 {{ str_starts_with($currentRoute, 'admin.accounts') ? 'bg-white/20' : 'bg-gray-700/50 group-hover:bg-gray-600/50' }} transition-colors">
                                <i class="fas fa-user text-lg {{ str_starts_with($currentRoute, 'admin.accounts') ? 'text-white' : 'text-blue-400' }}"></i>
                            </div>
                            <span class="font-semibold">My Account</span>
                            @if(str_starts_with($currentRoute, 'admin.accounts'))
                                <div class="ml-auto w-2 h-2 rounded-full bg-white"></div>
                            @endif
                        </a>
                    </div>
                @endif
            </nav>

            <!-- User Section -->
            @if($account)
                <div class="border-t border-gray-700 p-4 bg-gray-800/50">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg ring-2 ring-blue-400/20">
                                <i class="fas fa-user text-white text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-3 flex-1 min-w-0">
                            <p class="text-sm font-semibold text-white truncate">{{ $account->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $account->email }}</p>
                        </div>
                    </div>
                    <form action="{{ route('investor.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center px-4 py-2.5 text-sm font-medium text-white bg-red-600/80 hover:bg-red-600 rounded-xl transition-all duration-200 shadow-md hover:shadow-lg hover:scale-[1.02]">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            @endif
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden ml-64">
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 sticky top-0 z-20 shadow-sm">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900 tracking-tight">@yield('title', 'Dashboard')</h1>
                </div>
                <div class="flex items-center space-x-4">
                    @if (session()->has('masquerading_from_admin'))
                        <div class="flex items-center px-3 py-1 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800 text-xs">
                            <i class="fas fa-user-secret mr-2"></i>
                            <span>Masquerading as #{{ auth('investor')->id() }}</span>
                            <form action="{{ route('investor.stopMasquerade') }}" method="POST" class="inline-block ml-2">
                                @csrf
                                <button class="text-yellow-700 hover:text-yellow-900 underline">Return</button>
                            </form>
                        </div>
                    @endif
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
                @if (session('success'))
                    <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('status'))
                    <div class="mb-4 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">{{ session('status') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
