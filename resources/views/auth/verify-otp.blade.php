<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - JMN Matrix</title>
    @vite('resources/css/app.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .otp-input {
            width: 60px; height: 60px;
            text-align: center; font-size: 24px; font-weight: 800;
            background: #0f172a; border: 2px solid #1e293b; border-radius: 16px;
            color: #fff; outline: none; transition: all 0.3s;
        }
        .otp-input:focus {
            border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
        }
    </style>
</head>
<body class="bg-[#0f172a] flex items-center justify-center min-h-screen selection:bg-blue-600 selection:text-white">
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[10%] left-[10%] w-[400px] h-[400px] bg-blue-600/5 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[10%] right-[10%] w-[500px] h-[500px] bg-indigo-600/5 rounded-full blur-[120px]"></div>
    </div>

    <div class="relative z-10 w-full max-w-md p-6" x-data="{ loading: false }">
        <div class="bg-[#1e293b]/50 backdrop-blur-xl p-8 md:p-10 rounded-3xl shadow-2xl border border-slate-800">
            <div class="text-center mb-8">
                <div class="flex justify-center mb-4">
                    <div class="bg-white/95 p-4 rounded-2xl shadow-xl shadow-blue-500/10 border border-white/20">
                        <img src="{{ asset('images/logo.png') }}?v=1" alt="Logo Record" class="h-12 w-auto object-contain">
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Verifikasi OTP</h1>
                <p class="text-slate-400 text-sm mt-2">Masukkan 4 digit kode yang telah dikirim ke email Anda.</p>
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

            <form action="{{ route('password.verify-otp') }}" method="POST" @submit="loading = true" class="space-y-8" x-data="otpHandler()">
                @csrf
                <input type="hidden" name="otp" x-model="otpValue">

                <div class="flex justify-center gap-4">
                    <template x-for="(digit, index) in digits" :key="index">
                        <input type="text" maxlength="1" inputmode="numeric"
                            class="otp-input"
                            x-model="digits[index]"
                            @input="handleInput($event, index)"
                            @keydown.backspace="handleBackspace($event, index)"
                            @paste="handlePaste($event)"
                            :x-ref="'otp_' + index">
                    </template>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-50 text-white hover:text-blue-600 py-3.5 rounded-xl font-bold shadow-lg shadow-blue-600/10 transition-all active:scale-[0.98] flex items-center justify-center space-x-2"
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
                        const inputs = document.querySelectorAll('.otp-input');
                        inputs[index + 1].focus();
                    }
                },
                handleBackspace(e, index) {
                    if (!this.digits[index] && index > 0) {
                        const inputs = document.querySelectorAll('.otp-input');
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
</body>
</html>
