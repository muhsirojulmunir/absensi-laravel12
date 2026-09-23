<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontrak Kerja - {{ $contract->employee_name }}</title>
    @php
        $kopPath = public_path('images/kop_surat_record.png');
        $kopSrc = file_exists($kopPath) 
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($kopPath)) 
            : asset('images/kop_surat_record.png');
    @endphp
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            /* Standar Kertas F4 / Folio di Indonesia: 215mm x 330mm */
            size: 215mm 330mm;
            margin: 15mm 20mm 15mm 20mm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11.5pt;
            line-height: 1.55;
            color: #000;
            background-color: #f1f5f9;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Top Bar Navigasi (Hanya di Layar) */
        .no-print-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #0f172a;
            color: #fff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 1000;
            box-shadow: 0 4px 14px rgba(0,0,0,0.18);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 13.5px;
        }

        .no-print-bar .info-tag {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .no-print-bar .badge-f4 {
            background: #2563eb;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 6px;
            letter-spacing: 0.5px;
        }

        .no-print-bar a, .no-print-bar button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            font-size: 13px;
        }

        .btn-back {
            background: #334155;
            color: #e2e8f0;
        }
        .btn-back:hover {
            background: #475569;
            color: #fff;
        }

        .btn-edit {
            background: #1e40af;
            color: #fff;
        }
        .btn-edit:hover {
            background: #1d4ed8;
        }

        .btn-print {
            background: #16a34a;
            color: #fff;
        }
        .btn-print:hover {
            background: #15803d;
        }

        /* Dokumen Wrapper (Ukuran F4) */
        .document-wrapper {
            width: 215mm;
            min-height: 330mm;
            margin: 68px auto 40px;
            background: #fff;
            padding: 16mm 20mm 20mm 20mm;
            box-shadow: 0 6px 25px rgba(0,0,0,0.12);
            border-radius: 2px;
            box-sizing: border-box;
        }

        /* Kop Surat Lebar Penuh Sesuai Teks Dokumen */
        .kop-container {
            width: 100%;
            margin-bottom: 14px;
            padding: 0;
            text-align: center;
        }

        .kop-image {
            width: 100%;
            height: auto;
            display: block;
            margin: 0;
            padding: 0;
        }

        /* Judul Dokumen */
        .doc-title-block {
            text-align: center;
            margin: 10px 0 16px;
            break-after: avoid;
            page-break-after: avoid;
        }

        .doc-title {
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .doc-number {
            font-size: 11.5pt;
            margin-top: 3px;
            font-weight: bold;
        }

        /* Paragraf & Text */
        p, .paragraph {
            text-align: justify;
            text-justify: inter-word;
            margin-bottom: 8px;
            line-height: 1.55;
            orphans: 3;
            widows: 3;
        }

        .section-lead {
            margin-top: 10px;
            margin-bottom: 4px;
        }

        /* Blok yang Tidak Boleh Terpotong (Keep Together) */
        .keep-together,
        .pasal-block,
        .parties-block,
        .menimbang-block,
        .signature-section {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .pasal-block {
            margin-top: 14px;
            margin-bottom: 14px;
        }

        .pasal-title {
            text-align: center;
            font-weight: bold;
            font-size: 11.5pt;
            margin-bottom: 2px;
            break-after: avoid;
            page-break-after: avoid;
        }

        .pasal-subtitle {
            text-align: center;
            font-weight: bold;
            font-size: 11.5pt;
            margin-bottom: 8px;
            break-after: avoid;
            page-break-after: avoid;
        }

        /* List Items */
        ol, ul {
            padding-left: 22px;
            margin-bottom: 8px;
        }

        li {
            page-break-inside: avoid;
            break-inside: avoid;
            line-height: 1.55;
            margin-bottom: 4px;
            text-align: justify;
            text-justify: inter-word;
        }

        /* Tanda Tangan */
        .signature-section {
            margin-top: 30px;
        }

        .signature-date {
            text-align: right;
            margin-bottom: 16px;
            padding-right: 20px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }

        .signature-table td {
            width: 50%;
            vertical-align: top;
        }

        .signature-role {
            font-weight: bold;
            font-size: 11.5pt;
        }

        .signature-space {
            height: 70px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            font-size: 11.5pt;
        }

        .signature-pos {
            font-size: 11pt;
            margin-top: 2px;
        }

        /* Mode Cetak */
        @media print {
            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 215mm;
            }

            .no-print, .no-print-bar {
                display: none !important;
            }

            .document-wrapper {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
        }
    </style>
</head>
<body>

{{-- Toolbar Atas di Layar Komputer --}}
<div class="no-print-bar no-print">
    <div class="info-tag">
        <span class="badge-f4">KERTAS F4</span>
        <strong>Kontrak Kerja:</strong> {{ $contract->employee_name }} &mdash; <span>No. {{ $contract->contract_number }}</span>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <div style="font-size: 11.5px; color: #cbd5e1; background: #1e293b; padding: 4px 10px; border-radius: 6px; border: 1px solid #334155; line-height: 1.4;">
            💡 <strong>Hilangkan Header/Footer:</strong> Di print dialog, klik <em>More settings</em> lalu <u>uncheck</u> <strong>"Headers and footers"</strong> agar URL/tanggal tidak muncul.
        </div>
        <a href="{{ route('super-admin.contracts.index') }}" class="btn-back">
            &larr; Kembali
        </a>
        <a href="{{ route('super-admin.contracts.edit', $contract) }}" class="btn-edit">
            &#9998; Edit Data
        </a>
        <button onclick="window.print()" class="btn-print">
            &#128438; Cetak / Simpan PDF
        </button>
    </div>
</div>

<div class="document-wrapper">

    {{-- KOP SURAT (Menyesuaikan Lebar Penuh Kertas) --}}
    <div class="kop-container">
        <img src="{{ $kopSrc }}" alt="Kop Surat Record" class="kop-image">
    </div>

    {{-- JUDUL DOKUMEN --}}
    <div class="doc-title-block">
        <div class="doc-title">KONTRAK KERJA</div>
        <div class="doc-number">Nomor: {{ $contract->contract_number }}</div>
    </div>

    {{-- PEMBUKA & PARA PIHAK --}}
    <div class="parties-block">
        <p><strong>ANTARA:</strong></p>
        <p>
            <strong>CV JAYA MANDIRI</strong>, sebuah perusahaan yang didirikan berdasarkan hukum Republik Indonesia, berkedudukan di Jln Kyai Tambak Deres No 32, Bulak, Surabaya, yang dalam hal ini diwakili oleh Record, yang bertindak dalam jabatannya selaku <strong>{{ strtoupper($contract->company_representative) }}</strong> selanjutnya disebut sebagai <strong>{{ strtoupper($contract->company_representative_position) }}</strong>
        </p>

        <p style="margin-top: 10px;"><strong>DAN:</strong></p>
        <p>
            <strong>{{ strtoupper($contract->employee_name) }}</strong> seorang individu yang bertempat tinggal di <strong>{{ strtoupper($contract->employee_address ?? '-') }}</strong> dengan Nomor Induk Kependudukan <strong>{{ $contract->employee_nik ?? '-' }}</strong> selanjutnya disebut sebagai <strong>{{ strtoupper($contract->employee_position) }}</strong>.
        </p>

        <p style="margin-top: 10px;">
            Perusahaan dan Pekerja secara bersama-sama disebut sebagai <strong>“Para Pihak”</strong> dan masing-masing disebut sebagai <strong>“Pihak”</strong>.
        </p>
    </div>

    {{-- MENIMBANG --}}
    <div class="menimbang-block" style="margin-top: 10px;">
        <p><strong>MENIMBANG:</strong></p>
        <ol style="list-style-type: lower-alpha; padding-left: 24px; margin-bottom: 10px;">
            <li>Bahwa Perusahaan membutuhkan tenaga kerja untuk melaksanakan kegiatan operasional di bidang ONLINE MARKETPLACE RECORDSHOES.</li>
            <li>Bahwa Pekerja memiliki keahlian dan kualifikasi yang dibutuhkan oleh Perusahaan untuk melaksanakan pekerjaan tersebut.</li>
            <li>Bahwa Para Pihak sepakat untuk mengadakan hubungan kerja berdasarkan syarat dan ketentuan yang diatur dalam Kontrak Kerja ini.</li>
        </ol>

        <p style="margin-top: 12px;"><strong>DENGAN DEMIKIAN, PARA PIHAK TELAH SEPAKAT DAN MENYETUJUI HAL-HAL SEBAGAI BERIKUT:</strong></p>
    </div>

    {{-- PASAL 1: JABATAN DAN LINGKUP PEKERJAAN --}}
    <div class="pasal-block">
        <div class="pasal-title">Pasal 1</div>
        <div class="pasal-subtitle">Jabatan dan Lingkup Pekerjaan</div>
        <ol>
            <li>Perusahaan mempekerjakan Pekerja sebagai <strong>{{ strtoupper($contract->employee_position) }}</strong>.</li>
            <li>Pekerja wajib melaksanakan tugas dan tanggung jawab sesuai dengan jabatan dan deskripsi pekerjaan yang diberikan oleh Perusahaan.</li>
            <li>Pekerja wajib mengikuti arahan, prosedur, serta kebijakan yang berlaku di Perusahaan.</li>
        </ol>
    </div>

    {{-- PASAL 2: JANGKA WAKTU KONTRAK --}}
    <div class="pasal-block">
        <div class="pasal-title">Pasal 2</div>
        <div class="pasal-subtitle">Jangka Waktu Kontrak</div>
        @php
            $startDate = \Carbon\Carbon::parse($contract->contract_start);
            $endDate = \Carbon\Carbon::parse($contract->contract_end);
        @endphp
        <ol>
            <li>Kontrak Kerja ini berlaku sejak tanggal <strong>{{ strtoupper($startDate->translatedFormat('d F Y')) }}</strong> sampai dengan tanggal <strong>{{ strtoupper($endDate->translatedFormat('d F Y')) }}</strong>.</li>
            <li>Perpanjangan kontrak dapat dilakukan berdasarkan kesepakatan Para Pihak dengan mempertimbangkan kebutuhan dan kebijakan Perusahaan.</li>
            <li>Kontrak dapat berakhir sebelum jangka waktu sebagaimana dimaksud pada ayat (1) berdasarkan ketentuan peraturan perundang-undangan dan/atau kesepakatan Para Pihak.</li>
        </ol>
    </div>

    {{-- PASAL 3: WAKTU DAN TEMPAT KERJA --}}
    <div class="pasal-block">
        <div class="pasal-title">Pasal 3</div>
        <div class="pasal-subtitle">Waktu dan Tempat Kerja</div>
        <ol>
            <li>Pekerja melaksanakan pekerjaan selama 8 jam per hari dan 5 hari dalam seminggu.</li>
            <li>Hari dan waktu kerja ditetapkan sebagai berikut:
                <ul style="list-style-type: disc; padding-left: 20px; margin-top: 3px;">
                    <li>Hari kerja: [Senin–Jumat / Sabtu jika urgent ]</li>
                    <li>Jam kerja: [08.30–17.00 WIB]</li>
                </ul>
            </li>
            <li>Tempat kerja Pekerja berada di KYAI TAMBAK DERES 32 BULAK SURABAYA.</li>
            <li>Perusahaan dapat menyesuaikan waktu dan/atau tempat kerja sesuai dengan kebutuhan operasional dengan pemberitahuan kepada Pekerja.</li>
        </ol>
    </div>

    {{-- PASAL 4: GAJI DAN TUNJANGAN --}}
    <div class="pasal-block">
        <div class="pasal-title">Pasal 4</div>
        <div class="pasal-subtitle">Gaji dan Tunjangan</div>
        <ol>
            <li>Pekerja berhak menerima gaji sebesar <strong>Rp {{ number_format($contract->basic_salary, 0, ',', '.') }}</strong> per bulan.</li>
            <li>Selain gaji pokok, Pekerja dapat menerima tunjangan sesuai dengan kebijakan Perusahaan, antara lain:
                <ul style="list-style-type: disc; padding-left: 20px; margin-top: 3px;">
                    <li>Tunjangan makan: Rp {{ number_format($contract->meal_allowance, 0, ',', '.') }}</li>
                    <li>Tunjangan transportasi: Rp {{ number_format($contract->transport_allowance, 0, ',', '.') }}</li>
                    @if($contract->other_allowance > 0 || $contract->other_allowance_note)
                    <li>Tunjangan lainnya: Rp {{ number_format($contract->other_allowance, 0, ',', '.') }} ({{ $contract->other_allowance_note ?? 'DIBAYAR PER 2 BULAN PERIODE' }})</li>
                    @endif
                </ul>
            </li>
            <li>Pembayaran gaji dan tunjangan dilakukan setiap tanggal 5 AWAL BULAN BERIKUTNYA melalui transfer ke rekening Pekerja atau metode pembayaran lain yang ditentukan oleh Perusahaan.</li>
            <li>Pajak dan kewajiban lainnya yang menjadi tanggung jawab Pekerja akan diperhitungkan sesuai dengan ketentuan peraturan perundang-undangan yang berlaku.</li>
            <li>Pekerja berhak mengajukan negosiasi ulang terkait besaran gaji yang diberikan saat ini. Keputusan kenaikan gaji sepenuhnya menjadi kewenangan Perusahaan dengan mempertimbangkan alasan-alasan yang diajukan oleh Pekerja, seperti pencapaian target, evaluasi performa, atau penyesuaian beban kerja.</li>
        </ol>
    </div>

    {{-- PASAL 5: HAK DAN KEWAJIBAN PERUSAHAAN --}}
    <div class="pasal-block">
        <div class="pasal-title">Pasal 5</div>
        <div class="pasal-subtitle">Hak dan Kewajiban Perusahaan</div>
        <p><strong>Hak Perusahaan:</strong></p>
        <ol>
            <li>Mengatur dan mengawasi pelaksanaan pekerjaan Pekerja.</li>
            <li>Mengevaluasi kinerja Pekerja.</li>
            <li>Memberikan tugas dan tanggung jawab sesuai dengan posisi dan kemampuan Pekerja.</li>
            <li>Memberikan teguran atau tindakan sesuai dengan peraturan perusahaan apabila Pekerja melakukan pelanggaran.</li>
        </ol>
        <p style="margin-top: 6px;"><strong>Kewajiban Perusahaan:</strong></p>
        <ol>
            <li>Membayar gaji dan tunjangan Pekerja sesuai dengan ketentuan Kontrak Kerja.</li>
            <li>Memberikan fasilitas kerja yang diperlukan untuk menunjang pekerjaan Pekerja.</li>
            <li>Memenuhi hak-hak Pekerja sesuai dengan ketentuan peraturan perundang-undangan yang berlaku.</li>
        </ol>
    </div>

    {{-- PASAL 6: HAK DAN KEWAJIBAN PEKERJA --}}
    <div class="pasal-block">
        <div class="pasal-title">Pasal 6</div>
        <div class="pasal-subtitle">Hak dan Kewajiban Pekerja</div>
        <p><strong>Hak Pekerja:</strong></p>
        <ol>
            <li>Menerima gaji dan komisi per-2 bulan sesuai dengan ketentuan Kontrak Kerja.</li>
            <li>Mendapatkan hak lainnya sesuai dengan peraturan perusahaan dan peraturan perundang-undangan yang berlaku.</li>
            <li>Mendapatkan lingkungan kerja yang aman dan layak.</li>
            <li>Berhak untuk mengajukan negosiasi penyesuaian atau kenaikan gaji dari nominal yang diterima saat ini. Pengajuan tersebut wajib menyertakan alasan dan dasar yang objektif (seperti evaluasi kinerja yang baik, pencapaian target, atau peningkatan beban tanggung jawab) sebagai bahan pertimbangan bagi Perusahaan.</li>
        </ol>
        <p style="margin-top: 6px;"><strong>Kewajiban Pekerja:</strong></p>
        <ol>
            <li>Melaksanakan pekerjaan dengan penuh tanggung jawab.</li>
            <li>Mematuhi peraturan dan tata tertib Perusahaan.</li>
            <li>Menjaga nama baik dan kepentingan Perusahaan.</li>
            <li>Menjaga kerahasiaan data dan informasi milik Perusahaan.</li>
            <li>Menjaga dan menggunakan fasilitas Perusahaan dengan baik.</li>
        </ol>
    </div>

    {{-- PASAL 7: DISIPLIN DAN PELANGGARAN --}}
    <div class="pasal-block">
        <div class="pasal-title">Pasal 7</div>
        <div class="pasal-subtitle">Disiplin dan Pelanggaran</div>
        <ol>
            <li>Pekerja wajib menaati seluruh peraturan, tata tertib, dan kebijakan yang berlaku di Perusahaan.</li>
            <li>Pelanggaran terhadap ketentuan Perusahaan dapat dikenakan teguran atau tindakan disipliner sesuai dengan peraturan perusahaan dan ketentuan hukum yang berlaku.</li>
            <li>Pekerja dilarang menggunakan, menyebarkan, atau memberikan informasi rahasia Perusahaan kepada pihak lain tanpa izin dari Perusahaan.</li>
        </ol>
    </div>

    {{-- PASAL 8: PENGAKHIRAN KONTRAK --}}
    <div class="pasal-block">
        <div class="pasal-title">Pasal 8</div>
        <div class="pasal-subtitle">Pengakhiran Kontrak</div>
        <ol>
            <li>Kontrak Kerja berakhir apabila jangka waktu kontrak telah selesai sesuai dengan ketentuan Pasal 2.</li>
            <li>Kontrak dapat diakhiri sebelum jangka waktunya berdasarkan kesepakatan Para Pihak atau alasan lain yang diperbolehkan berdasarkan ketentuan peraturan perundang-undangan yang berlaku.</li>
            <li>Dalam hal terjadi pengakhiran hubungan kerja, Para Pihak wajib menyelesaikan seluruh hak dan kewajiban masing-masing.</li>
            <li>Pekerja yang bermaksud mengundurkan diri dari Perusahaan wajib menyampaikan surat pengunduran diri secara tertulis paling lambat 30 (tiga puluh) hari kalender sebelum tanggal efektif pengunduran diri.</li>
            <li>Apabila Pekerja mengundurkan diri di tengah masa kontrak tanpa memberikan pemberitahuan tertulis sekurang-kurangnya 30 (tiga puluh) hari kalender sebelumnya (H-30) sebagaimana dimaksud pada ayat 4, maka Perusahaan berhak menjatuhkan penalti berupa tidak dibayarkannya gaji Pekerja pada periode berjalan tersebut.</li>
        </ol>
    </div>

    {{-- PASAL 9: PENYELESAIAN PERSELISIHAN --}}
    <div class="pasal-block">
        <div class="pasal-title">Pasal 9</div>
        <div class="pasal-subtitle">Penyelesaian Perselisihan</div>
        <ol>
            <li>Apabila terjadi perselisihan yang berkaitan dengan pelaksanaan Kontrak Kerja ini, Para Pihak sepakat untuk menyelesaikannya terlebih dahulu secara musyawarah untuk mufakat.</li>
            <li>Apabila penyelesaian secara musyawarah tidak mencapai kesepakatan, maka penyelesaian dilakukan sesuai dengan ketentuan peraturan perundang-undangan yang berlaku.</li>
        </ol>
    </div>

    {{-- PASAL 10: PENUTUP --}}
    <div class="pasal-block">
        <div class="pasal-title">Pasal 10</div>
        <div class="pasal-subtitle">Penutup</div>
        <ol>
            <li>Kontrak Kerja ini dibuat berdasarkan kesepakatan Para Pihak tanpa adanya paksaan dari pihak manapun.</li>
            <li>Hal-hal yang belum diatur dalam Kontrak Kerja ini akan diatur kemudian berdasarkan kesepakatan Para Pihak dan ketentuan peraturan perundang-undangan yang berlaku.</li>
            <li>Kontrak Kerja ini dibuat dalam 2 (dua) rangkap, masing-masing mempunyai kekuatan hukum yang sama dan dipegang oleh masing-masing Pihak.</li>
        </ol>
    </div>

    {{-- BAGIAN TANDA TANGAN (TIDAK BOLEH TERPOTONG) --}}
    <div class="signature-section">
        <div class="signature-date">
            {{ $contract->signed_city }}, {{ \Carbon\Carbon::parse($contract->signed_date)->translatedFormat('d F Y') }}
        </div>
        <table class="signature-table">
            <tr>
                <td class="signature-role">PIHAK PERUSAHAAN</td>
                <td class="signature-role">PIHAK PEKERJA</td>
            </tr>
            <tr>
                <td class="signature-space"></td>
                <td class="signature-space"></td>
            </tr>
            <tr>
                <td>
                    <div class="signature-name">{{ strtoupper($contract->company_representative) }}</div>
                    <div class="signature-pos">{{ $contract->company_representative_position }}</div>
                </td>
                <td>
                    <div class="signature-name">{{ strtoupper($contract->employee_name) }}</div>
                    <div class="signature-pos">{{ $contract->employee_position }}</div>
                </td>
            </tr>
        </table>
    </div>

</div>

</body>
</html>
