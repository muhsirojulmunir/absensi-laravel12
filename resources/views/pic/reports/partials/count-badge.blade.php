{{-- Badge angka ringkasan (Hadir/Telat/Izin/Sakit). Nilai NOL sengaja diredupkan
     (abu-abu polos, tanpa warna/border) supaya angka yang benar-benar berarti lebih
     menonjol saat mata menyusuri tabel — tidak semua kolom terlihat "sama penting". --}}
@php
    $isZero = (int) $value === 0;
@endphp
<span @class([
    'inline-flex items-center justify-center min-w-[30px] h-7 px-2 text-xs font-bold rounded-lg tabular-nums',
    'bg-slate-50 dark:bg-slate-800/50 text-slate-300 dark:text-slate-600' => $isZero,
    $activeClass => !$isZero,
])>{{ $value }}</span>
