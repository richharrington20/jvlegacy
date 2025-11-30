<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investor Login - JaeVee</title>
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite('resources/css/app.css')
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .login-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .login-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
    </style>
</head>
<body class="login-gradient min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
        <!-- Logo and Header -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <img src="{{ asset('logo.png') }}" alt="JaeVee Logo" class="h-20 w-auto drop-shadow-lg">
            </div>
            <h1 class="text-4xl font-bold text-white mb-2 tracking-tight">Welcome Back</h1>
            <p class="text-white/90 text-lg">Sign in to your investor account</p>
        </div>

        <!-- System Status -->
        @if($systemStatus)
            <div class="mb-6 login-card rounded-2xl shadow-2xl p-5 border border-white/20">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        @if($systemStatus->status_type === 'error')
                            <i class="fas fa-exclamation-circle text-red-500 text-2xl"></i>
                        @elseif($systemStatus->status_type === 'warning')
                            <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl"></i>
                        @elseif($systemStatus->status_type === 'success')
                            <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                        @elseif($systemStatus->status_type === 'maintenance')
                            <i class="fas fa-tools text-orange-500 text-2xl"></i>
                        @else
                            <i class="fas fa-info-circle text-blue-500 text-2xl"></i>
                        @endif
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-base font-semibold text-gray-900 mb-1">
                            {{ $systemStatus->title }}
                        </h3>
                        <div class="text-sm text-gray-700">
                            {!! $systemStatus->message !!}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Login Card -->
        <div class="login-card rounded-2xl shadow-2xl p-8 border border-white/20">
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                    <div class="flex">
                        <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3"></i>
                        <div>
                            <p class="text-sm font-medium text-red-800 mb-1">Please correct the following errors:</p>
                            <ul class="list-disc list-inside text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('investor.login.post') }}" class="space-y-6">
                @csrf
                
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-gray-400"></i>Email Address
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        autocomplete="email" 
                        required 
                        autofocus
                        value="{{ old('email') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                        placeholder="you@example.com"
                    />
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-gray-400"></i>Password
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        autocomplete="current-password" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                        placeholder="Enter your password"
                    />
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold py-3.5 px-4 rounded-xl hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl"
                >
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign In
                </button>
            </form>

            <!-- Footer -->
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500">
                    Secure investor portal access
                </p>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="mt-6 text-center">
            <p class="text-white/80 text-sm">
                Need help? <a href="#" class="text-white font-medium hover:underline">Contact Support</a>
            </p>
        </div>
    </div>
</body>
</html>
