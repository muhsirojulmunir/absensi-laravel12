@extends('layouts.master')
@section('title', 'Backup Absensi Terhapus')
@section('content')
<div class="space-y-8">
    @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 px-5 py-3 rounded-2xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-5 py-3 rounded-2xl text-sm font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-bold text-blue-950 dark:text-white tracking-tight">Backup Absensi Terhapus</h1>
            <p class="text-blue-500 dark:text-blue-400 mt-1">Arsip absensi yang dihapus dari monitoring — dapat dipulihkan jika diperlukan.</p>
        </div>
        <a href="{{ route('super-admin.attendance.index') }}"
           class="inline-flex items-center gap-2 bg-blue-50 dark:bg-slate-800 border border-blue-200 dark:border-slate-700 text-blue-700 dark:text-blue-300 px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-blue-100 dark:hover:bg-slate-700 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Monitoring
        </a>
    </div>

    <form method="GET" action="{{ route('super-admin.attendance.deleted-backups') }}"
          class="flex flex-wrap items-center gap-3 bg-slate-50 dark:bg-slate-900 border border-blue-100 dark:border-slate-800 rounded-2xl p-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama karyawan..."
               class="bg-white dark:bg-slate-800 border border-blue-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm font-medium text-blue-950 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 min-w-[200px]">
        <input type="date" name="date" value="{{ request('date') }}"
               class="bg-white dark:bg-slate-800 border border-blue-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm font-bold text-blue-950 dark:text-white [color-scheme:dark] outline-none">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all">Filter</button>
        @if(request()->hasAny(['search', 'date']))
            <a href="{{ route('super-admin.attendance.deleted-backups') }}" class="text-xs font-bold text-blue-500 hover:underline">Reset</a>
        @endif
    </form>

    <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-3xl overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900 border-b border-blue-100 dark:border-slate-700">
                        <th class="px-6 py-4 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase">Karyawan</th>
                        <th class="px-4 py-4 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase">Tanggal</th>
                        <th class="px-4 py-4 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase text-center">Check In / Out</th>
                        <th class="px-4 py-4 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase">Status</th>
                        <th class="px-4 py-4 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase">Dihapus</th>
                        <th class="px-4 py-4 text-[11px] font-bold text-blue-500 dark:text-blue-400 uppercase text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50 dark:divide-slate-700">
                    @forelse($deletedBackups as $backup)
                        @php
                            $p = $backup->payload;
                        @endphp
                        <tr class="hover:bg-blue-50/30 dark:hover:bg-slate-700/30">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-blue-950 dark:text-white">{{ $backup->user_name }}</div>
                                <div class="text-[10px] text-blue-500 dark:text-blue-400 uppercase">{{ $backup->division_name ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-4 text-sm font-bold text-blue-800 dark:text-blue-200">
                                {{ $backup->date->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-4 py-4 text-center text-sm text-blue-600 dark:text-blue-400">
                                {{ $p['check_in'] ?? '--' }} / {{ $p['check_out'] ?? '--' }}
                            </td>
                            <td class="px-4 py-4">
                                <span class="text-xs font-bold uppercase text-blue-700 dark:text-blue-300">{{ $p['status'] ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-500 dark:text-slate-400">
                                {{ $backup->deleted_at->translatedFormat('d M Y H:i') }}
                                @if($backup->deletedByUser)
                                    <span class="block text-[10px]">oleh {{ $backup->deletedByUser->name }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                <form action="{{ route('super-admin.attendance.restore', $backup) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Pulihkan absensi ini ke data aktif?');">
                                    @csrf
                                    <button type="submit"
                                        class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[10px] font-bold uppercase hover:bg-emerald-100 transition-all">
                                        Pulihkan
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-16 text-center text-blue-500 dark:text-blue-400 text-sm font-medium opacity-60">
                                Belum ada backup absensi terhapus.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($deletedBackups->hasPages())
            <div class="px-6 py-4 border-t border-blue-100 dark:border-slate-700">
                {{ $deletedBackups->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
