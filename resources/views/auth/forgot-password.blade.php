@extends('layouts.app')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-[#0f172a]">
    <div class="bg-[#1e293b]/50 backdrop-blur-xl p-8 rounded-3xl shadow-2xl border border-slate-800 w-full max-w-md">
        <h2 class="text-2xl font-bold text-white mb-4 text-center">Lupa Kata Sandi</h2>
        @if(session('status'))
            <div class="mb-4 p-3 bg-emerald-500/10 border border-emerald-500/20 rounded">
                <p class="text-emerald-500 text-sm">{{ session('status') }}</p>
            </div>
        @endif
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-400 mb-1" for="email">Email</label>
                <input id="email" name="email" type="email" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-white placeholder-slate-600" placeholder="you@example.com">
                @error('email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-2 rounded-xl font-semibold">
                Kirim Kode OTP
            </button>
        </form>
    </div>
</div>
@endsection
