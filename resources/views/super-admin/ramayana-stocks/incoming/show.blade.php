@extends('layouts.master')
@section('title', 'Detail Barang Masuk - ' . ($user->location->name ?? $user->name))
@section('content')

<!-- SheetJS untuk Export Excel | JSZip untuk ZIP -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<div class="space-y-6">
    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <a href="{{ route('super-admin.ramayana-stocks.incoming.history', $user->id) }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-blue-600 mb-2 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Riwayat Barang Masuk
            </a>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Laporan Detail Barang Masuk</h2>
            <p class="text-sm font-semibold text-blue-600 dark:text-blue-400 mt-1">
                {{ $user->name }} - {{ $user->location->name ?? 'Belum Ada Lokasi' }}
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button id="btn-zip" onclick="exportToZipA4(this)" class="inline-flex items-center px-4 py-2 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:hover:bg-emerald-800/50 rounded-xl text-sm font-bold shadow-sm transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Download ZIP (A4)
            </button>
            <button onclick="exportToExcel()" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-green-500/30 transition-all transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Excel
            </button>
        </div>
    </div>

    <!-- Informasi Batch -->
    <div class="bg-blue-50 dark:bg-slate-800 border-l-4 border-blue-500 p-4 rounded-r-xl shadow-sm">
        <div class="flex flex-col md:flex-row gap-6">
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal Input</p>
                <p class="font-bold text-slate-800 dark:text-white">{{ $incomingStock->created_at->translatedFormat('d F Y, H:i') }}</p>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal Surat Jalan</p>
                <p class="font-bold text-slate-800 dark:text-white">{{ $incomingStock->date->translatedFormat('d F Y') }}</p>
            </div>
            @if($incomingStock->note)
            <div class="flex-1">
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Keterangan / Nomor DO</p>
                <p class="font-bold text-slate-800 dark:text-white">{{ $incomingStock->note }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Kartu Stock Area (Format Cetak) -->
    <div id="kartu-stock-area" class="bg-white overflow-hidden" style="padding: 20px; font-family: Arial, sans-serif; color: #000; border: 1px solid #d1d5db; min-height: 500px;">

        <!-- Header Laporan -->
        <div class="text-center mb-6">
            <h1 style="font-size: 24px; font-weight: bold; margin: 0; text-transform: uppercase;">JAYA MANDIRI</h1>
            <h2 style="font-size: 20px; font-weight: bold; margin: 5px 0 0 0;">Laporan Bukti Barang Masuk ({{ $user->location->name ?? 'RAMAYANA' }})</h2>
            <p style="font-size: 16px; font-weight: bold; margin: 5px 0 0 0;">Per Tanggal : {{ $incomingStock->date->format('d-m-Y') }}</p>
            @if($incomingStock->note)
                <p style="font-size: 14px; font-weight: normal; margin: 5px 0 0 0;">Ref: {{ $incomingStock->note }}</p>
            @endif
        </div>

        <!-- Tabel -->
        <table id="stock-table" style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="background-color: #f3f4f6;">
                    <th style="border: 1px solid #000; padding: 10px; width: 5%;">NO</th>
                    <th style="border: 1px solid #000; padding: 10px; width: 15%;">Kode Barang</th>
                    <th style="border: 1px solid #000; padding: 10px; width: 50%; text-align: left;">Nama Barang</th>
                    <th style="border: 1px solid #000; padding: 10px; width: 15%;">Qty Masuk</th>
                    <th style="border: 1px solid #000; padding: 10px; width: 15%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($incomingStock->items as $index => $item)
                <tr>
                    <td style="border: 1px solid #c0c0c0; padding: 8px; text-align: center;">{{ $index + 1 }}</td>
                    <td style="border: 1px solid #c0c0c0; padding: 8px; text-align: center;">{{ $item->kode_barang }}</td>
                    <td style="border: 1px solid #c0c0c0; padding: 8px;">{{ $item->sku }}@if(!empty($item->size)) {{ $item->size }} @endif</td>
                    <td style="border: 1px solid #c0c0c0; padding: 8px; text-align: center; font-weight: bold; color: #059669;">{{ number_format($item->qty, 2, '.', '') }} {{ $item->satuan }}</td>
                    <td style="border: 1px solid #c0c0c0; padding: 8px;"></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="border: 1px solid #000; padding: 20px; text-align: center;">
                        Tidak ada detail barang masuk.
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background-color: #1e3a5f;">
                    <td colspan="3" style="border: 1px solid #000; padding: 10px; font-weight: bold; text-align: right; color: #fff;">TOTAL BARANG MASUK :</td>
                    <td style="border: 1px solid #000; padding: 10px; font-weight: bold; text-align: center; color: #fff;">{{ number_format($incomingStock->total_qty, 2, '.', '') }}</td>
                    <td style="border: 1px solid #000; padding: 10px; color: #fff;"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
// Fungsi Export sama dengan di show.blade.php
function exportToExcel() {
    let originalTable = document.getElementById('stock-table');
    let cloneTable = originalTable.cloneNode(true);
    let rows = cloneTable.querySelectorAll('tr');

    rows.forEach(row => {
        let cells = row.querySelectorAll('th, td');
        cells.forEach(cell => {
            cell.removeAttribute('style');
        });
    });

    let wb = XLSX.utils.book_new();
    let ws = XLSX.utils.table_to_sheet(cloneTable);

    const titleStr = 'LAPORAN BARANG MASUK - {{ $user->location->name ?? "RAMAYANA" }}';
    const dateStr = 'TANGGAL : {{ $incomingStock->date->format('d-m-Y') }}';
    XLSX.utils.sheet_add_aoa(ws, [
        [titleStr],
        [dateStr],
        []
    ], { origin: "A1" });

    ws['!merges'] = [
        { s: { r: 0, c: 0 }, e: { r: 0, c: 4 } },
        { s: { r: 1, c: 0 }, e: { r: 1, c: 4 } }
    ];

    ws['!cols'] = [ { wpx: 40 }, { wpx: 100 }, { wpx: 300 }, { wpx: 100 }, { wpx: 100 } ];
    XLSX.utils.book_append_sheet(wb, ws, "Barang Masuk");

    const tgl = '{{ $incomingStock->date->format('Ymd') }}';
    const loc = '{{ preg_replace('/[^a-zA-Z0-9_-]/', '', $user->location->name ?? "Ramayana") }}';
    const fileName = `Barang_Masuk_${loc}_${tgl}.xlsx`;

    XLSX.writeFile(wb, fileName);
}

function chunkArray(array, size) {
    const chunked = [];
    for(let i=0; i<array.length; i+=size) {
        chunked.push(array.slice(i, i+size));
    }
    return chunked;
}

function exportToZipA4(btn) {
    const originalText = btn.innerHTML;
    btn.innerHTML = '<svg class="animate-spin w-4 h-4 mr-2 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"></path></svg> Memproses...';
    btn.disabled = true;

    try {
        const rows = Array.from(document.querySelectorAll('#stock-table tbody tr'));
        const totalFooter = document.querySelector('#stock-table tfoot').outerHTML;
        
        let validRows = rows.filter(r => !r.innerText.includes('Tidak ada detail barang masuk.'));
        if (validRows.length === 0) {
            alert('Tidak ada data untuk di-download');
            return;
        }

        const rowsPerPage = 38; 
        const pages = chunkArray(validRows, rowsPerPage);

        const zip = new JSZip();

        pages.forEach((pageRows, index) => {
            const pageNum = index + 1;
            const isLastPage = pageNum === pages.length;

            let tbodyHtml = pageRows.map(tr => tr.outerHTML).join('');
            let footerHtml = isLastPage ? totalFooter : '';

            let htmlContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>Laporan Bukti Barang Masuk - Hal ${pageNum}</title>
                <style>
                    @page { size: A4; margin: 10mm; }
                    body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 0; color: #000; }
                    .header { text-align: center; margin-bottom: 10px; }
                    .header h1 { font-size: 16px; margin: 0; }
                    .header h2 { font-size: 14px; margin: 3px 0 0 0; }
                    .header p { font-size: 11px; margin: 3px 0 0 0; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
                    th, td { border: 1px solid #000; padding: 4px; }
                    th { background-color: #f0f0f0; }
                    .page-number { text-align: right; font-size: 10px; font-style: italic; margin-top: 5px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>JAYA MANDIRI</h1>
                    <h2>LAPORAN BUKTI BARANG MASUK ({{ $user->location->name ?? 'RAMAYANA' }})</h2>
                    <p>Per Tanggal : {{ $incomingStock->date->format('d-m-Y') }}</p>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">NO</th>
                            <th style="width: 15%;">KODE BARANG</th>
                            <th style="width: 50%; text-align:left;">NAMA BARANG</th>
                            <th style="width: 15%;">QTY MASUK</th>
                            <th style="width: 15%;">KETERANGAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tbodyHtml}
                    </tbody>
                    ${footerHtml}
                </table>
                <div class="page-number">Halaman ${pageNum} dari ${pages.length}</div>
                <script>window.onload = function() { window.print(); }</script>
            </body>
            </html>
            `;

            zip.file(`Hal_${pageNum}.html`, htmlContent);
        });

        const tgl = '{{ $incomingStock->date->format('Ymd') }}';
        const loc = '{{ preg_replace('/[^a-zA-Z0-9_-]/', '', $user->location->name ?? "Ramayana") }}';
        
        zip.generateAsync({type:"blob"}).then(function(content) {
            const url = window.URL.createObjectURL(content);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Barang_Masuk_${loc}_${tgl}.zip`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        });
    } catch (e) {
        console.error(e);
        alert('Terjadi kesalahan saat membuat ZIP');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}
</script>

@endsection
