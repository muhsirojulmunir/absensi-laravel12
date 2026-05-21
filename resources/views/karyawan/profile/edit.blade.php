@extends('layouts.master')
@section('title', 'Data Diri')
@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<div class="max-w-4xl mx-auto space-y-8 pb-20 animate-[fadeIn_0.5s_ease-out]">
    <!-- Header Section -->
    <div class="flex items-center justify-between group">
        <div class="transition-transform duration-300 group-hover:translate-x-1">
            <h1 class="text-3xl font-bold text-blue-950 dark:text-white tracking-tight">Portal Biodata Diri <span class="text-blue-500">.</span></h1>
            <p class="text-blue-600/80 dark:text-blue-400 mt-1 font-medium tracking-wide">Kelola informasi pribadi, kontak darurat, dan pas foto Anda.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 px-6 py-4 rounded-2xl text-sm font-bold shadow-sm animate-[fadeIn_0.3s_ease-out]">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-100 text-red-600 px-6 py-4 rounded-2xl text-sm font-bold shadow-sm animate-[fadeIn_0.3s_ease-out] space-y-1">
            <p class="uppercase tracking-widest text-[10px] mb-2">Terjadi Kesalahan Validasi:</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar Profile Summary -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 p-8 rounded-3xl text-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden relative group hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300">
                <div class="absolute inset-0 bg-blue-50/50 dark:bg-blue-900/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="w-32 h-32 bg-slate-50 dark:bg-slate-900 border-4 border-white dark:border-slate-800 shadow-xl rounded-2xl mx-auto flex items-center justify-center text-blue-900 dark:text-blue-100 text-4xl font-black mb-6 overflow-hidden group-hover:scale-105 transition-transform">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover" id="sidebar-avatar">
                        @else
                            <span id="avatar-initial">{{ substr($user->name, 0, 1) }}</span>
                        @endif
                    </div>
                    <h2 class="text-lg font-extrabold text-blue-950 dark:text-white tracking-tight">{{ $user->name }}</h2>
                    <p class="text-blue-500 dark:text-blue-400 text-[10px] font-bold uppercase tracking-widest mt-1">{{ $user->position ?? 'Professional Member' }}</p>
                    <div class="mt-6 pt-6 border-t border-blue-50 dark:border-slate-700">
                        <span class="px-4 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/50 text-[10px] font-black uppercase rounded-lg tracking-[0.2em] shadow-sm">
                            {{ $user->role->name }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 p-8 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] group hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all">
                <h3 class="text-[10px] font-bold text-blue-400 dark:text-blue-500 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full group-hover:scale-150 transition-transform"></span> Informasi Korporat
                </h3>
                <div class="space-y-6">
                    <div class="flex items-center justify-between border-b border-blue-50 dark:border-slate-700 pb-2">
                        <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase">Employee ID</p>
                        <p class="text-xs font-bold text-blue-900 dark:text-blue-100 font-mono bg-blue-50 dark:bg-slate-900 border border-blue-100 dark:border-slate-700 px-3 py-1 rounded-lg">{{ $user->employee_id ?? '-' }}</p>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase">Divisi</p>
                        <p class="text-xs font-bold text-blue-900 dark:text-blue-100">{{ $user->division->name ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Form -->
        <div class="lg:col-span-2">
            <form action="{{ route('karyawan.profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-form" class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden transition-all hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] group/form">
                @csrf
                @method('PUT')
                
                <!-- Hidden input for cropped image -->
                <input type="hidden" name="avatar_cropped" id="avatar_cropped">

                <div class="p-8 space-y-10">
                    <!-- Photo Upload -->
                    <div class="space-y-6">
                        <div class="flex items-center space-x-2">
                            <span class="w-2 h-2 bg-blue-500 rounded-full shadow-[0_0_8px_rgba(59,130,246,0.5)] group-hover/form:scale-125 transition-transform"></span>
                            <h2 class="text-xs font-bold text-blue-900 dark:text-blue-100 uppercase tracking-widest leading-none">Pas Foto Profil</h2>
                        </div>
                        <div class="flex items-start space-x-6">
                            <div class="relative group cursor-pointer w-32 h-40">
                                <div class="w-full h-full bg-slate-50 dark:bg-slate-900 rounded-2xl flex items-center justify-center border-2 border-dashed border-blue-200 dark:border-slate-700 overflow-hidden shadow-sm group-hover:border-blue-400 dark:group-hover:border-blue-500 transition-colors" id="photo-preview-container">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover" id="main-photo-preview">
                                    @else
                                        <svg class="w-12 h-12 text-blue-300 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    @endif
                                </div>
                                <label for="avatar-input" class="absolute inset-0 bg-blue-600/60 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 flex items-center justify-center cursor-pointer transition-all rounded-2xl">
                                    <span class="text-[10px] font-bold text-white uppercase tracking-widest">Ganti Foto</span>
                                </label>
                                <input type="file" id="avatar-input" name="avatar" class="hidden" accept="image/*">
                            </div>
                            <div class="flex-1 space-y-4 pt-4">
                                <p class="text-[11px] text-blue-500 dark:text-blue-400 font-medium leading-relaxed max-w-sm">
                                    Gunakan pas foto formal (Rasio 3x4). Anda dapat mengatur zoom dan me-crop posisi gambar secara persisi setelah memilih file Anda.
                                </p>
                                <div class="flex space-x-2">
                                    <button type="button" onclick="document.getElementById('avatar-input').click()" class="text-[10px] font-bold uppercase tracking-widest text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-5 py-2.5 rounded-xl border border-blue-100 dark:border-blue-900/50 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-all shadow-sm">Pilih File Foto</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Info -->
                    <div class="space-y-6 pt-10 border-t border-blue-50 dark:border-slate-700">
                        <div class="flex items-center space-x-2">
                            <span class="w-2 h-2 bg-indigo-400 rounded-full group-hover/form:scale-125 transition-transform"></span>
                            <h2 class="text-xs font-bold text-blue-900 dark:text-blue-100 uppercase tracking-widest leading-none">Biodata Pribadi</h2>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <label class="block text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ $user->name }}" required 
                                       class="w-full bg-slate-50 dark:bg-slate-900 border border-blue-100 dark:border-slate-700 rounded-xl px-4 py-3.5 text-blue-950 dark:text-blue-100 text-sm font-bold focus:ring-2 focus:ring-blue-600 outline-none transition-all placeholder-slate-400 dark:placeholder-slate-600 shadow-inner focus:bg-white dark:focus:bg-slate-950 text-shadow-sm">
                            </div>
                            <div class="space-y-3">
                                <label class="block text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Nomer Telepon</label>
                                <input type="text" name="phone" value="{{ $user->phone }}" 
                                       class="w-full bg-slate-50 dark:bg-slate-900 border border-blue-100 dark:border-slate-700 rounded-xl px-4 py-3.5 text-blue-950 dark:text-blue-100 text-sm font-bold focus:ring-2 focus:ring-blue-600 outline-none transition-all placeholder-slate-400 dark:placeholder-slate-600 shadow-inner focus:bg-white dark:focus:bg-slate-950 text-shadow-sm">
                            </div>
                        </div>
                        <div class="space-y-3">
                            <label class="block text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Alamat Email <span class="text-slate-400 normal-case font-normal">(Digunakan untuk Lupa Password)</span></label>
                            <input type="email" name="email" value="{{ $user->email }}" placeholder="contoh: karyawan@perusahaan.com"
                                   class="w-full bg-slate-50 dark:bg-slate-900 border border-blue-100 dark:border-slate-700 rounded-xl px-4 py-3.5 text-blue-950 dark:text-blue-100 text-sm font-bold focus:ring-2 focus:ring-blue-600 outline-none transition-all placeholder-slate-400 dark:placeholder-slate-600 shadow-inner focus:bg-white dark:focus:bg-slate-950 text-shadow-sm">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <label class="block text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Tempat Lahir</label>
                                <input type="text" name="birth_place" value="{{ $user->birth_place }}" placeholder="Contoh: Jakarta"
                                       class="w-full bg-slate-50 dark:bg-slate-900 border border-blue-100 dark:border-slate-700 rounded-xl px-4 py-3.5 text-blue-950 dark:text-blue-100 text-sm font-bold focus:ring-2 focus:ring-blue-600 outline-none transition-all placeholder-slate-400 dark:placeholder-slate-600 shadow-inner focus:bg-white dark:focus:bg-slate-950 text-shadow-sm">
                            </div>
                            <div class="space-y-3">
                                <label class="block text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Tanggal Lahir</label>
                                <input type="date" name="birth_date" value="{{ $user->birth_date }}" 
                                       class="w-full bg-slate-50 dark:bg-slate-900 border border-blue-100 dark:border-slate-700 rounded-xl px-4 py-3.5 text-blue-950 dark:text-blue-100 text-sm font-bold focus:ring-2 focus:ring-blue-600 outline-none transition-all shadow-inner focus:bg-white dark:focus:bg-slate-950 text-shadow-sm">
                            </div>
                        </div>
                        <div class="space-y-3">
                            <label class="block text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Alamat Lengkap Tempat Tinggal</label>
                            <textarea name="address" rows="3" class="w-full bg-slate-50 dark:bg-slate-900 border border-blue-100 dark:border-slate-700 rounded-2xl px-5 py-4 text-blue-950 dark:text-blue-100 text-sm font-bold focus:ring-2 focus:ring-blue-600 outline-none transition-all placeholder-slate-400 dark:placeholder-slate-600 shadow-inner focus:bg-white dark:focus:bg-slate-950 text-shadow-sm">{{ $user->address }}</textarea>
                        </div>
                    </div>

                    <!-- Emergency Contact -->
                    <div class="space-y-6 pt-10 border-t border-blue-50 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="w-2 h-2 bg-amber-500 rounded-full shadow-[0_0_8px_rgba(245,158,11,0.5)] group-hover/form:scale-125 transition-transform"></span>
                                <h2 class="text-xs font-bold text-blue-900 dark:text-blue-100 uppercase tracking-widest leading-none">Kontak Darurat (Terkait)</h2>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <label class="block text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Nama Orang Kesayangan/Keluarga</label>
                                <input type="text" name="emergency_name" value="{{ $user->emergency_name }}" placeholder="Contoh: Siti Aminah"
                                       class="w-full bg-slate-50 dark:bg-slate-900 border border-blue-100 dark:border-slate-700 rounded-xl px-4 py-3.5 text-blue-950 dark:text-blue-100 text-sm font-bold focus:ring-2 focus:ring-amber-500 outline-none transition-all placeholder-slate-400 dark:placeholder-slate-600 shadow-inner focus:bg-white dark:focus:bg-slate-950 text-shadow-sm">
                            </div>
                            <div class="space-y-3">
                                <label class="block text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Status Hubungan</label>
                                <select name="emergency_relation" class="w-full bg-slate-50 dark:bg-slate-900 border border-blue-100 dark:border-slate-700 rounded-xl px-4 py-3.5 text-blue-950 dark:text-blue-100 text-sm font-bold focus:ring-2 focus:ring-amber-500 outline-none transition-all shadow-inner focus:bg-white dark:focus:bg-slate-950 text-shadow-sm">
                                    <option value="" disabled {{ !$user->emergency_relation ? 'selected' : '' }} class="dark:bg-slate-900">-- Pilih Hubungan Darurat --</option>
                                    <option value="Orang tua (Ibu/Ayah)" {{ $user->emergency_relation == 'Orang tua (Ibu/Ayah)' ? 'selected' : '' }} class="dark:bg-slate-900">Orang tua (Ibu/Ayah)</option>
                                    <option value="Pasangan (Suami/Istri)" {{ $user->emergency_relation == 'Pasangan (Suami/Istri)' ? 'selected' : '' }} class="dark:bg-slate-900">Pasangan (Suami/Istri)</option>
                                    <option value="Saudara Kandung (Kakak/Adik)" {{ $user->emergency_relation == 'Saudara Kandung (Kakak/Adik)' ? 'selected' : '' }} class="dark:bg-slate-900">Saudara Kandung (Kakak/Adik)</option>
                                    <option value="Teman Dekat / Tetangga" {{ $user->emergency_relation == 'Teman Dekat / Tetangga' ? 'selected' : '' }} class="dark:bg-slate-900">Teman Dekat / Tetangga</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                             <div class="space-y-3">
                                <label class="block text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Nomer HP Darurat / Whatsapp</label>
                                <input type="text" name="emergency_phone" value="{{ $user->emergency_phone }}" 
                                       class="w-full bg-slate-50 dark:bg-slate-900 border border-blue-100 dark:border-slate-700 rounded-xl px-4 py-3.5 text-blue-950 dark:text-blue-100 text-sm font-bold focus:ring-2 focus:ring-amber-500 outline-none transition-all placeholder-slate-400 dark:placeholder-slate-600 shadow-inner focus:bg-white dark:focus:bg-slate-950 text-shadow-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Security -->
                    <div class="space-y-6 pt-10 border-t border-blue-50 dark:border-slate-700">
                        <div class="flex items-center space-x-2">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full shadow-[0_0_8px_rgba(16,185,129,0.5)] group-hover/form:scale-125 transition-transform"></span>
                            <h2 class="text-xs font-bold text-blue-900 dark:text-blue-100 uppercase tracking-widest leading-none">Data Akses & Keamanan</h2>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <label class="block text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Atur Kata Sandi Baru</label>
                                <input type="password" name="password" class="w-full bg-slate-50 dark:bg-slate-900 border border-blue-100 dark:border-slate-700 rounded-xl px-4 py-3.5 text-blue-950 dark:text-blue-100 text-sm font-bold focus:ring-2 focus:ring-emerald-500 outline-none transition-all placeholder-slate-400 dark:placeholder-slate-600 shadow-inner focus:bg-white dark:focus:bg-slate-950 text-shadow-sm" placeholder="••••••••">
                            </div>
                            <div class="space-y-3">
                                <label class="block text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="w-full bg-slate-50 dark:bg-slate-900 border border-blue-100 dark:border-slate-700 rounded-xl px-4 py-3.5 text-blue-950 dark:text-blue-100 text-sm font-bold focus:ring-2 focus:ring-emerald-500 outline-none transition-all placeholder-slate-400 dark:placeholder-slate-600 shadow-inner focus:bg-white dark:focus:bg-slate-950 text-shadow-sm" placeholder="••••••••">
                            </div>
                        </div>
                        <p class="text-[9px] text-blue-400 font-bold uppercase tracking-wider italic">* Kosongkan kolom jika Anda tidak ingin mengatur ulang (Reset) kata sandi.</p>
                    </div>
                </div>

                <div class="bg-blue-50/50 dark:bg-slate-900/50 px-8 py-6 border-t border-blue-100 dark:border-slate-700 text-right flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-10 py-3.5 rounded-xl font-bold shadow-[0_8px_30px_rgb(37,99,235,0.2)] transition-all active:scale-[0.98] flex items-center space-x-2 hover:-translate-y-1 hover:shadow-[0_8px_30px_rgb(37,99,235,0.4)]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        <span class="uppercase tracking-widest text-xs">Simpan Pembaruan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cropping Modal -->
<div id="cropper-modal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-blue-950/90 p-2 md:p-4 backdrop-blur-sm animate-in fade-in duration-300">
    <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 w-full max-w-lg md:max-w-2xl rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300 flex flex-col max-h-[95vh]">
        <div class="px-6 md:px-8 py-4 md:py-6 border-b border-blue-50 dark:border-slate-700 flex items-center justify-between shrink-0 bg-blue-50/30 dark:bg-slate-900/50">
            <h3 class="text-xs md:text-sm font-bold text-blue-950 dark:text-white uppercase tracking-widest">Crop Pas Foto 3x4</h3>
            <button onclick="closeCropper()" class="text-blue-400 hover:text-blue-600 dark:text-blue-500 dark:hover:text-blue-300 transition-colors bg-white dark:bg-slate-900 p-1 rounded-lg border border-blue-100 dark:border-slate-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-4 md:p-8 overflow-y-auto flex-1 flex flex-col">
            <div class="bg-slate-50 dark:bg-slate-900 border border-blue-100 dark:border-slate-700 rounded-2xl overflow-hidden relative flex-1 min-h-[300px] md:min-h-[400px] flex items-center justify-center shadow-inner">
                <img id="cropper-image" class="max-w-full">
            </div>
            <div class="mt-6 md:mt-8 space-y-6 shrink-0">
                <div class="flex items-center space-x-4 bg-blue-50 dark:bg-slate-900 rounded-2xl px-5 py-4 border border-blue-100 dark:border-slate-700">
                    <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase shrink-0">Bongkar Zoom</span>
                    <input type="range" id="zoom-range" min="0" max="3" step="0.1" value="1" class="flex-1 accent-blue-600 bg-white dark:bg-slate-800 h-2 rounded-lg appearance-none cursor-pointer border border-blue-100 dark:border-slate-700 shadow-inner">
                </div>
                <div class="flex flex-col md:flex-row gap-3 md:gap-4 pt-2">
                    <button type="button" onclick="closeCropper()" class="order-2 md:order-1 flex-1 px-6 py-3.5 md:py-3 rounded-xl text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 hover:text-blue-900 dark:hover:text-white border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-900 transition-all font-mono shadow-sm">Batalkan</button>
                    <button type="button" id="btn-confirm-crop" onclick="confirmCrop()" class="order-1 md:order-2 flex-[2] bg-blue-600 hover:bg-blue-500 text-white px-8 py-3.5 md:py-3 rounded-xl font-bold uppercase tracking-widest text-xs shadow-lg shadow-blue-600/20 transition-all flex items-center justify-center space-x-2">
                        <span id="btn-text">Ya, Terapkan Sekarang</span>
                        <div id="btn-loader" class="hidden w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin"></div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Toast -->
<div id="toast" class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[200] hidden animate-in slide-in-from-bottom-8 duration-300">
    <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 px-6 py-3 rounded-2xl shadow-xl flex items-center space-x-3">
        <div id="toast-icon" class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-sm shadow-blue-300"></div>
        <p id="toast-message" class="text-xs font-bold text-blue-950 dark:text-white uppercase tracking-wider"></p>
    </div>
</div>

<script>
let cropper;
const avatarInput = document.getElementById('avatar-input');
const cropperModal = document.getElementById('cropper-modal');
const cropperImage = document.getElementById('cropper-image');
const zoomRange = document.getElementById('zoom-range');
const toast = document.getElementById('toast');
const toastMessage = document.getElementById('toast-message');
const toastIcon = document.getElementById('toast-icon');

function showToast(message, type = 'info') {
    toastMessage.textContent = message;
    toastIcon.className = `w-2.5 h-2.5 rounded-full shadow-sm ${type === 'error' ? 'bg-red-500 shadow-red-300' : 'bg-blue-500 shadow-blue-300'}`;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3000);
}

