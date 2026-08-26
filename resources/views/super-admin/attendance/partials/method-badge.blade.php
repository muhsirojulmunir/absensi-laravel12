{{-- Badge kecil di bawah jam Check In/Check Out: metode absen, jarak radius, dan
     tautan foto (kalau Absen Manual). Dipakai di Monitoring Absensi Super Admin. --}}
@php
    $isManual = $method === 'manual';
@endphp
<div class="mt-1 flex flex-col items-center gap-0.5">
    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[8px] font-bold uppercase tracking-wider
        {{ $isManual
            ? 'bg-violet-50 dark:bg-violet-950/30 text-violet-600 dark:text-violet-400 border border-violet-100 dark:border-violet-900/30'
            : 'bg-slate-50 dark:bg-slate-800 text-slate-400 dark:text-slate-500 border border-slate-100 dark:border-slate-700' }}">
        @if($isManual) 📷 Manual @else 📍 Otomatis @endif
    </span>

    @if(!is_null($distance))
        <span class="text-[8px] font-bold text-slate-400 dark:text-slate-500">{{ $distance }}m</span>
    @endif

    @if($isManual && $photo)
        <a href="{{ asset('storage/' . $photo) }}" target="_blank" rel="noopener"
           class="text-[8px] font-bold text-blue-500 hover:text-blue-600 underline underline-offset-2">
            Lihat Foto
        </a>
    @endif
</div>
