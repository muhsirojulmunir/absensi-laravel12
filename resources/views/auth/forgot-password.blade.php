@extends('layouts.app')

@section('content')
    <!-- Subtle Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[10%] left-[10%] w-[350px] h-[350px] bg-blue-600/5 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-[10%] right-[10%] w-[450px] h-[450px] bg-indigo-600/5 rounded-full blur-[120px]"></div>
    </div>

    <div class="relative z-10 w-full max-w-md p-6">
        <div class="bg-[#0f131d]/70 backdrop-blur-xl p-8 md:p-10 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.3)] border border-slate-800/80">
            <!-- Branding -->
            <div class="text-center mb-8">
                <div class="flex justify-center mb-5">
                    <div class="bg-white/5 p-3.5 rounded-2xl shadow-inner border border-white/10 backdrop-blur-md">
                        <img src="{{ asset('images/logo.png') }}?v=1" alt="Logo Record" class="h-12 w-auto object-contain">
                    </div>
                </div>
                <h1 class="text-xl font-bold text-white tracking-tight">Lupa Kata Sandi</h1>
                <p class="text-slate-400 text-xs mt-1.5 font-medium">Masukkan email Anda untuk menerima kode verifikasi OTP</p>
            </div>

            @if(session('status'))
                <div class="mb-5 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
                    <p class="text-emerald-500 text-xs font-semibold">{{ session('status') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2" for="email">Alamat Email</label>
                    <input id="email" name="email" type="email" required 
                        class="w-full bg-[#121824] border border-slate-800/80 rounded-xl px-4 py-3 text-white text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none placeholder-slate-650" 
                        placeholder="nama@perusahaan.com">
                    @error('email')
                        <p class="mt-2 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>
                
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3.5 rounded-xl font-semibold shadow-lg shadow-blue-500/10 hover:shadow-blue-500/20 transition-all active:scale-[0.98] flex items-center justify-center cursor-pointer">
                    Kirim Kode OTP
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-xs text-slate-400 hover:text-white font-semibold transition-colors">&larr; Kembali ke halaman Login</a>
            </div>
        </div>
    </div>
@endsection
