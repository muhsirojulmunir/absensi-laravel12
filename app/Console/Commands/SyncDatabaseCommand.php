<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class SyncDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data Karyawan/User lokal ke Live Server (InfinityFree)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai proses sinkronisasi database...');

        $liveUrl = config('app.live_url', env('LIVE_URL'));
        $secretKey = config('app.sync_secret_key', env('SYNC_SECRET_KEY'));

        if (!$liveUrl || !$secretKey) {
            $this->error('Error: LIVE_URL atau SYNC_SECRET_KEY belum diatur di file .env');
            return 1;
        }

        $endpoint = rtrim($liveUrl, '/') . '/api/sync-users';
        
        $this->info('Mengambil data pengguna lokal...');
        // Only fetch required fields to avoid sending unnecessary data
        $users = User::all()->toArray();
        $userCount = count($users);

        if ($userCount === 0) {
            $this->warn('Tidak ada data pengguna untuk disinkronkan.');
            return 0;
        }

        $this->info("Ditemukan {$userCount} pengguna. Mengirim ke Live Server ({$endpoint})...");

        try {
            $response = Http::withHeaders([
                'X-Sync-Key' => $secretKey,
                'Accept' => 'application/json',
            ])->post($endpoint, [
                'users' => $users
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $synced = $result['synced_count'] ?? 0;
                $this->info("✅ Sukses! Berhasil menyinkronkan {$synced} data pengguna ke Live Server.");
                return 0;
            } else {
                $this->error("Gagal sinkronisasi. Server merespon dengan status: " . $response->status());
                $this->error("Pesan: " . $response->body());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Terjadi kesalahan saat menghubungi server live: " . $e->getMessage());
            return 1;
        }
    }
}
