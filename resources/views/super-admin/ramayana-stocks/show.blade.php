@extends('layouts.master')
@section('title', 'Kartu Stock - ' . ($user->location->name ?? $user->name))
@section('content')

<!-- SheetJS untuk Export Excel | JSZip untuk ZIP -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

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

        <div class="flex flex-wrap gap-2 mt-4 sm:mt-0">
            <a href="{{ route('super-admin.ramayana-stocks.incoming.create', $user->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-800/50 rounded-xl text-sm font-bold shadow-sm transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Barang Masuk
            </a>
            <a href="{{ route('super-admin.ramayana-stocks.incoming.history', $user->id) }}" class="inline-flex items-center px-4 py-2 bg-purple-100 text-purple-700 hover:bg-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:hover:bg-purple-800/50 rounded-xl text-sm font-bold shadow-sm transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Riwayat Barang Masuk
            </a>
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
        <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
            <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Menampilkan stok per tanggal: <span class="font-bold text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($filterDate)->translatedFormat('d F Y') }}</span>
            @if($filterDate !== \Carbon\Carbon::today()->toDateString())
                <a href="{{ route('super-admin.ramayana-stocks.show', $user->id) }}" class="ml-2 text-blue-500 hover:text-blue-700 font-semibold underline">Reset ke Hari Ini</a>
            @endif
        </div>
    </div>

    <!-- Kartu Stock Area -->
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
                    <th onclick="sortTable('name')" style="border: 1px solid #000; padding: 10px; background-color: #1e3a5f; color: #fff; width: 40%; text-align: left; font-weight: bold; cursor: pointer; user-select: none;">
                        Nama Barang <span id="sort-name-icon" style="font-size: 11px; margin-left: 4px; opacity: 0.9;">↑</span>
                    </th>
                    <th onclick="sortTable('qty')" style="border: 1px solid #000; padding: 10px; background-color: #1e3a5f; color: #fff; width: 20%; text-align: center; font-weight: bold; cursor: pointer; user-select: none;">
                        Total Quantity <span id="sort-qty-icon" style="font-size: 11px; margin-left: 4px; opacity: 0.8;">⇅</span>
                    </th>
                    <th style="border: 1px solid #000; padding: 10px; background-color: #1e3a5f; color: #fff; width: 20%; text-align: left; font-weight: bold;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @php $rowNo = 1; @endphp
                @forelse($flatStocks as $stock)
                @php $isEven = $rowNo % 2 === 0; @endphp
                <tr x-show="search === '' || '{{ strtolower(($stock['sku'] ?? '') . ' ' . ($stock['kode_barang'] ?? '')) }}'.includes(search.toLowerCase())"
                    data-orig-index="{{ $rowNo - 1 }}"
                    data-name="{{ strtolower($stock['sku'] ?? '') }}@if(!empty($stock['size'])) {{ strtolower($stock['size']) }}@endif"
                    data-qty="{{ $stock['qty'] }}"
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
// ── SORTING ──────────────────────────────────────────────────────────────────
// Default: Nama Barang A→Z (dir=1) saat halaman pertama load
const sortState = { col: 'name', dir: 1 };

function applySort() {
    const tbody = document.querySelector('#stock-table tbody');
    const rows  = Array.from(tbody.querySelectorAll('tr[data-orig-index]'));

    const icons = { 1: '↑', 2: '↓', 0: '⇅' };
    document.getElementById('sort-name-icon').textContent = sortState.col === 'name' ? icons[sortState.dir] : '⇅';
    document.getElementById('sort-qty-icon').textContent  = sortState.col === 'qty'  ? icons[sortState.dir] : '⇅';

    if (sortState.dir === 0) {
        rows.sort((a, b) => parseInt(a.dataset.origIndex) - parseInt(b.dataset.origIndex));
    } else {
        rows.sort((a, b) => {
            if (sortState.col === 'name') {
                const va = a.dataset.name || '';
                const vb = b.dataset.name || '';
                return sortState.dir === 1 ? va.localeCompare(vb) : vb.localeCompare(va);
            } else {
                const va = parseFloat(a.dataset.qty) || 0;
                const vb = parseFloat(b.dataset.qty) || 0;
                return sortState.dir === 1 ? va - vb : vb - va;
            }
        });
    }

    rows.forEach((row, i) => {
        tbody.appendChild(row);
        row.cells[0].textContent    = i + 1;
        row.style.backgroundColor   = (i % 2 !== 0) ? '#eef2ff' : '#ffffff';
    });
}