avatarInput.addEventListener('change', function(e) {
    const files = e.target.files;
    if (files && files.length > 0) {
        const file = files[0];
        
        if (!file.type.match('image.*')) {
            showToast('File harus berupa gambar!', 'error');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(event) {
            cropperImage.src = event.target.result;
            cropperModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; 
            
            if (cropper) {
                cropper.destroy();
            }
            
            cropper = new Cropper(cropperImage, {
                aspectRatio: 3 / 4,
                viewMode: 1, 
                dragMode: 'move',
                autoCropArea: 0.8,
                restore: false,
                guides: true,
                center: true,
                highlight: true,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                minContainerWidth: 200,
                minContainerHeight: 300,
                ready() {
                    zoomRange.value = cropper.getData().scaleX || 1;
                }
            });
        };
        reader.onerror = () => showToast('Gagal membaca file gambar.', 'error');
        reader.readAsDataURL(file);
    }
});

function confirmCrop() {
    if (!cropper) return;

    const btn = document.getElementById('btn-confirm-crop');
    const btnText = document.getElementById('btn-text');
    const btnLoader = document.getElementById('btn-loader');

    try {
        btn.disabled = true;
        btnText.textContent = 'Memotong...';
        btnLoader.classList.remove('hidden');

        setTimeout(() => {
            try {
                const canvas = cropper.getCroppedCanvas({
                    width: 600,
                    height: 800,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });

                if (!canvas) {
                    throw new Error('Gagal re-render kanvas visual foto.');
                }

                const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.9);
                if (!croppedDataUrl || croppedDataUrl === 'data:,') {
                    throw new Error('Hasil re-render foto diproses terlanjut putus.');
                }

                document.getElementById('avatar_cropped').value = croppedDataUrl;
                
                // Clear the original file input to save bandwidth and avoid validation issues with non-standard formats (HEIC)
                document.getElementById('avatar-input').value = '';

                // Update Main Photo Preview (Fix for null error)
                const mainPhotoPreview = document.getElementById('main-photo-preview');
                if (mainPhotoPreview) {
                    mainPhotoPreview.src = croppedDataUrl;
                } else {
                    const container = document.getElementById('photo-preview-container');
                    if (container) {
                        container.innerHTML = `<img src="${croppedDataUrl}" class="w-full h-full object-cover" id="main-photo-preview">`;
                    }
                }
                
                const sidebarAvatar = document.getElementById('sidebar-avatar');
                if (sidebarAvatar) sidebarAvatar.src = croppedDataUrl;
                else {
                    const sbInit = document.getElementById('avatar-initial');
                    if(sbInit) {
                        const newImg = document.createElement('img');
                        newImg.src = croppedDataUrl;
                        newImg.id = 'sidebar-avatar';
                        newImg.className = 'w-full h-full object-cover';
                        sbInit.replaceWith(newImg);
                    }
                }

                showToast('Ukuran foto profil baru diterapkan!');
                closeCropper();
            } catch (err) {
                console.error(err);
                showToast(err.message || 'Server error preview cache.', 'error');
            } finally {
                btn.disabled = false;
                btnText.textContent = 'Ya, Terapkan Sekarang';
                btnLoader.classList.add('hidden');
            }
        }, 150);

    } catch (err) {
        showToast('Koneksi terputus: ' + err.message, 'error');
        btn.disabled = false;
    }
}

function closeCropper() {
    cropperModal.classList.add('hidden');
    document.body.style.overflow = '';
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
    avatarInput.value = '';
}

zoomRange.addEventListener('input', function() {
    if (cropper) {
        cropper.zoomTo(this.value);
    }
});
</script>
<style>
    /* Styling modern cropper library overides agar tema biru white-mode match */
    .cropper-line { background-color: #3b82f6 !important; opacity: 0.8 !important; }
    .cropper-point { background-color: #2563eb !important; }
    .cropper-view-box { outline-color: #3b82f6 !important; outline-width: 2px !important; border-radius: 8px;}
</style>
@endsection
