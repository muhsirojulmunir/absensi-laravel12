@extends('layouts.master')
@section('title', 'Manajemen Pengguna')
@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 py-2">
        <div class="space-y-1">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Manajemen Pengguna</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Direktori semua karyawan dan tingkat akses mereka dalam sistem.</p>
        </div>
        <a href="{{ route('super-admin.users.create') }}" class="btn-premium-primary text-xs uppercase tracking-widest">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            <span>Tambah Pengguna Baru</span>
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/25 text-emerald-600 dark:text-emerald-400 px-5 py-4 rounded-xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <!-- Search/Filter Bar -->
    <form method="GET" action="{{ route('super-admin.users.index') }}" class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 rounded-2xl p-4 flex flex-col md:flex-row gap-4 shadow-sm">
        <div class="relative flex-1">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, username atau ID karyawan..." class="pl-11 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 placeholder-slate-400">
        </div>
        <button type="submit" class="btn-premium-primary text-xs uppercase tracking-widest py-3 px-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <span>Cari</span>
        </button>
    </form>

    <!-- Data Table -->
    <div class="bg-white dark:bg-dark-card border border-slate-200/50 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 dark:bg-slate-900/40 border-b border-slate-100 dark:border-slate-800/85">
                        <th class="px-6 py-4.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Karyawan</th>
                        <th class="px-6 py-4.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Divisi</th>
                        <th class="px-6 py-4.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Peran</th>
                        <th class="px-6 py-4.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Login Terakhir</th>
                        <th class="px-6 py-4.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                    @foreach($users as $user)
                        <tr class="hover:bg-slate-55 dark:hover:bg-slate-900/20 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3.5">
                                    <div class="w-9 h-9 bg-slate-100 dark:bg-slate-900 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 font-bold border border-slate-200/40 dark:border-slate-800 text-xs overflow-hidden shadow-inner">
                                        @if($user->avatar)
                                            <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($user->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-800 dark:text-white group-hover:text-blue-500 transition-colors">{{ $user->name }}</div>
                                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold tracking-wide">{{ $user->username }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-700 dark:text-slate-350 font-bold">
                                {{ $user->division->name ?? 'Sistem' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $roleColors = [
                                        'super-admin' => 'bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 border-indigo-100 dark:border-indigo-900/20',
                                        'hrd' => 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/20',
                                        'pic' => 'bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-900/20',
                                        'karyawan' => 'bg-slate-50 dark:bg-slate-900 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-800',
                                        'karyawan_ramayana' => 'bg-purple-50 dark:bg-purple-950/30 text-purple-600 dark:text-purple-400 border-purple-100 dark:border-purple-900/20'
                                    ];
                                    $roleColor = $roleColors[$user->role->slug] ?? 'bg-slate-50 dark:bg-slate-900 text-slate-600 border-slate-200 dark:border-slate-800';
                                @endphp
                                <span class="px-2.5 py-1 border {{ $roleColor }} text-[9px] font-bold uppercase rounded-lg tracking-wider">
                                    {{ $user->role->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-tight">
                                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum Login' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->is_active)
                                    <span class="inline-flex items-center space-x-1.5 text-[9px] font-bold text-emerald-500 uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                        <span>Aktif</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center space-x-1.5 text-[9px] font-bold text-rose-500 uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 bg-rose-500 rounded-full"></span>
                                        <span>Nonaktif</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end space-x-1.5">
                                    @if(auth()->id() !== $user->id)
                                    <form action="{{ route('super-admin.users.toggle-status', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan' }} pengguna ini?')" class="inline">
                                        @csrf
                                        <button type="submit" class="p-1.5 {{ $user->is_active ? 'text-rose-500 hover:bg-rose-500/10' : 'text-emerald-500 hover:bg-emerald-500/10' }} rounded-lg transition-colors cursor-pointer" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Pengguna">
                                            @if($user->is_active)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            @endif
                                        </button>
                                    </form>
                                    @endif
                                    <a href="{{ route('super-admin.users.edit', $user) }}" class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors" title="Edit Pengguna">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <form action="{{ route('super-admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus pengguna ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-500/10 rounded-lg transition-colors cursor-pointer" title="Hapus Pengguna">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="bg-slate-50/70 dark:bg-slate-900/40 px-6 py-4 border-t border-slate-100 dark:border-slate-800/80 flex flex-col md:flex-row items-center justify-between gap-3 text-xs">
            <p class="font-medium text-slate-450 dark:text-slate-500 italic">Menampilkan {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} pengguna</p>
            <div class="flex items-center space-x-1">
                {{ $users->links('vendor.pagination.tailwind') }}
            </div>
        </div>
    </div>
</div>
@endsection
