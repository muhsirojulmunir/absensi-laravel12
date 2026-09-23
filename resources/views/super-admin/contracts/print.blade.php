<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontrak Kerja - {{ $contract->employee_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11.5pt;
            line-height: 1.5;
            color: #000;
            background-color: #f1f5f9;
        }

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
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 14px;
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
            background: #2563eb;
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

        .document-wrapper {
            max-width: 210mm;
            margin: 64px auto 40px;
            background: #fff;
            padding: 22mm 24mm 22mm 24mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-radius: 4px;
        }

        .doc-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .doc-logo {
            max-height: 55px;
            margin-bottom: 12px;
        }

        .doc-title {
            font-size: 15pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .doc-number {
            font-size: 11.5pt;
            margin-top: 4px;
            font-weight: normal;
        }

        p, .paragraph {
            text-align: justify;
            text-justify: inter-word;
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .parties-block {
            margin: 12px 0 16px;
        }

        .section-header {
            font-weight: bold;
            margin: 14px 0 4px;
        }

        .pasal-title {
            text-align: center;
            font-weight: bold;
            margin: 18px 0 4px;
            font-size: 12pt;
        }

        .pasal-subtitle {
            text-align: center;
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 11.5pt;
        }

        ol, ul {
            padding-left: 24px;
            margin-bottom: 10px;
        }

        li {
            line-height: 1.6;
            margin-bottom: 4px;
            text-align: justify;
        }

        .sub-list {
            list-style-type: none;
            padding-left: 18px;
            margin: 4px 0 8px;
        }

        .sub-list li {
            position: relative;
            padding-left: 18px;
        }
        .sub-list li::before {
            content: "•";
            position: absolute;
            left: 0;
            font-weight: bold;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 10px;
        }

        .table-data td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .signature-section {
            margin-top: 36px;
            page-break-inside: avoid;
        }

        .signature-date {
            text-align: right;
            margin-bottom: 20px;
            padding-right: 40px;
        }

        .signature-table {
            width: 100%;
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
            height: 75px;
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

        @media print {
            body {
                background: #fff;
                margin: 0;
                padding: 0;
            }

            .no-print, .no-print-bar {
                display: none !important;
            }

            .document-wrapper {
                margin: 0;
                padding: 15mm 20mm;
                max-width: none;
                width: 100%;
                box-shadow: none;
                border-radius: 0;
            }

            @page {
                size: A4;
                margin: 10mm 15mm;
            }
        }
    </style>
</head>
<body>

{{-- Top Toolbar for Screen View --}}
<div class="no-print-bar no-print">
    <div>
        <strong>Kontrak Kerja:</strong> {{ $contract->employee_name }} ({{ $contract->contract_number }})
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('super-admin.contracts.index') }}" class="btn-back">
            &larr; Daftar Kontrak
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

    {{-- Header / Logo --}}
    <div class="doc-header">
        @if(file_exists(public_path('images/kop_surat_record.png')))
            <img src="{{ asset('images/kop_surat_record.png') }}" alt="Record Logo" class="doc-logo">
        @elseif(file_exists(public_path('images/logo_record.png')))
            <img src="{{ asset('images/logo_record.png') }}" alt="Record Logo" class="doc-logo">
        @endif
        <div class="doc-title">KONTRAK KERJA</div>
        <div class="doc-number">Nomor: {{ $contract->contract_number }}</div>
    </div>

    {{-- Pembuka Para Pihak --}}
    <p><strong>ANTARA:</strong></p>
    <p>
        <strong>CV JAYA MANDIRI</strong>, sebuah perusahaan yang didirikan berdasarkan hukum Republik Indonesia, berkedudukan di Jln Kyai Tambak Deres No 32, Bulak, Surabaya, yang dalam hal ini diwakili oleh Record, yang bertindak dalam jabatannya selaku <strong>{{ strtoupper($contract->company_representative) }}</strong> selanjutnya disebut sebagai <strong>{{ strtoupper($contract->company_representative_position) }}</strong>
    </p>

    <p><strong>DAN:</strong></p>
    <p>
        <strong>{{ strtoupper($contract->employee_name) }}</strong> seorang individu yang bertempat tinggal di <strong>{{ strtoupper($contract->employee_address ?? '-') }}</strong> dengan Nomor Induk Kependudukan <strong>{{ $contract->employee_nik ?? '-' }}</strong> selanjutnya disebut sebagai <strong>{{ strtoupper($contract->employee_position) }}</strong>.
    </p>

    <p>
        Perusahaan dan Pekerja secara bersama-sama disebut sebagai <strong>“Para Pihak”</strong> dan masing-masing disebut sebagai <strong>“Pihak”</strong>.
    </p>

    {{-- Menimbang --}}
    <p><strong>MENIMBANG:</strong></p>
    <ol style="list-style-type: lower-alpha; padding-left: 24px; margin-bottom: 12px;">
        <li>Bahwa Perusahaan membutuhkan tenaga kerja untuk melaksanakan kegiatan operasional di bidang ONLINE MARKETPLACE RECORDSHOES.</li>
        <li>Bahwa Pekerja memiliki keahlian dan kualifikasi yang dibutuhkan oleh Perusahaan untuk melaksanakan pekerjaan tersebut.</li>
        <li>Bahwa Para Pihak sepakat untuk mengadakan hubungan kerja berdasarkan syarat dan ketentuan yang diatur dalam Kontrak Kerja ini.</li>
    </ol>

    <p style="margin-top: 14px;"><strong>DENGAN DEMIKIAN, PARA PIHAK TELAH SEPAKAT DAN MENYETUJUI HAL-HAL SEBAGAI BERIKUT:</strong></p>

    {{-- Pasal 1 --}}
    <div class="pasal-title">Pasal 1</div>
    <div class="pasal-subtitle">Jabatan dan Lingkup Pekerjaan</div>
    <ol>
        <li>Perusahaan mempekerjakan Pekerja sebagai <strong>{{ strtoupper($contract->employee_position) }}</strong>.</li>
        <li>Pekerja wajib melaksanakan tugas dan tanggung jawab sesuai dengan jabatan dan deskripsi pekerjaan yang diberikan oleh Perusahaan.</li>
        <li>Pekerja wajib mengikuti arahan, prosedur, serta kebijakan yang berlaku di Perusahaan.</li>
    </ol>

    {{-- Pasal 2 --}}
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

    {{-- Pasal 3 --}}
    <div class="pasal-title">Pasal 3</div>
    <div class="pasal-subtitle">Waktu dan Tempat Kerja</div>
    <ol>
        <li>Pekerja melaksanakan pekerjaan selama 8 jam per hari dan 5 hari dalam seminggu.</li>
        <li>Hari dan waktu kerja ditetapkan sebagai berikut:
            <ul style="list-style-type: disc; padding-left: 20px; margin-top: 4px;">
                <li>Hari kerja: [Senin–Jumat / Sabtu jika urgent ]</li>
                <li>Jam kerja: [08.30–17.00 WIB]</li>
            </ul>
        </li>
        <li>Tempat kerja Pekerja berada di KYAI TAMBAK DERES 32 BULAK SURABAYA.</li>
        <li>Perusahaan dapat menyesuaikan waktu dan/atau tempat kerja sesuai dengan kebutuhan operasional dengan pemberitahuan kepada Pekerja.</li>
    </ol>

    {{-- Pasal 4 --}}
    <div class="pasal-title">Pasal 4</div>
    <div class="pasal-subtitle">Gaji dan Tunjangan</div>
    <ol>
        <li>Pekerja berhak menerima gaji sebesar <strong>Rp {{ number_format($contract->basic_salary, 0, ',', '.') }}</strong> per bulan.</li>
        <li>Selain gaji pokok, Pekerja dapat menerima tunjangan sesuai dengan kebijakan Perusahaan, antara lain:
            <ul style="list-style-type: disc; padding-left: 20px; margin-top: 4px;">
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

    {{-- Pasal 5 --}}
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

    {{-- Pasal 6 --}}
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

    {{-- Pasal 7 --}}
    <div class="pasal-title">Pasal 7</div>
    <div class="pasal-subtitle">Disiplin dan Pelanggaran</div>
    <ol>
        <li>Pekerja wajib menaati seluruh peraturan, tata tertib, dan kebijakan yang berlaku di Perusahaan.</li>
        <li>Pelanggaran terhadap ketentuan Perusahaan dapat dikenakan teguran atau tindakan disipliner sesuai dengan peraturan perusahaan dan ketentuan hukum yang berlaku.</li>
        <li>Pekerja dilarang menggunakan, menyebarkan, atau memberikan informasi rahasia Perusahaan kepada pihak lain tanpa izin dari Perusahaan.</li>
    </ol>

    {{-- Pasal 8 --}}
    <div class="pasal-title">Pasal 8</div>
    <div class="pasal-subtitle">Pengakhiran Kontrak</div>
    <ol>
        <li>Kontrak Kerja berakhir apabila jangka waktu kontrak telah selesai sesuai dengan ketentuan Pasal 2.</li>
        <li>Kontrak dapat diakhiri sebelum jangka waktunya berdasarkan kesepakatan Para Pihak atau alasan lain yang diperbolehkan berdasarkan ketentuan peraturan perundang-undangan yang berlaku.</li>
        <li>Dalam hal terjadi pengakhiran hubungan kerja, Para Pihak wajib menyelesaikan seluruh hak dan kewajiban masing-masing.</li>
        <li>Pekerja yang bermaksud mengundurkan diri dari Perusahaan wajib menyampaikan surat pengunduran diri secara tertulis paling lambat 30 (tiga puluh) hari kalender sebelum tanggal efektif pengunduran diri.</li>
        <li>Apabila Pekerja mengundurkan diri di tengah masa kontrak tanpa memberikan pemberitahuan tertulis sekurang-kurangnya 30 (tiga puluh) hari kalender sebelumnya (H-30) sebagaimana dimaksud pada ayat 4, maka Perusahaan berhak menjatuhkan penalti berupa tidak dibayarkannya gaji Pekerja pada periode berjalan tersebut.</li>
    </ol>

    {{-- Pasal 9 --}}
    <div class="pasal-title">Pasal 9</div>
    <div class="pasal-subtitle">Penyelesaian Perselisihan</div>
    <ol>
        <li>Apabila terjadi perselisihan yang berkaitan dengan pelaksanaan Kontrak Kerja ini, Para Pihak sepakat untuk menyelesaikannya terlebih dahulu secara musyawarah untuk mufakat.</li>
        <li>Apabila penyelesaian secara musyawarah tidak mencapai kesepakatan, maka penyelesaian dilakukan sesuai dengan ketentuan peraturan perundang-undangan yang berlaku.</li>
    </ol>

    {{-- Pasal 10 --}}
    <div class="pasal-title">Pasal 10</div>
    <div class="pasal-subtitle">Penutup</div>
    <ol>
        <li>Kontrak Kerja ini dibuat berdasarkan kesepakatan Para Pihak tanpa adanya paksaan dari pihak manapun.</li>
        <li>Hal-hal yang belum diatur dalam Kontrak Kerja ini akan diatur kemudian berdasarkan kesepakatan Para Pihak dan ketentuan peraturan perundang-undangan yang berlaku.</li>
        <li>Kontrak Kerja ini dibuat dalam 2 (dua) rangkap, masing-masing mempunyai kekuatan hukum yang sama dan dipegang oleh masing-masing Pihak.</li>
    </ol>

    {{-- Tanda Tangan --}}
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
