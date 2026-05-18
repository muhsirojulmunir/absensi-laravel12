<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('karyawan.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'emergency_name' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
            'emergency_relation' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'birth_place' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'password' => 'nullable|string|min:8|confirmed',
        ];

        // If no cropped image is provided, strictly validate the file input as an image
        if (!$request->filled('avatar_cropped')) {
            $rules['avatar'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240';
        } else {
            // If cropped image is provided, we allow the original file to pass through (it might be HEIC)
            // as we only use the base64 cropped version anyway.
            $rules['avatar'] = 'nullable|max:15360'; 
        }

        $request->validate($rules, [
            'avatar.image' => 'File yang diunggah harus berupa gambar.',
            'avatar.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif.',
            'avatar.max' => 'Ukuran file tidak boleh lebih dari 10MB.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        $data = [
            'name' => $request->name,
            'phone' => $request->phone,
            'emergency_name' => $request->emergency_name,
            'emergency_phone' => $request->emergency_phone,
            'emergency_relation' => $request->emergency_relation,
            'address' => $request->address,
            'birth_place' => $request->birth_place,
            'birth_date' => $request->birth_date,
        ];

        if ($request->filled('avatar_cropped')) {
            // Delete old avatar if exists
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }

            $imageData = $request->input('avatar_cropped');
            $fileName = 'avatars/' . uniqid() . '.jpg';
            
            // Handle various base64 image formats (data:image/jpeg;base64, data:image/png;base64, etc.)
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, gif

                if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                    return back()->withErrors(['avatar' => 'Format gambar tidak didukung.']);
                }

                $imageData = base64_decode($imageData);

                if ($imageData === false) {
                    return back()->withErrors(['avatar' => 'Gagal mendekode data gambar.']);
                }
            } else {
                return back()->withErrors(['avatar' => 'Format data gambar tidak valid.']);
            }
            
            \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $imageData);
            $data['avatar'] = $fileName;
        } elseif ($request->hasFile('avatar')) {
            // Fallback for regular upload if cropper not used
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('karyawan.profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}