function sortTable(col) {
    if (sortState.col === col) {
        // name: 1(A-Z) → 2(Z-A) → 1(A-Z) — tidak ada default/reset untuk name
        // qty : 1(kecil-besar) → 2(besar-kecil) → 0(default) → 1 dst
        if (col === 'name') {
            sortState.dir = sortState.dir === 1 ? 2 : 1;
        } else {
            sortState.dir = (sortState.dir + 1) % 3;
        }
    } else {
        sortState.col = col;
        sortState.dir = 1;
    }
    applySort();
}

// Jalankan default sort A→Z saat halaman selesai load
document.addEventListener('DOMContentLoaded', () => applySort());

// ── EXPORT ZIP A4 (Canvas API — tanpa html2canvas, jauh lebih cepat) ─────────
async function exportToZipA4(btn) {
    const originalHTML  = btn.innerHTML;
    btn.disabled        = true;

    const ROWS_PER_PAGE = 30;
    const PW = 1123, PH = 794; // landscape A4 px @96dpi — lebih lebar untuk tabel

    const allRows     = Array.from(document.querySelectorAll('#stock-table tbody tr[data-orig-index]'));
    const visibleRows = allRows.filter(r => r.style.display !== 'none');
    const totalPages  = Math.max(1, Math.ceil(visibleRows.length / ROWS_PER_PAGE));

    const locationName  = '{{ Str::slug($user->location->name ?? "RAMAYANA") }}';
    const filterDateStr = '{{ \Carbon\Carbon::parse($filterDate)->format("d-m-Y") }}';
    const locationLabel = '{{ $user->location->name ?? "RAMAYANA" }}';
    const totalStock    = '{{ number_format($totalOverallStock, 2, ".", "") }}';

    const zip = new JSZip();

    // Warna
    const C_HEADER = '#1e3a5f';
    const C_EVEN   = '#eef2ff';
    const C_WHITE  = '#ffffff';
    const C_BORDER = '#c0c0c0';
    const C_DARK   = '#000000';

    // Helper: wrap text dalam lebar tertentu (canvas measureText)
    function wrapText(ctx, text, maxWidth) {
        const words = String(text).split(' ');
        const lines = [];
        let line    = '';
        for (const word of words) {
            const test = line ? line + ' ' + word : word;
            if (ctx.measureText(test).width > maxWidth && line) {
                lines.push(line);
                line = word;
            } else {
                line = test;
            }
        }
        if (line) lines.push(line);
        return lines.length ? lines : [''];
    }

    for (let page = 0; page < totalPages; page++) {
        btn.innerHTML = `Memproses halaman ${page + 1}/${totalPages}...`;

        const pageRows = visibleRows.slice(page * ROWS_PER_PAGE, (page + 1) * ROWS_PER_PAGE);

        const canvas  = document.createElement('canvas');
        canvas.width  = PW * 2; // retina 2x
        canvas.height = PH * 2;
        const ctx     = canvas.getContext('2d');
        ctx.scale(2, 2); // scale up untuk ketajaman

        // Background putih
        ctx.fillStyle = C_WHITE;
        ctx.fillRect(0, 0, PW, PH);

        // ── Header laporan ──
        ctx.fillStyle = C_DARK;
        ctx.textAlign = 'center';

        ctx.font = 'bold 18px Arial';
        ctx.fillText('JAYA MANDIRI', PW / 2, 36);

        ctx.font = 'bold 13px Arial';
        ctx.fillText(`Laporan Posisi Persediaan (Qty) (${locationLabel})`, PW / 2, 56);

        ctx.font = 'bold 11px Arial';
        ctx.fillText(`Per Tanggal : ${filterDateStr.replace(/-/g, '/')}`, PW / 2, 74);

        ctx.font = '10px Arial';
        ctx.fillStyle = '#666';
        ctx.fillText(`Halaman ${page + 1} / ${totalPages}`, PW / 2, 90);

        // ── Tabel ──
        const tableX      = 20;
        const tableWidth  = PW - 40;
        const colWidths   = [
            tableWidth * 0.04,  // No
            tableWidth * 0.13,  // Kode Barang
            tableWidth * 0.42,  // Nama Barang
            tableWidth * 0.22,  // Qty
            tableWidth * 0.19,  // Keterangan
        ];
        const rowH        = 26;
        const headerH     = 30;
        let   curY        = 104;

        // Header tabel
        ctx.fillStyle = C_HEADER;
        ctx.fillRect(tableX, curY, tableWidth, headerH);

        ctx.fillStyle = C_WHITE;
        ctx.font      = 'bold 10px Arial';
        ctx.textAlign = 'center';

        const headers = ['No', 'Kode Barang', 'Nama Barang', 'Total Quantity', 'Keterangan'];
        let xOff = tableX;
        headers.forEach((h, i) => {
            ctx.fillText(h, xOff + colWidths[i] / 2, curY + 19);
            xOff += colWidths[i];
        });

        // Border header
        ctx.strokeStyle = C_DARK;
        ctx.lineWidth   = 0.8;
        xOff = tableX;
        headers.forEach((_, i) => {
            ctx.strokeRect(xOff, curY, colWidths[i], headerH);
            xOff += colWidths[i];
        });
        curY += headerH;

        // Baris data
        pageRows.forEach((row, idx) => {
            const globalNo = page * ROWS_PER_PAGE + idx + 1;
            const isEven   = globalNo % 2 === 0;
            const cells    = row.cells;

            const namaText = cells[2].textContent.trim();
            const namaLines = wrapText(
                Object.assign(document.createElement('canvas').getContext('2d'), { font: '10px Arial' }),
                namaText,
                colWidths[2] - 8
            );
            const rowHeight = Math.max(rowH, namaLines.length * 14 + 8);

            // Background baris
            ctx.fillStyle = isEven ? C_EVEN : C_WHITE;
            ctx.fillRect(tableX, curY, tableWidth, rowHeight);

            // Teks tiap sel
            ctx.fillStyle   = '#444';
            ctx.font        = '10px Arial';
            ctx.textAlign   = 'center';

            // No
            ctx.fillText(String(globalNo), tableX + colWidths[0] / 2, curY + rowHeight / 2 + 4);

            // Kode Barang
            ctx.font      = 'bold 9px monospace';
            ctx.textAlign = 'left';
            ctx.fillText(cells[1].textContent.trim(), tableX + colWidths[0] + 4, curY + rowHeight / 2 + 4);

            // Nama Barang (multi-line)
            ctx.font = '10px Arial';
            const namaCtx = canvas.getContext('2d');
            namaCtx.scale(1, 1);
            namaLines.forEach((line, li) => {
                ctx.fillText(
                    line,
                    tableX + colWidths[0] + colWidths[1] + 4,
                    curY + 14 + li * 14
                );
            });

            // Qty — warna sesuai nilai
            const qtyVal   = parseFloat(row.dataset.qty) || 0;
            ctx.fillStyle  = qtyVal < 0 ? '#dc2626' : (qtyVal === 0 ? '#d97706' : '#059669');
            ctx.font       = 'bold 10px Arial';
            ctx.textAlign  = 'center';
            const qtyX     = tableX + colWidths[0] + colWidths[1] + colWidths[2];
            ctx.fillText(cells[3].textContent.trim(), qtyX + colWidths[3] / 2, curY + rowHeight / 2 + 4);

            // Border baris
            ctx.strokeStyle = C_BORDER;
            ctx.lineWidth   = 0.5;
            xOff = tableX;
            colWidths.forEach(w => {
                ctx.strokeRect(xOff, curY, w, rowHeight);
                xOff += w;
            });

            curY += rowHeight;
        });

        // Footer TOTAL (hanya halaman terakhir)
        if (page === totalPages - 1) {
            ctx.fillStyle = C_HEADER;
            ctx.fillRect(tableX, curY, tableWidth, rowH);

            ctx.fillStyle = C_WHITE;
            ctx.font      = 'bold 10px Arial';
            ctx.textAlign = 'right';
            const totalLabelX = tableX + colWidths[0] + colWidths[1] + colWidths[2] - 6;
            ctx.fillText('TOTAL :', totalLabelX, curY + 18);

            ctx.textAlign = 'center';
            const totalQtyX = tableX + colWidths[0] + colWidths[1] + colWidths[2];
            ctx.fillText(totalStock, totalQtyX + colWidths[3] / 2, curY + 18);

            ctx.strokeStyle = C_DARK;
            ctx.lineWidth   = 0.8;
            xOff = tableX;
            colWidths.forEach(w => {
                ctx.strokeRect(xOff, curY, w, rowH);
                xOff += w;
            });
        }

        // Canvas → PNG base64 → masuk ZIP
        const imgData = canvas.toDataURL('image/png').split(',')[1];
        zip.file(`Halaman_${String(page + 1).padStart(2, '0')}_dari_${totalPages}.png`, imgData, { base64: true });
    }

    btn.innerHTML = 'Membuat ZIP...';
    const blob   = await zip.generateAsync({ type: 'blob', compression: 'DEFLATE', compressionOptions: { level: 3 } });
    const link   = document.createElement('a');
    link.href     = URL.createObjectURL(blob);
    link.download = `Kartu_Stock_${locationName}_${filterDateStr}.zip`;
    link.click();

    btn.disabled  = false;
    btn.innerHTML = originalHTML;
}

