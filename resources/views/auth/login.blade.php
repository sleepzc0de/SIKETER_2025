<!-- resources/views/auth/login.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
    <div class="flex min-h-full">
        <!-- Left side - Background image -->
        <div class="hidden lg:block relative flex-1">
            <div class="absolute inset-0 bg-gradient-to-br from-navy-900 via-navy-800 to-blue-900">
                <div class="absolute inset-0 bg-gradient-to-r from-yellow-400/20 to-transparent"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center text-white">
                        <div class="flex justify-center mb-8">
                            <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-yellow-400 to-yellow-600 shadow-2xl">
                                <svg class="h-10 w-10 text-navy-900" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"/>
                                </svg>
                            </div>
                        </div>
                        <h1 class="text-4xl font-bold mb-4">Aplikasi Ketatausahaan</h1>
                        <p class="text-xl text-gray-300 mb-2">Biro Manajemen BMN dan Pengadaan</p>
                        <p class="text-lg text-gray-400">Kementerian Keuangan Republik Indonesia</p>
                    </div>
                </div>
                <!-- Decorative elements -->
                <div class="absolute top-20 left-20 h-32 w-32 rounded-full bg-yellow-400/10"></div>
                <div class="absolute bottom-20 right-20 h-48 w-48 rounded-full bg-blue-400/10"></div>
                <div class="absolute top-1/2 left-1/4 h-24 w-24 rounded-full bg-white/5"></div>
            </div>
        </div>

        <!-- Right side - Login form -->
        <div class="flex flex-1 flex-col justify-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
            <div class="mx-auto w-full max-w-sm lg:w-96">
                <div class="lg:hidden flex justify-center mb-8">
                    <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br from-yellow-400 to-yellow-600">
                        <svg class="h-8 w-8 text-navy-900" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"/>
                        </svg>
                    </div>
                </div>

                <div>
                    <h2 class="text-3xl font-bold leading-9 tracking-tight text-gray-900">Masuk ke Akun Anda</h2>
                    <p class="mt-2 text-sm leading-6 text-gray-500">
                        Silakan masuk untuk mengakses sistem ketatausahaan
                    </p>
                </div>

                <div class="mt-10">
                    @if (session('error'))
                        <div class="mb-4 rounded-md bg-red-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div>
                        <form class="space-y-6" method="POST" action="{{ route('login') }}" x-data="loginForm()">
                            @csrf

                            <div>
                                <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email</label>
                                <div class="mt-2">
                                    <input id="email" name="email" type="email" autocomplete="email" required
                                           value="{{ old('email') }}"
                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-navy-600 sm:text-sm sm:leading-6 @error('email') ring-red-500 focus:ring-red-500 @enderror">
                                    @error('email')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
                                <div class="mt-2" x-data="{ showPassword: false }">
                                    <div class="relative">
                                        <input id="password" name="password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" required
                                               class="block w-full rounded-md border-0 py-1.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-navy-600 sm:text-sm sm:leading-6 @error('password') ring-red-500 focus:ring-red-500 @enderror">
                                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                            <svg x-show="!showPassword" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <svg x-show="showPassword" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464m1.414 1.414L8.464 8.464m5.414 5.414L15.292 15.292m-1.414-1.414L15.292 15.292" />
                                            </svg>
                                        </button>
                                    </div>
                                    @error('password')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="captcha" class="block text-sm font-medium leading-6 text-gray-900">Kode Captcha</label>
                                <div class="mt-2 flex space-x-3">
                                    <div class="flex-1">
                                        <input id="captcha" name="captcha" type="text" required
                                               class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-navy-600 sm:text-sm sm:leading-6 @error('captcha') ring-red-500 focus:ring-red-500 @enderror">
                                    </div>
                                    <div class="flex flex-col">
                                        <div x-html="captchaImage" class="mb-2"></div>
                                        <button type="button" @click="refreshCaptcha()" class="text-xs text-navy-600 hover:text-navy-500">
                                            Refresh
                                        </button>
                                    </div>
                                </div>
                                @error('captcha')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-navy-600 focus:ring-navy-600">
                                    <label for="remember" class="ml-3 block text-sm leading-6 text-gray-700">Ingat saya</label>
                                </div>
                            </div>

                            <div>
                                <button type="submit" x-bind:disabled="loading"
                                        class="flex w-full justify-center rounded-md bg-gradient-to-r from-navy-600 to-navy-700 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:from-navy-700 hover:to-navy-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-navy-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                                    <span x-show="!loading">Masuk</span>
                                    <span x-show="loading" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Memproses...
                                    </span>
                                </button>
                            </div>

                            <div class="mt-6">
                                <div class="relative">
                                    <div class="absolute inset-0 flex items-center">
                                        <div class="w-full border-t border-gray-300"></div>
                                    </div>
                                    <div class="relative flex justify-center text-sm font-medium leading-6">
                                        <span class="bg-gray-50 px-6 text-gray-900">Atau</span>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <a href="{{ route('auth.google') }}"
                                       class="flex w-full items-center justify-center gap-3 rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:ring-transparent transition-all duration-200">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24">
                                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                        </svg>
                                        <span class="text-sm font-semibold leading-6">Masuk dengan Google</span>
                                    </a>
                                </div>
                            </div>
                        </form>

                        <script>
                            function loginForm() {
                                return {
                                    loading: false,
                                    captchaImage: '{!! captcha_img() !!}',

                                    refreshCaptcha() {
                                        fetch('/refresh-captcha')
                                            .then(response => response.json())
                                            .then(data => {
                                                this.captchaImage = data.captcha;
                                            });
                                    }
                                }
                            }
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
