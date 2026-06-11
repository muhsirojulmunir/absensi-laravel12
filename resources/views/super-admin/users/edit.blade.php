@extends('layouts.master')
@section('title', 'Edit User')
@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center space-x-3">
            <a href="{{ route('super-admin.users.index') }}"
                class="p-2 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 text-gray-400 dark:text-blue-400 hover:text-navy-900 dark:hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-navy-900 dark:text-white">Edit Pengguna: {{ $user->name }}</h1>
        </div>

        <form action="{{ route('super-admin.users.update', $user) }}" method="POST"
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
            @csrf
            @method('PUT')
            <div class="p-8 space-y-6">
                <!-- Profile Preview Section -->
                <div class="flex items-center space-x-6 pb-6 border-b border-gray-50 dark:border-slate-700">
                    <div
                        class="w-20 h-20 bg-slate-50 dark:bg-slate-900 rounded-2xl flex items-center justify-center text-blue-900 dark:text-blue-100 text-3xl font-black border-2 border-white dark:border-slate-700 shadow-lg overflow-hidden">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover">
                        @else
                            <span>{{ substr($user->name, 0, 1) }}</span>
                        @endif
                    </div>
                    <div>
                        <p
                            class="text-[10px] font-black text-blue-400 dark:text-blue-500 uppercase tracking-widest leading-none mb-2">
                            Avatar Saat Ini</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Avatar dapat diperbarui oleh
                            karyawan melalui portal biodata mandiri.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Info -->
                    <div class="space-y-4">
                        <h2 class="text-sm font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Informasi
                            Dasar</h2>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Nama
                                Lengkap</label>
                            <input type="text" name="name" value="{{ $user->name }}" required
                                class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Nama Pengguna
                                (Username)</label>
                            <input type="text" name="username" value="{{ $user->username }}" required
                                class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Alamat Email
                                (Opsional)</label>
                            <input type="email" name="email" value="{{ $user->email }}"
                                class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Kata Sandi
                                (Password)</label>
                            <div class="relative">
                                <input type="password" id="password" name="password"
                                    class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white"
                                    placeholder="Biarkan kosong jika tidak ingin mengubah">
                                <button type="button" onclick="const p=document.getElementById('password'); const isPass=p.type==='password'; p.type=isPass?'text':'password'; this.children[0].style.display=isPass?'none':'block'; this.children[1].style.display=isPass?'block':'none';" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    <svg class="h-5 w-5" style="display: none;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Workplace Info -->
                    <div class="space-y-4">
                        <h2 class="text-sm font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Detail
                            Pekerjaan</h2>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">ID
                                Karyawan</label>
                            <input type="text" name="employee_id" value="{{ $user->employee_id }}"
                                class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Peran
                                (Role)</label>
                            <select name="role_id" required
                                class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white cursor-pointer">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" data-slug="{{ $role->slug }}" {{ $user->role_id == $role->id ? 'selected' : '' }}
                                        class="dark:bg-slate-900">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Divisi</label>
                            <select name="division_id"
                                class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white cursor-pointer">
                                <option value="" class="dark:bg-slate-900">Pilih Divisi</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ $user->division_id == $division->id ? 'selected' : '' }} class="dark:bg-slate-900">{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="location_wrapper" style="display: {{ $user->role->slug === 'karyawan_ramayana' ? 'block' : 'none' }};">
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Lokasi Counter (Khusus Ramayana)</label>
                            <select name="location_id" id="location_id" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white cursor-pointer">
                                <option value="" class="dark:bg-slate-900">Pilih Lokasi Counter</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ $user->location_id == $location->id ? 'selected' : '' }} class="dark:bg-slate-900">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="space-y-4 pt-4 border-t border-gray-50 dark:border-slate-700">
                    <h2 class="text-sm font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Informasi
                        Tambahan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Jabatan
                                (Posisi)</label>
                            <input type="text" name="position" value="{{ $user->position }}"
                                class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Nomor
                                Telepon</label>
                            <input type="text" name="phone" value="{{ $user->phone }}"
                                class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Tempat Lahir</label>
                            <input type="text" name="birth_place" value="{{ $user->birth_place }}" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white" placeholder="Jakarta">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Tanggal Lahir</label>
                            <input type="date" name="birth_date" value="{{ $user->birth_date }}" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white dark:[color-scheme:dark]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Alamat Rumah</label>
                        <textarea name="address" rows="3"
                            class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-900 dark:text-white">{{ $user->address }}</textarea>
                    </div>
                </div>
            </div>

            <div
                class="bg-gray-50 dark:bg-slate-900/50 p-6 flex justify-end space-x-3 border-t border-gray-100 dark:border-slate-700">
                <a href="{{ route('super-admin.users.index') }}"
                    class="px-6 py-2.5 text-sm font-bold text-gray-500 dark:text-slate-400 hover:text-navy-900 dark:hover:text-white transition-colors">Batal</a>
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-500 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-900/20 transition-all active:scale-[0.98]">
                    Simpan Pembaruan
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    const roleSelect = document.querySelector('select[name="role_id"]');
    if(roleSelect) {
        roleSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const roleSlug = selectedOption ? selectedOption.getAttribute('data-slug') : '';
            const locationWrapper = document.getElementById('location_wrapper');
            
            if (roleSlug === 'karyawan_ramayana') {
                locationWrapper.style.display = 'block';
            } else {
                locationWrapper.style.display = 'none';
                document.getElementById('location_id').value = '';
            }
        });
    }
</script>
@endpush