@extends('layouts.master')
@section('title', 'Edit User')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('super-admin.users.index') }}" 
           class="p-2.5 bg-white dark:bg-slate-900 rounded-xl shadow-xs border border-slate-200/60 dark:border-slate-800 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-100 dark:hover:border-indigo-900/50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Edit Pengguna: {{ $user->name }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Perbarui informasi akun dan hak akses karyawan.</p>
        </div>
    </div>

    <!-- Error Alerts -->
    @if($errors->any())
        <div class="bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/50 text-rose-800 dark:text-rose-400 px-6 py-4 rounded-2xl text-sm shadow-xs animate-fade-in">
            <div class="flex items-center mb-2 font-semibold">
                <svg class="w-5 h-5 mr-2 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path>
                </svg>
                <span>Selesaikan beberapa error berikut:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 ml-7 font-medium text-rose-700 dark:text-rose-350">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('super-admin.users.update', $user) }}" method="POST"
          class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-800 overflow-hidden">
        @csrf
        @method('PUT')
        
        <div class="p-6 sm:p-8 space-y-8">
            <!-- Profile Preview Section -->
            <div class="flex items-center space-x-6 pb-6 border-b border-slate-100 dark:border-slate-800/60">
                <div class="w-20 h-20 bg-slate-50 dark:bg-slate-950 rounded-2xl flex items-center justify-center text-indigo-900 dark:text-indigo-150 text-3xl font-black border-2 border-white dark:border-slate-800 shadow-md overflow-hidden shrink-0">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover">
                    @else
                        <span>{{ substr($user->name, 0, 1) }}</span>
                    @endif
                </div>
                <div>
                    <p class="text-[10px] font-black text-indigo-500 dark:text-indigo-400 uppercase tracking-widest leading-none mb-2">Avatar Saat Ini</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
                        Avatar dapat diperbarui oleh karyawan secara mandiri melalui portal biodata mereka.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Left Side: Basic Info -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-2 pb-2 border-b border-slate-100 dark:border-slate-800/60">
                        <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                        <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Informasi Dasar</h2>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Nama Pengguna (Username)</label>
                            <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Alamat Email (Opsional)</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Kata Sandi (Password)</label>
                            <div class="relative">
                                <input type="password" id="password" name="password"
                                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl pl-4 pr-12 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all"
                                       placeholder="Biarkan kosong jika tidak ingin mengubah">
                                <button type="button" 
                                        onclick="const p=document.getElementById('password'); const isPass=p.type==='password'; p.type=isPass?'text':'password'; this.children[0].classList.toggle('hidden', isPass); this.children[1].classList.toggle('hidden', !isPass);" 
                                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors focus:outline-none">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Workplace Info -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-2 pb-2 border-b border-slate-100 dark:border-slate-800/60">
                        <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                        <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Detail Pekerjaan</h2>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">ID Karyawan</label>
                            <input type="text" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}" readonly
                                   class="w-full bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-850 rounded-xl px-4 py-3 text-sm text-slate-500 dark:text-slate-400 font-mono font-bold outline-none cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Peran (Role)</label>
                            <select name="role_id" required
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all cursor-pointer">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" data-slug="{{ $role->slug }}" {{ $user->role_id == $role->id ? 'selected' : '' }}
                                            class="dark:bg-slate-950">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Divisi</label>
                            <select name="division_id"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all cursor-pointer">
                                <option value="" class="dark:bg-slate-950">Pilih Divisi</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ $user->division_id == $division->id ? 'selected' : '' }} class="dark:bg-slate-950">{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="location_wrapper" style="display: {{ $user->role->slug === 'karyawan_ramayana' ? 'block' : 'none' }};" class="animate-fade-in space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Lokasi Absen Utama (Khusus Ramayana)</label>
                                <select name="location_id" id="location_id" 
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all cursor-pointer">
                                    <option value="" class="dark:bg-slate-950">Pilih Lokasi Utama</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ $user->location_id == $location->id ? 'selected' : '' }} class="dark:bg-slate-950">{{ $location->name }} (Radius: {{ $location->radius }}m)</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 pl-0.5">Lokasi Absen Tambahan (Opsional)</label>
                                <div id="additional_locations_container" class="space-y-3">
                                    <!-- Dropdown tambahan dinamis masuk di sini -->
                                </div>
                                <button type="button" id="btn_add_location" class="inline-flex items-center space-x-1.5 mt-1 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-all cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                    <span>Tambah Lokasi (+)</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Section: Additional Info -->
            <div class="space-y-6 pt-6 border-t border-slate-100 dark:border-slate-800/60">
                <div class="flex items-center space-x-2 pb-2">
                    <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                    <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Informasi Tambahan</h2>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Jabatan (Posisi)</label>
                            <input type="text" name="position" value="{{ old('position', $user->position) }}"
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Nomor Telepon</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Tempat Lahir</label>
                            <input type="text" name="birth_place" value="{{ old('birth_place', $user->birth_place) }}" 
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all" 
                                   placeholder="Jakarta">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Tanggal Lahir</label>
                            <input type="date" name="birth_date" value="{{ old('birth_date', $user->birth_date) }}" 
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all dark:[color-scheme:dark]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 pl-0.5">Alamat Rumah</label>
                        <textarea name="address" rows="3" 
                                  class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all resize-none">{{ old('address', $user->address) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Footer Actions -->
        <div class="bg-slate-50 dark:bg-slate-950/40 px-6 py-5 flex justify-end items-center space-x-3 border-t border-slate-100 dark:border-slate-800/80">
            <a href="{{ route('super-admin.users.index') }}" 
               class="px-5 py-2.5 text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">Batal</a>
            <button type="submit" 
                    class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-2.5 rounded-xl font-bold shadow-md shadow-indigo-900/10 hover:shadow-lg transition-all active:scale-[0.98]">
                Simpan Pembaruan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const btnAddLocation = document.getElementById('btn_add_location');
    const additionalContainer = document.getElementById('additional_locations_container');
    const locationsData = @js($locations);
    const existingAdditionalIds = @js($user->additional_location_ids ?? []);

    function addLocationRow(selectedId = '') {
        const row = document.createElement('div');
        row.className = 'flex items-center space-x-3 animate-fade-in';
        
        let optionsHtml = '<option value="">Pilih Lokasi Tambahan</option>';
        locationsData.forEach(loc => {
            const selected = loc.id == selectedId ? 'selected' : '';
            optionsHtml += `<option value="${loc.id}" ${selected}>${loc.name} (Radius: ${loc.radius}m)</option>`;
        });

        row.innerHTML = `
            <div class="flex-1">
                <select name="additional_location_ids[]" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition-all focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 cursor-pointer">
                    ${optionsHtml}
                </select>
            </div>
            <button type="button" class="btn-remove-location p-3 bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/40 rounded-xl transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        `;
        
        row.querySelector('.btn-remove-location').addEventListener('click', function() {
            row.remove();
        });

        additionalContainer.appendChild(row);
    }

    // Populate existing additional locations on load
    if (Array.isArray(existingAdditionalIds)) {
        existingAdditionalIds.forEach(id => {
            addLocationRow(id);
        });
    }

    btnAddLocation.addEventListener('click', () => {
        addLocationRow();
    });

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
                additionalContainer.innerHTML = '';
            }
        });
    }
</script>
@endpush