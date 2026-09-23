@extends('layouts.master')
@section('title', isset($contract) ? 'Edit Kontrak Kerja' : 'Buat Kontrak Kerja')
@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('super-admin.contracts.index') }}"
           class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
                {{ isset($contract) ? 'Edit Kontrak Kerja' : 'Buat Kontrak Kerja Baru' }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Isi data karyawan dan periode kontrak</p>
        </div>
    </div>

    <form method="POST" action="{{ isset($contract) ? route('super-admin.contracts.update', $contract) : route('super-admin.contracts.store') }}"
          class="space-y-6">
        @csrf
        @if(isset($contract)) @method('PUT') @endif

        @if(isset($errors) && $errors->any())
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Nomor & Karyawan --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-5">Informasi Kontrak</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nomor Kontrak <span class="text-red-500">*</span></label>
                    <input type="text" name="contract_number" value="{{ old('contract_number', $contract->contract_number ?? $nextNumber ?? '') }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Link ke Akun Pengguna (opsional)</label>
                    <select name="user_id" class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Tidak dilink --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $contract->user_id ?? '') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->role->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Data Karyawan --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-5">Data Karyawan (Pihak Kedua)</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="employee_name" value="{{ old('employee_name', $contract->employee_name ?? '') }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">NIK</label>
                    <input type="text" name="employee_nik" value="{{ old('employee_nik', $contract->employee_nik ?? '') }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">No. HP / WhatsApp</label>
                    <input type="text" name="employee_phone" value="{{ old('employee_phone', $contract->employee_phone ?? '') }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tempat Lahir</label>
                    <input type="text" name="employee_birth_place" value="{{ old('employee_birth_place', $contract->employee_birth_place ?? '') }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tanggal Lahir</label>
                    <input type="date" name="employee_birth_date" value="{{ old('employee_birth_date', isset($contract->employee_birth_date) ? $contract->employee_birth_date->format('Y-m-d') : '') }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Alamat Lengkap</label>
                    <textarea name="employee_address" rows="3"
                              class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('employee_address', $contract->employee_address ?? '') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Jabatan / Posisi <span class="text-red-500">*</span></label>
                    <input type="text" name="employee_position" value="{{ old('employee_position', $contract->employee_position ?? '') }}"
                           placeholder="cth: Social Media Specialist"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>
        </div>

        {{-- Periode Kontrak --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-5">Masa Kontrak</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="contract_start" value="{{ old('contract_start', isset($contract->contract_start) ? $contract->contract_start->format('Y-m-d') : '') }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tanggal Selesai <span class="text-red-500">*</span></label>
                    <input type="date" name="contract_end" value="{{ old('contract_end', isset($contract->contract_end) ? $contract->contract_end->format('Y-m-d') : '') }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>
        </div>

        {{-- Gaji & Tunjangan --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-5">Gaji & Tunjangan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Gaji Pokok (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="basic_salary" value="{{ old('basic_salary', $contract->basic_salary ?? 0) }}" min="0" step="1000"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tunjangan Makan (Rp)</label>
                    <input type="number" name="meal_allowance" value="{{ old('meal_allowance', $contract->meal_allowance ?? 0) }}" min="0" step="1000"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tunjangan Transportasi (Rp)</label>
                    <input type="number" name="transport_allowance" value="{{ old('transport_allowance', $contract->transport_allowance ?? 0) }}" min="0" step="1000"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tunjangan Lainnya (Rp)</label>
                    <input type="number" name="other_allowance" value="{{ old('other_allowance', $contract->other_allowance ?? 0) }}" min="0" step="1000"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Keterangan Tunjangan Lainnya</label>
                    <input type="text" name="other_allowance_note" value="{{ old('other_allowance_note', $contract->other_allowance_note ?? '') }}"
                           placeholder="cth: DIBAYAR PER 2 BULAN PERIODE"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- Tanda Tangan --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-5">Tanda Tangan Perusahaan (Pihak Pertama)</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nama Perwakilan <span class="text-red-500">*</span></label>
                    <input type="text" name="company_representative" value="{{ old('company_representative', $contract->company_representative ?? 'NICOLAS EDO WIDJAJA') }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Jabatan Perwakilan <span class="text-red-500">*</span></label>
                    <input type="text" name="company_representative_position" value="{{ old('company_representative_position', $contract->company_representative_position ?? 'PIC Online') }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Kota Penandatanganan <span class="text-red-500">*</span></label>
                    <input type="text" name="signed_city" value="{{ old('signed_city', $contract->signed_city ?? 'Surabaya') }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tanggal Penandatanganan <span class="text-red-500">*</span></label>
                    <input type="date" name="signed_date" value="{{ old('signed_date', isset($contract->signed_date) ? $contract->signed_date->format('Y-m-d') : date('Y-m-d')) }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pb-6">
            <a href="{{ route('super-admin.contracts.index') }}"
               class="px-5 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm shadow-blue-500/20 transition-all active:scale-95">
                {{ isset($contract) ? 'Simpan Perubahan' : 'Buat Kontrak' }}
            </button>
        </div>
    </form>
</div>
@endsection
