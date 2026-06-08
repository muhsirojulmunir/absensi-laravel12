@extends('layouts.master')
@section('title', 'Kartu Stock - ' . ($user->location->name ?? $user->name))
@section('content')

<!-- Include html2canvas for PNG export and SheetJS for Excel export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<div class="space-y-6" x-data="{ search: '' }">
    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <a href="{{ route('super-admin.ramayana-stocks.index') }}?date={{ $filterDate }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-blue-600 mb-2 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Daftar Counter
            </a>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Kartu Stock</h2>
            <p class="text-sm font-semibold text-blue-600 dark:text-blue-400 mt-1">
                {{ $user->name }} - {{ $user->location->name ?? 'Belum Ada Lokasi' }}
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button onclick="exportToPNG()" class="inline-flex items-center px-4 py-2 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:hover:bg-emerald-800/50 rounded-xl text-sm font-bold shadow-sm transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Download PNG
            </button>
            <button onclick="exportToExcel()" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-green-500/30 transition-all transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Excel
            </button>
        </div>
    </div>

    <!-- Filter Bar: Search + Tanggal -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form action="{{ route('super-admin.ramayana-stocks.show', $user->id) }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="flex-1 w-full">
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Cari di Kartu Stock</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" x-model="search" placeholder="Ketik nama atau kode barang..." style="padding-left: 2.5rem;" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 p-2.5 transition-colors font-medium">
                </div>
            </div>
            <div class="w-full sm:w-auto">
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                    <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Tanggal
                </label>
                <input type="date" name="date" value="{{ $filterDate }}" onchange="this.form.submit()" class="w-full sm:w-48 bg-slate-50 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 p-2.5 transition-colors font-medium">
            </div>
        </form>
        <!-- Info tanggal yang aktif -->
        <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
            <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Menampilkan stok per tanggal: <span class="font-bold text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($filterDate)->translatedFormat('d F Y') }}</span>
            @if($filterDate !== \Carbon\Carbon::today()->toDateString())
                <a href="{{ route('super-admin.ramayana-stocks.show', $user->id) }}" class="ml-2 text-blue-500 hover:text-blue-700 font-semibold underline">Reset ke Hari Ini</a>
            @endif
        </div>
    </div>

    <!-- Kartu Stock Area (This area will be exported to PNG) -->
    <div id="kartu-stock-area" class="bg-white overflow-hidden" style="padding: 20px; font-family: Arial, sans-serif; color: #000; border: 1px solid #d1d5db; min-height: 500px;">
        
        <!-- Header Laporan -->
        <div class="text-center mb-6">
            <h1 style="font-size: 24px; font-weight: bold; margin: 0; text-transform: uppercase;">JAYA MANDIRI</h1>
            <h2 style="font-size: 20px; font-weight: bold; margin: 5px 0 0 0;">Laporan Posisi Persediaan (Qty) ({{ $user->location->name ?? 'RAMAYANA' }})</h2>
            <p style="font-size: 16px; font-weight: bold; margin: 5px 0 0 0;">Per Tanggal : {{ \Carbon\Carbon::parse($filterDate)->format('d-m-Y') }}</p>
        </div>

        <!-- Tabel -->
        <table id="stock-table" style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr>
                    <th style="border: 1px solid #000; padding: 10px; background-color: #1e3a5f; color: #fff; width: 5%; text-align: center; font-weight: bold;">No</th>
                    <th style="border: 1px solid #000; padding: 10px; background-color: #1e3a5f; color: #fff; width: 15%; text-align: left; font-weight: bold;">Kode Barang</th>
                    <th style="border: 1px solid #000; padding: 10px; background-color: #1e3a5f; color: #fff; width: 40%; text-align: left; font-weight: bold;">Nama Barang</th>
                    <th style="border: 1px solid #000; padding: 10px; background-color: #1e3a5f; color: #fff; width: 20%; text-align: center; font-weight: bold;">Total Quantity</th>
                    <th style="border: 1px solid #000; padding: 10px; background-color: #1e3a5f; color: #fff; width: 20%; text-align: left; font-weight: bold;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @php $rowNo = 1; @endphp
                @forelse($flatStocks as $stock)
                @php $isEven = $rowNo % 2 === 0; @endphp
                <tr x-show="search === '' || '{{ strtolower($stock['sku'] . ' ' . $stock['kode_barang']) }}'.includes(search.toLowerCase())"
                    style="background-color: {{ $isEven ? '#eef2ff' : '#ffffff' }};">
                    <td style="border: 1px solid #c0c0c0; padding: 8px; text-align: center; color: #666;">{{ $rowNo++ }}</td>
                    <td style="border: 1px solid #c0c0c0; padding: 8px; font-family: monospace; font-weight: bold;">{{ $stock['kode_barang'] ?: '-' }}</td>
                    <td style="border: 1px solid #c0c0c0; padding: 8px;">{{ $stock['sku'] }}@if(!empty($stock['size'])) {{ $stock['size'] }} @endif</td>
                    <td style="border: 1px solid #c0c0c0; padding: 8px; text-align: center; font-weight: bold; color: {{ $stock['qty'] < 0 ? '#dc2626' : ($stock['qty'] == 0 ? '#d97706' : '#059669') }};">{{ number_format($stock['qty'], 2, '.', '') }} {{ $stock['satuan'] }}</td>
                    <td style="border: 1px solid #c0c0c0; padding: 8px;"></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="border: 1px solid #000; padding: 20px; text-align: center;">
                        Belum ada data persediaan.
                    </td>
                </tr>
                @endforelse
            </tbody>
            <!-- Footer Summary -->
            <tfoot>
                <tr style="background-color: #1e3a5f;">
                    <td colspan="3" style="border: 1px solid #000; padding: 10px; font-weight: bold; text-align: right; color: #fff;">TOTAL :</td>
                    <td style="border: 1px solid #000; padding: 10px; font-weight: bold; text-align: center; color: #fff;">{{ number_format($totalOverallStock, 2, '.', '') }}</td>
                    <td style="border: 1px solid #000; padding: 10px; color: #fff;"></td>
                </tr>
            </tfoot>
        </table>
    </div>

