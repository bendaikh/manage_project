<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'BoxIno') }} - Login</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, rgba(46, 16, 101, 0.85) 0%, rgba(76, 29, 149, 0.85) 100%),
                        url('https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=2070&auto=format&fit=crop') center/cover;
            background-blend-mode: overlay;
        }
        .gradient-bg-alt {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .input-focus {
            transition: all 0.3s ease;
        }
        .input-focus:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.3);
        }
        .warehouse-bg {
            background: linear-gradient(to bottom right, rgba(249, 250, 251, 0.98), rgba(243, 244, 246, 0.98)),
                        url('https://images.unsplash.com/photo-1553413077-190dd305871c?q=80&w=2070&auto=format&fit=crop') center/cover;
            background-blend-mode: overlay;
        }
        .text-shadow {
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.55);
        }
        .text-shadow-sm {
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.45);
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex">
        <!-- Left Side - Branding & Info -->
        <div class="hidden lg:flex lg:w-1/2 gradient-bg relative overflow-hidden">
            <!-- Animated Background Elements -->
            <div class="absolute top-10 left-10 w-72 h-72 bg-white opacity-10 rounded-full blur-3xl animate-float"></div>
            <div class="absolute bottom-10 right-10 w-96 h-96 bg-purple-300 opacity-10 rounded-full blur-3xl animate-float" style="animation-delay: 1.5s;"></div>
            
            <div class="relative z-10 flex flex-col justify-center px-16 text-white">
                <!-- Logo Section -->
                <div class="mb-12">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <h1 class="text-3xl font-bold text-shadow">Boxino</h1>
                    </div>
                    <h2 class="text-4xl font-bold leading-tight mb-4 text-shadow">
                        Warehouse & Order<br/>Management System
                    </h2>
                    <p class="text-purple-100 text-lg text-shadow-sm">
                        Streamline your inventory, manage orders efficiently, and track shipments all in one powerful platform.
                    </p>
                </div>

                <!-- Feature Highlights -->
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="mt-1 w-6 h-6 bg-white bg-opacity-20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">Real-time Inventory Tracking</h3>
                            <p class="text-purple-100 text-sm">Monitor stock levels across multiple warehouses</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="mt-1 w-6 h-6 bg-white bg-opacity-20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">Automated Order Processing</h3>
                            <p class="text-purple-100 text-sm">Streamline fulfillment and reduce errors</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="mt-1 w-6 h-6 bg-white bg-opacity-20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">Advanced Analytics & Reporting</h3>
                            <p class="text-purple-100 text-sm">Make data-driven decisions with comprehensive insights</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12 warehouse-bg">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-600 to-purple-700 rounded-2xl shadow-lg mb-4">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Boxino</h1>
                </div>

                <!-- Login Card -->
                <div class="glass-effect rounded-2xl shadow-2xl p-8 sm:p-10">
                    <div class="mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 mb-2">Welcome back!</h2>
                        <p class="text-gray-600">Enter your credentials to access your account</p>
                    </div>

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="mb-6 font-medium text-sm text-green-700 bg-green-50 border border-green-200 p-4 rounded-lg flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-6">
                        @csrf

                        <!-- Email Address -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                Email Address
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                    </svg>
                                </div>
                                <input id="email" 
                                       type="email" 
                                       name="email" 
                                       value="{{ old('email') }}"
                                       placeholder="you@example.com"
                                       class="input-focus w-full pl-12 pr-4 py-3.5 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('email') border-red-400 @enderror"
                                       required 
                                       autofocus 
                                       autocomplete="username">
                            </div>
                            @error('email')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                                Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input id="password" 
                                       type="password" 
                                       name="password"
                                       placeholder="••••••••"
                                       class="input-focus w-full pl-12 pr-4 py-3.5 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('password') border-red-400 @enderror"
                                       required 
                                       autocomplete="current-password">
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="flex items-center justify-between">
                            <label for="remember_me" class="flex items-center group cursor-pointer">
                                <input id="remember_me" 
                                       type="checkbox" 
                                       name="remember"
                                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded transition duration-150">
                                <span class="ml-2 text-sm text-gray-700 group-hover:text-gray-900 transition duration-150">Remember me</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" 
                                   class="text-sm font-semibold text-purple-600 hover:text-purple-700 transition duration-150">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button type="submit" 
                                    class="w-full flex justify-center items-center py-4 px-6 border border-transparent rounded-xl shadow-lg text-base font-semibold text-white bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transform transition duration-150 hover:scale-[1.02] active:scale-[0.98]">
                                <span>Sign in to your account</span>
                                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <p class="mt-8 text-center text-sm text-gray-500">
                    © {{ date('Y') }} Boxino. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
