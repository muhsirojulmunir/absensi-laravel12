@extends('layouts.master')
@section('title', 'Manajemen Pengguna')
@section('content')
<div class="space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-bold text-blue-950 dark:text-white tracking-tight">Manajemen Pengguna</h1>
            <p class="text-blue-500 dark:text-blue-400 mt-1">Direktori semua karyawan dan tingkat akses mereka dalam sistem.</p>
        </div>
        <a href="{{ route('super-admin.users.create') }}" class="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-blue-600/10 transition-all active:scale-[0.98]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            <span>Tambah Pengguna Baru</span>
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 px-6 py-4 rounded-2xl text-sm font-semibold animate-in fade-in slide-in-from-top-4 duration-300">
            {{ session('success') }}
        </div>
    @endif

    <!-- Search/Filter Bar -->
    <div class="bg-blue-50/30 dark:bg-slate-900/50 border border-blue-100 dark:border-slate-800 rounded-2xl p-4 flex flex-col md:flex-row gap-4">
        <div class="relative flex-1">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-blue-500 dark:text-blue-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text" placeholder="Cari karyawan berdasarkan nama, email atau ID..." class="w-full bg-slate-50 dark:bg-slate-900 shadow-inner border border-blue-100 dark:border-slate-800 rounded-xl py-3 pl-12 pr-4 text-sm text-blue-950 dark:text-blue-100 focus:ring-2 focus:ring-blue-600 outline-none transition-all placeholder-slate-600 dark:placeholder-slate-400">
        </div>
        <select class="bg-slate-50 dark:bg-slate-900 shadow-inner border border-blue-100 dark:border-slate-800 rounded-xl px-4 py-3 text-sm text-blue-950 dark:text-blue-100 focus:ring-2 focus:ring-blue-600 outline-none cursor-pointer">
            <option value="" class="dark:bg-slate-900">Semua Divisi</option>
        </select>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-blue-100 dark:border-slate-700 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.06)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-blue-50/30 dark:bg-slate-900/50 border-b border-blue-100 dark:border-slate-700">
                        <th class="px-8 py-5 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Karyawan</th>
                        <th class="px-6 py-5 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Divisi</th>
                        <th class="px-6 py-5 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Peran</th>
                        <th class="px-6 py-5 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Login Terakhir</th>
                        <th class="px-6 py-5 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider">Status</th>
                        <th class="px-8 py-5 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50">
                    @foreach($users as $user)
                        <tr class="hover:bg-blue-50/30 dark:hover:bg-slate-700/30 transition-colors group">
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 bg-blue-50 dark:bg-slate-900 rounded-xl flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold border border-blue-200/50 dark:border-slate-700 overflow-hidden">
                                        @if($user->avatar)
                                            <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($user->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-blue-950 dark:text-white group-hover:text-blue-400 transition-colors">{{ $user->name }}</div>
                                        <div class="text-[11px] text-blue-500 dark:text-blue-400 font-medium">{{ $user->username }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-blue-900 dark:text-blue-200 font-medium">
                                {{ $user->division->name ?? 'Sistem' }}
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                @php
                                    $roleColors = [
                                        'super-admin' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                                        'hrd' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                        'pic' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                        'karyawan' => 'bg-slate-700/30 text-blue-600/80 border-blue-200/50',
                                    ];
                                    $roleColor = $roleColors[$user->role->slug] ?? 'bg-blue-50 text-blue-500';
                                @endphp
                                <span class="px-3 py-1 border {{ $roleColor }} text-[10px] font-bold uppercase rounded-lg tracking-wide">
                                    {{ $user->role->name }}
                                </span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <span class="text-[11px] font-bold text-blue-900/60 dark:text-blue-400/60 uppercase tracking-tighter italic">
                                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum Login' }}
                                </span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                @if($user->is_active)
                                    <span class="inline-flex items-center space-x-2 text-[10px] font-bold text-emerald-500 uppercase">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                        <span>Aktif</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center space-x-2 text-[10px] font-bold text-red-500 uppercase">
                                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                        <span>Nonaktif</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end space-x-2 opacity-50 group-hover:opacity-100 transition-opacity">
                                    @if(auth()->id() !== $user->id)
                                    <form action="{{ route('super-admin.users.toggle-status', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan' }} pengguna ini?')" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 {{ $user->is_active ? 'text-red-500 hover:bg-red-500/10' : 'text-emerald-500 hover:bg-emerald-500/10' }} rounded-lg transition-all" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Pengguna">
                                            @if($user->is_active)
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                            @else
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            @endif
                                        </button>
                                    </form>
                                    @endif
                                    <a href="{{ route('super-admin.users.edit', $user) }}" class="p-2 text-blue-600/80 dark:text-blue-400 hover:text-blue-950 dark:hover:text-white hover:bg-slate-700/50 rounded-lg transition-all" title="Edit Pengguna">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <form action="{{ route('super-admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus pengguna ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-blue-600/80 dark:text-blue-400 hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-all" title="Hapus Pengguna">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="bg-slate-50 dark:bg-slate-900 shadow-inner/30 px-8 py-4 border-t border-blue-100 dark:border-slate-700 flex items-center justify-between">
            <p class="text-[11px] font-medium text-blue-500 dark:text-blue-400 italic">Menampilkan {{ $users->count() }} total pengguna</p>
        </div>
    </div>
</div>
@endsection