// ── EXPORT EXCEL ──────────────────────────────────────────────────────────────
function exportToExcel() {
    const wb   = XLSX.utils.book_new();
    const data = [];

    data.push(["JAYA MANDIRI"]);
    data.push(["Laporan Posisi Persediaan (Qty) ({{ $user->location->name ?? 'RAMAYANA' }})"]);
    data.push(["Per Tanggal : {{ \Carbon\Carbon::parse($filterDate)->format('d-m-Y') }}"]);
    data.push(["SPG: {{ $user->name }}"]);
    data.push([]);
    data.push(["No", "Kode Barang", "Nama Barang", "Total Quantity", "Satuan", "Keterangan"]);

    @php $excelNo = 1; @endphp
    @foreach($flatStocks as $stock)
    data.push([{{ $excelNo++ }}, "{{ $stock['kode_barang'] ?: '-' }}", "{{ addslashes($stock['sku'] ?? '') }}@if(!empty($stock['size'])) {{ $stock['size'] }}@endif", {{ $stock['qty'] }}, "{{ $stock['satuan'] }}", ""]);
    @endforeach

    data.push(["", "", "TOTAL", {{ $totalOverallStock }}, "", ""]);

    const ws = XLSX.utils.aoa_to_sheet(data);
    ws['!cols'] = [{ wch:5 }, { wch:15 }, { wch:45 }, { wch:18 }, { wch:10 }, { wch:20 }];
    ws['!merges'] = [
        { s:{r:0,c:0}, e:{r:0,c:5} },
        { s:{r:1,c:0}, e:{r:1,c:5} },
        { s:{r:2,c:0}, e:{r:2,c:5} },
        { s:{r:3,c:0}, e:{r:3,c:5} },
    ];

    const dataStartRow = 6;
    for (let i = dataStartRow; i < data.length; i++) {
        const cellRef = XLSX.utils.encode_cell({ r: i, c: 3 });
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