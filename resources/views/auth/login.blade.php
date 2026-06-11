<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('images/logo_record.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo_record.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JMN Matrix</title>
    @vite('resources/css/app.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-[#0f172a] flex items-center justify-center min-h-screen selection:bg-blue-600 selection:text-white">
    <!-- Subtle Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[10%] left-[10%] w-[400px] h-[400px] bg-blue-600/5 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[10%] right-[10%] w-[500px] h-[500px] bg-indigo-600/5 rounded-full blur-[120px]">
        </div>
    </div>

    <div class="relative z-10 w-full max-w-md p-6" x-data="{ loading: false }">
        <div class="bg-[#1e293b]/50 backdrop-blur-xl p-8 md:p-10 rounded-3xl shadow-2xl border border-slate-800">
            <!-- Branding -->
            <div class="text-center mb-10">
                <div class="flex justify-center mb-4">
                    <div class="bg-white/95 p-4 rounded-2xl shadow-xl shadow-blue-500/10 backdrop-blur-sm border border-white/20">
                        <img src="{{ asset('images/logo.png') }}?v=1" alt="Logo Record" class="h-16 w-auto object-contain transition-transform hover:scale-105 duration-300">
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Selamat Datang</h1>
                <p class="text-slate-400 text-sm mt-2">Masuk ke akun JMN Matrix Anda</p>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
                    <p class="text-emerald-500 text-xs font-semibold">{{ session('success') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-xl">
                    <p class="text-red-500 text-xs font-semibold">{{ $errors->first() }}</p>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" @submit="loading = true" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Username</label>
                    <input type="text" name="username" required
                        value="{{ request()->cookie('remember_username') }}"
                        class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3.5 text-white text-sm focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all outline-none placeholder-slate-600"
                        placeholder="your_username">
                </div>

                <div x-data="{ showPassword: false }">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Kata
                        Sandi</label>
                    <div style="position: relative;">
                        <input :type="showPassword ? 'text' : 'password'" name="password" required
                            class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3.5 text-white text-sm focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all outline-none placeholder-slate-600"
                            style="padding-right: 48px;"
                            placeholder="••••••••">
                        <button type="button" @click="showPassword = !showPassword"
                            style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 0; color: #94a3b8;"
                            onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">
                            <svg x-show="!showPassword" width="20" height="20" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showPassword" width="20" height="20" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center text-slate-400 cursor-pointer group">
                        <input type="checkbox" name="remember"
                            class="w-4 h-4 rounded border-slate-800 bg-slate-900 text-blue-600 focus:ring-offset-slate-900">
                        <span class="ml-2 group-hover:text-slate-300">Ingat Saya</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-blue-500 hover:text-blue-400 font-semibold">Lupa Kata Sandi?</a>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-50 text-white hover:text-blue-600 py-3.5 rounded-xl font-bold shadow-lg shadow-blue-600/10 transition-all active:scale-[0.98] flex items-center justify-center space-x-2"
                    :class="loading ? 'opacity-70 cursor-not-allowed' : ''">
                    <span x-show="!loading">Masuk</span>
                    <span x-show="loading" class="flex items-center">
                        <svg class="animate-spin h-5 w-5 mr-3 text-current" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                                fill="none"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Mengautentikasi...
                    </span>
                </button>
            </form>

            <div class="mt-8 pt-8 border-t border-slate-800/50 text-center">
                <p class="text-slate-500 text-xs">JMN Matrix v2.0 &copy; {{ date('Y') }}</p>
            </div>
        </div>
    </div>
</body>

</html>