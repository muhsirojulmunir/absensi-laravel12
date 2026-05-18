<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            Auth::user()->update(['last_login_at' => now()]);

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
}
