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

    @if(file_exists(public_path('build/manifest.json')))
        @vite('resources/css/app.css')
    @else
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @endif

    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvZOOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdgNs3Y/WjMd0FDhL/g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    @stack('head')
</head>
<body class="bg-gray-50" style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 border-r border-slate-700/50 flex flex-col fixed h-full z-30 shadow-2xl">
            <!-- Logo -->
            <div class="h-16 flex items-center justify-between px-5 border-b border-slate-700/50 bg-slate-900/50">
                <div class="flex items-center">
                    <div class="h-9 w-9 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center mr-3 shadow-lg">
                        <img src="{{asset('logo.png')}}" alt="Logo" class="h-6">
                    </div>
                    <span class="text-lg font-bold text-white tracking-tight">JaeVee</span>
                </div>
                <button class="text-slate-400 hover:text-white transition-colors" id="sidebar-toggle">
                    <i class="fas fa-bars text-lg"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4">
                @php
                    $account = Auth::user();
                    $currentRoute = request()->route()->getName() ?? '';
                @endphp
                
                @if($account && in_array($account->type_id, [1,2,3]))
                    <!-- Navigation Section -->
                    <div class="px-4 mb-6">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3 px-3">Navigation</p>
                        <div class="space-y-1">
                            <a href="{{ route('admin.dashboard') }}" class="group relative flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ $currentRoute === 'admin.dashboard' ? 'bg-slate-800/50 text-white' : 'text-slate-300 hover:bg-slate-800/30 hover:text-white' }}">
                                @if($currentRoute === 'admin.dashboard')
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-teal-400 rounded-r"></div>
                                @endif
                                <i class="fas fa-home w-5 text-center mr-3 {{ $currentRoute === 'admin.dashboard' ? 'text-teal-400' : 'text-slate-400 group-hover:text-teal-400' }}"></i>
                                <span>Home</span>
                            </a>
                            <a href="{{ route('admin.investments.index') }}" class="group relative flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ str_starts_with($currentRoute, 'admin.investments') ? 'bg-slate-800/50 text-white' : 'text-slate-300 hover:bg-slate-800/30 hover:text-white' }}">
                                @if(str_starts_with($currentRoute, 'admin.investments'))
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-teal-400 rounded-r"></div>
                                @endif
                                <i class="fas fa-chart-line w-5 text-center mr-3 {{ str_starts_with($currentRoute, 'admin.investments') ? 'text-teal-400' : 'text-slate-400 group-hover:text-teal-400' }}"></i>
                                <span>Investments</span>
                            </a>
                            <a href="{{ route('admin.projects.index') }}" class="group relative flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ str_starts_with($currentRoute, 'admin.projects') ? 'bg-slate-800/50 text-white' : 'text-slate-300 hover:bg-slate-800/30 hover:text-white' }}">
                                @if(str_starts_with($currentRoute, 'admin.projects'))
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-teal-400 rounded-r"></div>
                                @endif
                                <i class="fas fa-folder-open w-5 text-center mr-3 {{ str_starts_with($currentRoute, 'admin.projects') ? 'text-teal-400' : 'text-slate-400 group-hover:text-teal-400' }}"></i>
                                <span>Projects</span>
                            </a>
                            <a href="{{ route('admin.updates.index') }}" class="group relative flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ str_starts_with($currentRoute, 'admin.updates') ? 'bg-slate-800/50 text-white' : 'text-slate-300 hover:bg-slate-800/30 hover:text-white' }}">
                                @if(str_starts_with($currentRoute, 'admin.updates'))
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-teal-400 rounded-r"></div>
                                @endif
                                <i class="fas fa-bullhorn w-5 text-center mr-3 {{ str_starts_with($currentRoute, 'admin.updates') ? 'text-teal-400' : 'text-slate-400 group-hover:text-teal-400' }}"></i>
                                <span>Updates</span>
                            </a>
                            <a href="{{ route('admin.accounts.index') }}" class="group relative flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ str_starts_with($currentRoute, 'admin.accounts') ? 'bg-slate-800/50 text-white' : 'text-slate-300 hover:bg-slate-800/30 hover:text-white' }}">
                                @if(str_starts_with($currentRoute, 'admin.accounts'))
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-teal-400 rounded-r"></div>
                                @endif
                                <i class="fas fa-users w-5 text-center mr-3 {{ str_starts_with($currentRoute, 'admin.accounts') ? 'text-teal-400' : 'text-slate-400 group-hover:text-teal-400' }}"></i>
                                <span>Accounts</span>
                            </a>
                            <a href="{{ route('admin.system-status.index') }}" class="group relative flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ str_starts_with($currentRoute, 'admin.system-status') ? 'bg-slate-800/50 text-white' : 'text-slate-300 hover:bg-slate-800/30 hover:text-white' }}">
                                @if(str_starts_with($currentRoute, 'admin.system-status'))
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-teal-400 rounded-r"></div>
                                @endif
                                <i class="fas fa-info-circle w-5 text-center mr-3 {{ str_starts_with($currentRoute, 'admin.system-status') ? 'text-teal-400' : 'text-slate-400 group-hover:text-teal-400' }}"></i>
                                <span>System Status</span>
                            </a>
                        </div>
                    </div>
                @elseif($account && $account->type_id == 8)
                    <div class="px-4 mb-6">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3 px-3">Account</p>
                        <div class="space-y-1">
                            <a href="{{ route('admin.accounts.show', $account->id) }}" class="group relative flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ str_starts_with($currentRoute, 'admin.accounts') ? 'bg-slate-800/50 text-white' : 'text-slate-300 hover:bg-slate-800/30 hover:text-white' }}">
                                @if(str_starts_with($currentRoute, 'admin.accounts'))
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-teal-400 rounded-r"></div>
                                @endif
                                <i class="fas fa-user w-5 text-center mr-3 {{ str_starts_with($currentRoute, 'admin.accounts') ? 'text-teal-400' : 'text-slate-400 group-hover:text-teal-400' }}"></i>
                                <span>My Account</span>
                            </a>
                        </div>
                    </div>
                @endif
            </nav>

            <!-- User Section -->
            @if($account)
                <div class="border-t border-slate-700/50 p-4 bg-slate-900/30">
                    <div class="flex items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-md">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-3 flex-1 min-w-0">
                            <p class="text-sm font-semibold text-white truncate">{{ $account->name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $account->email }}</p>
                        </div>
                    </div>
                    <form action="{{ route('investor.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-slate-300 hover:text-white bg-slate-800/50 hover:bg-slate-800 rounded-lg transition-all duration-200">
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
                    @php
                        $currentRoute = request()->route()->getName() ?? '';
                        $routeSegments = explode('.', $currentRoute);
                        $breadcrumbs = [];
                        
                        // Build breadcrumbs from route
                        if ($currentRoute && $currentRoute !== 'admin.dashboard') {
                            // Always start with Home
                            $breadcrumbs[] = [
                                'label' => 'Home',
                                'url' => route('admin.dashboard'),
                                'active' => false
                            ];
                            
                            // Map route segments to labels
                            $routeLabels = [
                                'investments' => 'Investments',
                                'projects' => 'Projects',
                                'updates' => 'Updates',
                                'accounts' => 'Accounts',
                                'system-status' => 'System Status',
                                'create' => 'Create',
                                'edit' => 'Edit',
                                'show' => 'Details',
                                'index' => 'List',
                            ];
                            
                            $path = '';
                            
                            foreach ($routeSegments as $index => $segment) {
                                if ($segment === 'admin') continue;
                                
                                // Skip numeric segments (IDs) - but keep them for context
                                if (is_numeric($segment)) continue;
                                
                                $path .= ($path ? '.' : '') . $segment;
                                
                                // Get label
                                $label = $routeLabels[$segment] ?? ucfirst(str_replace('-', ' ', $segment));
                                
                                // Check if this is the last non-numeric segment
                                $isLast = true;
                                for ($j = $index + 1; $j < count($routeSegments); $j++) {
                                    if ($routeSegments[$j] !== 'admin' && !is_numeric($routeSegments[$j])) {
                                        $isLast = false;
                                        break;
                                    }
                                }
                                
                                // For show/edit routes, try to get the model name
                                if (($segment === 'show' || $segment === 'edit') && $isLast) {
                                    $routeParams = request()->route()->parameters();
                                    foreach ($routeParams as $paramName => $paramValue) {
                                        // Try to get model name from route parameter
                                        if (in_array($paramName, ['project', 'investment', 'account', 'update', 'id'])) {
                                            try {
                                                if ($paramName === 'project' || (str_contains($currentRoute, 'projects') && $paramName === 'id')) {
                                                    $project = \App\Models\Project::where('project_id', $paramValue)->first();
                                                    if ($project) {
                                                        $label = $project->name;
                                                    }
                                                } elseif ($paramName === 'account' || (str_contains($currentRoute, 'accounts') && $paramName === 'id')) {
                                                    $account = \App\Models\Account::find($paramValue);
                                                    if ($account) {
                                                        $label = $account->name;
                                                    }
                                                }
                                            } catch (\Exception $e) {
                                                // Ignore errors
                                            }
                                        }
                                    }
                                }
                                
                                // Build route name for URL
                                $routeName = 'admin.' . $path;
                                
                                // Determine URL based on segment type
                                $url = '#';
                                if ($segment === 'index') {
                                    // For index, link to parent
                                    $parentPath = str_replace('.index', '', $path);
                                    try {
                                        $url = route('admin.' . $parentPath . '.index');
                                    } catch (\Exception $e) {
                                        try {
                                            $url = route('admin.' . $parentPath);
                                        } catch (\Exception $e2) {
                                            $url = '#';
                                        }
                                    }
                                } elseif ($segment === 'create') {
                                    // For create, link to index
                                    $parentPath = str_replace('.create', '', $path);
                                    try {
                                        $url = route('admin.' . $parentPath . '.index');
                                    } catch (\Exception $e) {
                                        $url = '#';
                                    }
                                } elseif ($segment === 'edit' || $segment === 'show') {
                                    // For edit/show, link to index
                                    $parentPath = str_replace(['.edit', '.show'], '', $path);
                                    try {
                                        $url = route('admin.' . $parentPath . '.index');
                                    } catch (\Exception $e) {
                                        $url = '#';
                                    }
                                } else {
                                    // Try to get route URL
                                    try {
                                        $url = route($routeName . '.index');
                                    } catch (\Exception $e) {
                                        try {
                                            $url = route($routeName);
                                        } catch (\Exception $e2) {
                                            $url = '#';
                                        }
                                    }
                                }
                                
                                $breadcrumbs[] = [
                                    'label' => $label,
                                    'url' => $url,
                                    'active' => $isLast
                                ];
                            }
                        } elseif ($currentRoute === 'admin.dashboard') {
                            // On dashboard, just show Home
                            $breadcrumbs[] = [
                                'label' => 'Home',
                                'url' => route('admin.dashboard'),
                                'active' => true
                            ];
                        }
                    @endphp
                    
                    <nav class="flex items-center space-x-2 text-sm" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-2">
                            @foreach($breadcrumbs as $index => $crumb)
                                <li class="flex items-center">
                                    @if($index > 0)
                                        <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                                    @endif
                                    @if($crumb['active'] || $crumb['url'] === '#')
                                        <span class="text-gray-900 font-semibold">{{ $crumb['label'] }}</span>
                                    @else
                                        <a href="{{ $crumb['url'] }}" class="text-gray-600 hover:text-gray-900 transition-colors">
                                            {{ $crumb['label'] }}
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
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
