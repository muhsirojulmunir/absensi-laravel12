<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Pastikan folder avatars selalu ada sebelum menyimpan file.
     */
    private function ensureAvatarsDirectoryExists(): void
    {
        $avatarsDir = public_path('storage/avatars');
        if (!is_dir($avatarsDir)) {
            mkdir($avatarsDir, 0775, true);
        }
    }

    public function edit()
    {
        $user = Auth::user();
        return view('karyawan.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name'               => 'required|string|max:255',
            'email'              => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'phone'              => 'nullable|string|max:20',
            'emergency_name'     => 'nullable|string|max:255',
            'emergency_phone'    => 'nullable|string|max:20',
            'emergency_relation' => 'nullable|string|max:100',
            'address'            => 'nullable|string|max:500',
            'birth_place'        => 'nullable|string|max:255',
            'birth_date'         => 'nullable|date',
            'password'           => 'nullable|string|min:8|confirmed',
        ];

        if (!$request->filled('avatar_cropped')) {
            $rules['avatar'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240';
        } else {
            $rules['avatar'] = 'nullable|max:15360';
        }

        $request->validate($rules, [
            'avatar.image'      => 'File yang diunggah harus berupa gambar.',
            'avatar.mimes'      => 'Format gambar harus jpeg, png, jpg, atau gif.',
            'avatar.max'        => 'Ukuran file tidak boleh lebih dari 10MB.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min'      => 'Kata sandi minimal 8 karakter.',
        ]);

        $data = [
            'name'               => $request->name,
            'email'              => $request->email,
            'phone'              => $request->phone,
            'emergency_name'     => $request->emergency_name,
            'emergency_phone'    => $request->emergency_phone,
            'emergency_relation' => $request->emergency_relation,
            'address'            => $request->address,
            'birth_place'        => $request->birth_place,
            'birth_date'         => $request->birth_date,
        ];

        if ($request->filled('delete_avatar') && $request->delete_avatar == '1') {
            // Hapus file lama dari disk
            if ($user->avatar) {
                $oldPath = public_path('storage/' . $user->avatar);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
                // Fallback: hapus juga dari storage/app/public jika masih ada
                if (Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
            }
            $data['avatar'] = null;

        } elseif ($request->filled('avatar_cropped')) {
            // Hapus avatar lama
            if ($user->avatar) {
                $oldPath = public_path('storage/' . $user->avatar);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
                if (Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
            }

            $imageData = $request->input('avatar_cropped');
            $fileName  = 'avatars/' . uniqid() . '.jpg';

            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $ext       = strtolower($type[1]);

                if (!in_array($ext, ['jpg', 'jpeg', 'gif', 'png'])) {
                    return back()->withErrors(['avatar' => 'Format gambar tidak didukung.']);
                }

                $imageData = base64_decode($imageData);

                if ($imageData === false) {
                    return back()->withErrors(['avatar' => 'Gagal mendekode data gambar.']);
                }
            } else {
                return back()->withErrors(['avatar' => 'Format data gambar tidak valid.']);
            }

            // Pastikan folder ada lalu simpan langsung ke public/storage/avatars/
            $this->ensureAvatarsDirectoryExists();
            $filePath = public_path('storage/' . $fileName);
            file_put_contents($filePath, $imageData);

            $data['avatar'] = $fileName;

        } elseif ($request->hasFile('avatar')) {
            // Fallback upload biasa
            if ($user->avatar) {
                $oldPath = public_path('storage/' . $user->avatar);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
                if (Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
            }

            $this->ensureAvatarsDirectoryExists();
            $file         = $request->file('avatar');
            $newFileName  = 'avatars/' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/avatars'), basename($newFileName));

            $data['avatar'] = $newFileName;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function destroyAvatar(Request $request)
    {
        $user = Auth::user();

        if ($user->avatar) {
            // Hapus dari public/storage (hosting tanpa symlink)
            $physicalPath = public_path('storage/' . $user->avatar);
            if (file_exists($physicalPath)) {
                @unlink($physicalPath);
            }
            // Fallback: hapus juga dari storage/app/public
            if (Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
        }

        $user->update(['avatar' => null]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Foto profil berhasil dihapus.']);
        }

        return redirect()->back()->with('success', 'Foto profil berhasil dihapus.');
    }
}
