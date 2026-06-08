@extends('layouts.master')

@section('title', 'Evaluasi Kinerja Karyawan')

@section('content')
<div class="max-w-7xl mx-auto space-y-8 pb-20 animate-[fadeIn_0.5s_ease-out]">

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="group">
        <h1 class="text-3xl font-black text-blue-950 dark:text-white tracking-tighter transition-transform duration-300 group-hover:translate-x-1">Evaluasi Kinerja Bulanan</h1>
        <p class="text-sm text-blue-600/80 dark:text-blue-400 font-medium mt-1">Rangkuman kinerja cerdas dengan analisis AI JMN Matrix.</p>
    </div>

    <!-- Filter Bulan & Tahun -->
    <form action="{{ route('performance.index') }}" method="GET" class="flex gap-2">
        <select name="month" class="rounded-xl border-blue-100 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm transition hover:shadow-md dark:text-slate-200">
            @for($i=1; $i<=12; $i++)
                <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                    {{ Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                </option>
            @endfor
        </select>
        <select name="year" class="rounded-xl border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50 text-sm focus:ring-blue-500 focus:border-blue-500 dark:text-slate-200">
            @for($i=Carbon\Carbon::now()->year; $i>=2023; $i--)
                <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
            @endfor
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors">
            Filter
        </button>
    </form>
</div>

@if($groupedUsers->isEmpty())
<div class="bg-white dark:bg-slate-800 rounded-[2.5rem] shadow-[0_15px_50px_rgba(0,0,0,0.03)] border border-blue-100 dark:border-slate-700 p-12 text-center">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-900 mb-4 text-slate-400 dark:text-slate-500">
        <i class="fas fa-robot text-2xl"></i>
    </div>
    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-1">Evaluasi Belum Tersedia</h3>
    <p class="text-slate-500 dark:text-slate-400 text-sm">Tidak ada data evaluasi kinerja untuk bulan dan tahun yang dipilih.</p>
</div>
@else

<div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-700 rounded-[2.5rem] shadow-[0_15px_50px_rgba(0,0,0,0.03)] p-8">
    <div class="space-y-10">
        @foreach($groupedUsers as $groupName => $users)
        <div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-6 flex items-center gap-2 border-b border-slate-100 dark:border-slate-700/50 pb-3">
                <i class="fas fa-users text-blue-500"></i> {{ $groupName }}
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($users as $user)
                    @php
                        $eval = $user->performanceEvaluations->first();
                        $predicateColor = match($eval->predicate) {
                            'Sangat Baik' => 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-900/30',
                            'Baik' => 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-950/30 dark:text-blue-400 dark:border-blue-900/30',
                            'Cukup' => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:text-amber-400 dark:border-amber-900/30',
                            'Kurang' => 'bg-red-100 text-red-700 border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900/30',
                            default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-900 dark:text-slate-400 dark:border-slate-800'
                        };
                        $predicateIcon = match($eval->predicate) {
                            'Sangat Baik' => 'fa-star text-emerald-500',
                            'Baik' => 'fa-thumbs-up text-blue-500',
                            'Cukup' => 'fa-hand text-amber-500',
                            'Kurang' => 'fa-triangle-exclamation text-red-500',
                            default => 'fa-clock text-slate-400'
                        };
                    @endphp
                    
                    <a href="{{ route('performance.show', ['id' => $user->id, 'month' => $month, 'year' => $year]) }}" 
                       class="block bg-slate-50 dark:bg-slate-900 rounded-[2rem] border border-blue-50/50 dark:border-slate-800 hover:shadow-md hover:border-blue-300 dark:hover:border-blue-500/50 transition-all duration-300 group overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full bg-blue-100/30 dark:bg-slate-800 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-xl">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-800 dark:text-slate-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors text-lg">{{ $user->name }}</h3>
                                        <p class="text-[11px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-semibold">{{ $user->division->name ?? 'Staff' }}</p>
                                    </div>
                                </div>
                                <i class="fas {{ $predicateIcon }} text-xl drop-shadow-sm"></i>
                            </div>
                            
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border {{ $predicateColor }}">
                                {{ $eval->predicate }}
                            </div>
                            
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-5 line-clamp-3 leading-relaxed italic">
                                "{{ $eval->ai_feedback }}"
                            </p>
                        </div>
                        <div class="px-6 py-4 border-t border-blue-50 dark:border-slate-800 bg-slate-100/50 dark:bg-slate-950/50 flex justify-between items-center transition-colors group-hover:bg-blue-50/50 dark:group-hover:bg-blue-900/20">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 flex items-center gap-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                Lihat Detail Evaluasi
                            </span>
                            <i class="fas fa-arrow-right text-slate-300 dark:text-slate-600 text-xs group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-transform group-hover:translate-x-1"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
