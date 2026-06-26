@extends('layouts.app')

@section('content')
    <style>
        .otp-input-field {
            width: 60px; height: 60px;
            text-align: center; font-size: 24px; font-weight: 800;
            background: #121824; border: 2px solid #1e293b; border-radius: 16px;
            color: #fff; outline: none; transition: all 0.3s;
        }
        .otp-input-field:focus {
            border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
        }
    </style>

    <!-- Subtle Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[10%] left-[10%] w-[350px] h-[350px] bg-blue-600/5 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-[10%] right-[10%] w-[450px] h-[450px] bg-indigo-600/5 rounded-full blur-[120px]"></div>
    </div>

    <div class="relative z-10 w-full max-w-md p-6" x-data="{ loading: false }">
        <div class="bg-[#0f131d]/70 backdrop-blur-xl p-8 md:p-10 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.3)] border border-slate-800/80">
            <div class="text-center mb-8">
                <div class="flex justify-center mb-5">
                    <div class="bg-white/5 p-3.5 rounded-2xl shadow-inner border border-white/10 backdrop-blur-md">
                        <img src="{{ asset('images/logo.png') }}?v=1" alt="Logo Record" class="h-12 w-auto object-contain">
                    </div>
                </div>
                <h1 class="text-xl font-bold text-white tracking-tight">Verifikasi OTP</h1>
                <p class="text-slate-400 text-xs mt-1.5 font-medium">Masukkan 4 digit kode yang telah dikirim ke email Anda.</p>
            </div>

            @if(session('success'))
                <div class="mb-5 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
                    <p class="text-emerald-500 text-xs font-semibold">{{ session('success') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 p-4 bg-red-500/10 border border-red-500/20 rounded-xl">
                    <p class="text-red-500 text-xs font-semibold">{{ $errors->first() }}</p>
                </div>
            @endif

            <form action="{{ route('password.verify-otp') }}" method="POST" @submit="loading = true" class="space-y-6" x-data="otpHandler()">
                @csrf
                <input type="hidden" name="otp" x-model="otpValue">

                <div class="flex justify-center gap-4">
                    <template x-for="(digit, index) in digits" :key="index">
                        <input type="text" maxlength="1" inputmode="numeric"
                            class="otp-input-field"
                            x-model="digits[index]"
                            @input="handleInput($event, index)"
                            @keydown.backspace="handleBackspace($event, index)"
                            @paste="handlePaste($event)"
                            :x-ref="'otp_' + index">
                    </template>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3.5 rounded-xl font-semibold shadow-lg shadow-blue-500/10 hover:shadow-blue-500/20 transition-all active:scale-[0.98] flex items-center justify-center space-x-2 cursor-pointer"
                    :class="loading ? 'opacity-70 cursor-not-allowed' : ''">
                    <span x-show="!loading">Verifikasi Kode</span>
                    <span x-show="loading" class="flex items-center">
                        <svg class="animate-spin h-5 w-5 mr-3 text-current" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memverifikasi...
                    </span>
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('password.request') }}" class="text-xs text-slate-400 hover:text-white font-semibold transition-colors">&larr; Kirim ulang kode</a>
            </div>
        </div>
    </div>

    <script>
        function otpHandler() {
            return {
                digits: ['', '', '', ''],
                get otpValue() {
                    return this.digits.join('');
                },
                handleInput(e, index) {
                    const val = e.target.value.replace(/\D/g, '');
                    this.digits[index] = val;
                    if (val && index < 3) {
                        const inputs = document.querySelectorAll('.otp-input-field');
                        inputs[index + 1].focus();
                    }
                },
                handleBackspace(e, index) {
                    if (!this.digits[index] && index > 0) {
                        const inputs = document.querySelectorAll('.otp-input-field');
                        inputs[index - 1].focus();
                    }
                },
                handlePaste(e) {
                    e.preventDefault();
                    const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 4);
                    for (let i = 0; i < 4; i++) {
                        this.digits[i] = text[i] || '';
                    }
                }
            }
        }
    </script>
@endsection
