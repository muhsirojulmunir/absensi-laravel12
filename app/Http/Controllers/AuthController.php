<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Update last login timestamp
            Auth::user()->update(['last_login_at' => now()]);

            // Remember Me functionality handled by Laravel's built‑in remember token when $remember is true.
            // When the checkbox is NOT ticked we still want to pre‑fill the username on the login page.
            // Store the username in a short‑lived cookie (30 days) only in that case.
            if (! $remember) {
                cookie()->queue('remember_username', $request->username, 60 * 24 * 30);
            }

            // Redirect based on role
            $role = Auth::user()->role->slug;
            
            return match($role) {
                'super-admin' => redirect()->intended('super-admin/dashboard'),
                'pic' => redirect()->intended('pic/dashboard'),
                'hrd' => redirect()->intended('hrd/dashboard'),
                default => redirect()->intended('karyawan/dashboard'),
            };
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'Email tidak terdaftar di sistem kami.',
        ]);

        $user = User::where('email', $request->email)->first();
        
        // Generate 4-digit OTP
        $otp = strval(rand(1000, 9999));
        
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(15),
        ]);

        // Send Email
        try {
            Mail::send('emails.otp', ['otp' => $otp, 'name' => $user->name], function($message) use ($user) {
                $message->to($user->email);
                $message->subject('Kode OTP Reset Password - JMN Matrix');
            });
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Gagal mengirim email OTP: ' . $e->getMessage()]);
        }

        // Save email in session
        session(['reset_email' => $user->email]);

        return redirect()->route('password.verify-otp-form')->with('success', 'Kode OTP 4 digit telah dikirim ke email Anda.');
    }

    public function showVerifyOtpForm()
    {
        if (!session()->has('reset_email')) {
            return redirect()->route('password.request')->withErrors(['email' => 'Silakan masukkan email Anda terlebih dahulu.']);
        }
        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:4',
        ]);

        $email = session('reset_email');
        
        if (!$email) {
            return redirect()->route('password.request')->withErrors(['email' => 'Sesi habis, silakan mulai ulang.']);
        }

        $user = User::where('email', $email)
            ->where('otp_code', $request->otp)
            ->where('otp_expires_at', '>=', now())
            ->first();

        if (!$user) {
            return back()->withErrors(['otp' => 'Kode OTP salah atau sudah kadaluarsa.']);
        }

        // Mark OTP verified in session
        session(['otp_verified' => true]);

        return redirect()->route('password.reset-form')->with('success', 'OTP Terverifikasi. Silakan masukkan password baru Anda.');
    }

    public function showResetPasswordForm()
    {
        if (!session()->has('reset_email') || !session()->has('otp_verified')) {
            return redirect()->route('password.request')->withErrors(['email' => 'Silakan verifikasi OTP terlebih dahulu.']);
        }
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ], [
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal harus 8 karakter.',
        ]);

        $email = session('reset_email');
        
        if (!$email || !session('otp_verified')) {
            return redirect()->route('password.request')->withErrors(['email' => 'Sesi habis, silakan mulai ulang.']);
        }

        $user = User::where('email', $email)->first();
        
        $user->update([
            'password' => Hash::make($request->password),
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        // Clean up session
        session()->forget(['reset_email', 'otp_verified']);

        return redirect()->route('login')->with('success', 'Kata sandi berhasil diperbarui. Silakan login kembali.');
    }
}
