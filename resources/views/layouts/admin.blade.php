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
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col fixed h-full z-30 shadow-sm">
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-white">
                <img src="{{asset('logo.png')}}" alt="Logo" class="h-8 mr-2">
                <span class="text-lg font-bold text-gray-900 tracking-tight">JaeVee System</span>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4">
                @php
                    $account = Auth::user();
                    $currentRoute = request()->route()->getName() ?? '';
                @endphp
                
                @if($account && in_array($account->type_id, [1,2,3]))
                    <div class="px-3 space-y-1">
                        <a href="{{ route('admin.investments.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ str_starts_with($currentRoute, 'admin.investments') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-chart-line w-5 mr-3 text-center"></i>
                            <span>Investments</span>
                        </a>
                        <a href="{{ route('admin.projects.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ str_starts_with($currentRoute, 'admin.projects') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-folder-open w-5 mr-3 text-center"></i>
                            <span>Projects</span>
                        </a>
                        <a href="{{ route('admin.updates.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ str_starts_with($currentRoute, 'admin.updates') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-bullhorn w-5 mr-3 text-center"></i>
                            <span>Updates</span>
                        </a>
                        <a href="{{ route('admin.accounts.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ str_starts_with($currentRoute, 'admin.accounts') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-users w-5 mr-3 text-center"></i>
                            <span>Accounts</span>
                        </a>
                    </div>
                @elseif($account && $account->type_id == 8)
                    <div class="px-3 space-y-1">
                        <a href="{{ route('admin.accounts.show', $account->id) }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ str_starts_with($currentRoute, 'admin.accounts') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fas fa-user w-5 mr-3 text-center"></i>
                            <span>My Account</span>
                        </a>
                    </div>
                @endif
            </nav>

            <!-- User Section -->
            @if($account)
                <div class="border-t border-gray-200 p-4">
                    <div class="flex items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-3 flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $account->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $account->email }}</p>
                        </div>
                    </div>
                    <form action="{{ route('investor.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-red-600 rounded-lg hover:bg-red-50 transition-colors">
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
