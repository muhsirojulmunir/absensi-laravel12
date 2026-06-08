<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private $apiKey;
    private $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    /**
     * Generate pesan pengingat absen menggunakan Gemini AI
     *
     * @param string $employeeName Nama karyawan
     * @param string $division Divisi karyawan
     * @param string $type Tipe pengingat: 'clock_in', 'clock_out', 'clock_out_8jam'
     * @return string Pesan yang dihasilkan AI
     */
    public function generateAttendanceReminder(string $employeeName, string $division, string $type): string
    {
        $prompt = $this->buildPrompt($employeeName, $division, $type);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.9,
                    'maxOutputTokens' => 150,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if ($text) {
                    return trim($text);
                }
            }

            Log::error('Gemini API error: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('Gemini API exception: ' . $e->getMessage());
        }

        // Fallback pesan statis jika AI gagal
        return $this->fallbackMessage($employeeName, $type);
    }

    /**
     * Bangun prompt untuk Gemini berdasarkan tipe pengingat
     */
    private function buildPrompt(string $name, string $division, string $type): string
    {
        $baseInstruction = "Kamu adalah asisten HR bernama 'JMN Matrix AI' di sebuah perusahaan. Buatkan SATU pesan notifikasi singkat (maksimal 2 kalimat) dalam Bahasa Indonesia yang ramah, profesional, dan sedikit santai. Jangan gunakan emoji berlebihan (maks 1-2 emoji). Jangan pakai tanda kutip di awal/akhir pesan. Langsung tulis pesannya saja.";

        switch ($type) {
            case 'clock_in':
                return "$baseInstruction\n\nBuatkan pesan pengingat ABSEN MASUK (Clock In) pagi untuk karyawan bernama '$name' di divisi '$division'. Berikan semangat untuk memulai hari kerja. Ingatkan bahwa jam masuk kantor adalah pukul 08:00 WIB.";

            case 'clock_out':
                return "$baseInstruction\n\nBuatkan pesan pengingat ABSEN PULANG (Clock Out) sore untuk karyawan bernama '$name' di divisi '$division'. Apresiasi kerja kerasnya hari ini dan ingatkan untuk segera melakukan absen pulang karena jam pulang kantor sudah tiba (17:00 WIB).";

            case 'clock_out_8jam':
                return "$baseInstruction\n\nBuatkan pesan pengingat ABSEN PULANG (Clock Out) untuk karyawan bernama '$name' di divisi '$division' (Live Streaming). Ingatkan bahwa dia sudah bekerja selama 8 jam dan waktunya untuk istirahat dan segera melakukan absen pulang.";

            default:
                return "$baseInstruction\n\nBuatkan pesan pengingat umum untuk karyawan bernama '$name' agar segera melakukan absen.";
        }
    }

    /**
     * Pesan statis sebagai fallback jika Gemini gagal
     */
    private function fallbackMessage(string $name, string $type): string
    {
        switch ($type) {
            case 'clock_in':
                return "Selamat pagi, $name! ☀️ Jangan lupa absen masuk ya. Jam kerja dimulai pukul 08:00 WIB. Semangat!";
            case 'clock_out':
                return "Hai $name! Jam pulang sudah tiba (17:00 WIB). Jangan lupa absen pulang ya. Terima kasih atas kerja kerasnya hari ini! 👋";
            case 'clock_out_8jam':
                return "Hai $name! Kamu sudah bekerja selama 8 jam. Waktunya istirahat! Jangan lupa absen pulang ya. 💪";
            default:
                return "Hai $name! Jangan lupa untuk melakukan absen. Terima kasih!";
        }
    }

    /**
     * Generate Laporan Kinerja Bulanan menggunakan Gemini AI
     */
    public function generatePerformanceReport($user, $month, $year, $attendance, $sales)
    {
        $division = $user->division->name ?? 'Kantor';
        
        $prompt = "Kamu adalah HR Manager & Analis Kinerja 'JMN Matrix AI'. Buatkan evaluasi kinerja bulanan untuk karyawan berikut:\n\n";
        $prompt .= "- Nama: {$user->name}\n";
        $prompt .= "- Divisi: {$division}\n";
        $prompt .= "- Periode: Bulan {$month} Tahun {$year}\n\n";
        
        $prompt .= "DATA ABSENSI:\n";
        $prompt .= "- Hadir Tepat Waktu: " . ($attendance['total_hadir'] - $attendance['total_telat']) . " hari\n";
        $prompt .= "- Terlambat: {$attendance['total_telat']} hari\n";
        $prompt .= "- Pulang Cepat: {$attendance['total_pulang_cepat']} hari\n";
        $prompt .= "- Izin/Sakit: {$attendance['total_izin_sakit']} hari\n";
        
        if ($sales) {
            $prompt .= "\nDATA PENJUALAN:\n";
            $prompt .= "- Total Item Terjual: {$sales['total_items_sold']} pcs\n";
            $prompt .= "- Total Omset Pendapatan: Rp " . number_format($sales['total_revenue'], 0, ',', '.') . "\n";
            $prompt .= "- Total Transaksi: {$sales['total_transactions']}\n";
        }
        
        $prompt .= "\nINSTRUKSI:\n";
        $prompt .= "1. Tentukan PREDIKAT Kinerja secara tegas. Pilih HANYA SATU dari: 'Sangat Baik', 'Baik', 'Cukup', atau 'Kurang'.\n";
        $prompt .= "2. Buat RANGKUMAN EVALUASI (Feedback) sebanyak 2-3 kalimat yang natural, menyoroti pola positif (misal konsisten hadir) dan pola negatif (misal sering telat/pulang cepat).\n";
        $prompt .= "3. Berikan SARAN & REMINDER yang brilian, memotivasi, dan spesifik untuk perbaikan di bulan depan.\n";
        $prompt .= "Format balasan MU HARUS persis seperti JSON berikut (TANPA blok kode ```json, langsung kembalikan raw JSON string):\n";
        $prompt .= '{"predicate": "Pilih Predikat", "feedback": "Teks evaluasi", "recommendation": "Teks saran"}';

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 500,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
                if ($text) {
                    $text = trim($text);
                    // Bersihkan backticks jika AI nakal mengirimkan format markdown
                    $text = str_replace(['```json', '```'], '', $text);
                    
                    $json = json_decode(trim($text), true);
                    if (json_last_error() === JSON_ERROR_NONE && isset($json['predicate'], $json['feedback'], $json['recommendation'])) {
                        return $json;
                    }
                }
            }
            \Illuminate\Support\Facades\Log::error('Gemini Performance API error: ' . $response->body());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gemini Performance API exception: ' . $e->getMessage());
        }

        // Fallback jika gagal parse JSON atau API error
        return [
            'predicate' => 'Menunggu Evaluasi',
            'feedback' => 'Gagal mengambil evaluasi otomatis. Silakan coba generate ulang.',
            'recommendation' => '-',
        ];
    }
}
