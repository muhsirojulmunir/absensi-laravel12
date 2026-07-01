<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\BirthdayGreeting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BirthdayGreetingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'birthday_user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:500',
        ]);

        // Verifikasi bahwa user yang diberi ucapan memang berulang tahun hari ini
        $birthdayUser = User::findOrFail($request->birthday_user_id);
        
        if (!$birthdayUser->birth_date) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna ini tidak memiliki data tanggal lahir.'
            ], 422);
        }

        $birthDate = \Carbon\Carbon::parse($birthdayUser->birth_date);
        $today = now();

        if ($birthDate->month !== $today->month || $birthDate->day !== $today->day) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna ini tidak berulang tahun hari ini.'
            ], 422);
        }

        $greeting = BirthdayGreeting::create([
            'birthday_user_id' => $birthdayUser->id,
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'year' => $today->year,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ucapan selamat ulang tahun berhasil dikirim!',
            'data' => [
                'id' => $greeting->id,
                'sender_name' => Auth::user()->name,
                'sender_avatar' => Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : null,
                'sender_initial' => substr(Auth::user()->name, 0, 1),
                'message' => $greeting->message,
                'created_at' => $greeting->created_at->diffForHumans(),
            ]
        ]);
    }
}