</div>

<script>
    // Export area to PNG using html2canvas
    function exportToPNG() {
        const area = document.getElementById('kartu-stock-area');
        const originalBorder = area.style.border;
        area.style.border = 'none';
        
        html2canvas(area, {
            scale: 2,
            backgroundColor: "#ffffff",
            logging: false
        }).then(canvas => {
            area.style.border = originalBorder;
            let link = document.createElement('a');
            link.download = 'Kartu_Stock__{{ Str::slug($user->location->name ?? "RAMAYANA") }}_{{ \Carbon\Carbon::parse($filterDate)->format("d-m-Y") }}.png';
            link.href = canvas.toDataURL("image/png");
            link.click();
        });
    }

    // Export table to styled Excel using SheetJS
    function exportToExcel() {
        const wb = XLSX.utils.book_new();
        
        // Data array manual (untuk kontrol penuh atas formatting)
        const data = [];
        
        // Header laporan (baris 1-4)
        data.push(["JAYA MANDIRI"]);
        data.push(["Laporan Posisi Persediaan (Qty) ({{ $user->location->name ?? 'RAMAYANA' }})"]);
        data.push(["Per Tanggal : {{ \Carbon\Carbon::parse($filterDate)->format('d-m-Y') }}"]);
        data.push(["SPG: {{ $user->name }}"]);
        data.push([]); // Baris kosong
        
        // Header tabel
        data.push(["No", "Kode Barang", "Nama Barang", "Total Quantity", "Satuan", "Keterangan"]);
        
        // Data stok
        @php $excelNo = 1; @endphp
        @foreach($flatStocks as $stock)
        data.push([{{ $excelNo++ }}, "{{ $stock['kode_barang'] ?: '-' }}", "{{ addslashes($stock['sku']) }}@if(!empty($stock['size'])) {{ $stock['size'] }}@endif", {{ $stock['qty'] }}, "{{ $stock['satuan'] }}", ""]);
        @endforeach
        
        // Footer
        data.push(["", "", "TOTAL", {{ $totalOverallStock }}, "", ""]);
        
        const ws = XLSX.utils.aoa_to_sheet(data);
        
        // Set column widths
        ws['!cols'] = [
            { wch: 5 },   // No
            { wch: 15 },  // Kode Barang
            { wch: 45 },  // Nama Barang
            { wch: 18 },  // Total Qty
            { wch: 10 },  // Satuan
            { wch: 20 }   // Keterangan
        ];
        
        // Merge cells untuk header laporan
        ws['!merges'] = [
            { s: { r: 0, c: 0 }, e: { r: 0, c: 5 } }, // JAYA MANDIRI
            { s: { r: 1, c: 0 }, e: { r: 1, c: 5 } }, // Laporan...
            { s: { r: 2, c: 0 }, e: { r: 2, c: 5 } }, // Per Tanggal
            { s: { r: 3, c: 0 }, e: { r: 3, c: 5 } }, // SPG
        ];
        
        // Style header cells (bold & centered)
        const headerStyle = { font: { bold: true, sz: 14 }, alignment: { horizontal: "center" } };
        const tableHeaderStyle = { font: { bold: true, color: { rgb: "FFFFFF" } }, fill: { fgColor: { rgb: "1E3A5F" } }, alignment: { horizontal: "center" }, border: { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} } };
        
        // Apply styles (SheetJS community doesn't support styles natively, but we can set cell types)
        // For proper styling, set number formats
        const dataStartRow = 6; // row index where data starts (0-indexed)
        const totalRows = data.length;
        
        // Format qty column as number
        for (let i = dataStartRow; i < totalRows; i++) {
            const cellRef = XLSX.utils.encode_cell({ r: i, c: 3 }); // Column D (Qty)
            if (ws[cellRef] && typeof ws[cellRef].v === 'number') {
                ws[cellRef].t = 'n';
                ws[cellRef].z = '#,##0.00';
            }
        }
        
        XLSX.utils.book_append_sheet(wb, ws, "Laporan Persediaan");
        XLSX.writeFile(wb, 'Kartu_Stock_{{ Str::slug($user->location->name ?? "RAMAYANA") }}_{{ \Carbon\Carbon::parse($filterDate)->format("d-m-Y") }}.xlsx');
    }
</script>

@endsection
