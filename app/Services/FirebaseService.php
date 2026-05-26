<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;

class FirebaseService
{
    private $projectId;
    private $credentialsPath;

    public function __construct()
    {
        $this->credentialsPath = storage_path('app/firebase-credentials.json');
        $this->projectId = env('FIREBASE_PROJECT_ID', 'jmn-matrix');
    }

    /**
     * Dapatkan OAuth2 Access Token dari Service Account
     */
    private function getAccessToken(): ?string
    {
        try {
            if (!file_exists($this->credentialsPath)) {
                Log::error('Firebase credentials file not found: ' . $this->credentialsPath);
                return null;
            }

            $credentials = json_decode(file_get_contents($this->credentialsPath), true);

            if (!$credentials || empty($credentials['private_key']) || empty($credentials['client_email'])) {
                Log::error('Firebase credentials are invalid or incomplete.');
                return null;
            }

            $now = time();
            $payload = [
                'iss' => $credentials['client_email'],
                'sub' => $credentials['client_email'],
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            ];

            $jwt = JWT::encode($payload, $credentials['private_key'], 'RS256');

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('Failed to get Firebase access token: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Firebase getAccessToken error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Kirim Push Notification ke satu device via FCM v1 API
     */
    public function sendNotification(string $fcmToken, string $title, string $body): bool
    {
        try {
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                Log::error('Cannot send notification: no access token.');
                return false;
            }

            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

            $response = Http::withToken($accessToken)
                ->post($url, [
                    'message' => [
                        'token' => $fcmToken,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'webpush' => [
                            'notification' => [
                                'icon' => '/favicon.ico',
                                'badge' => '/favicon.ico',
                                'vibrate' => [200, 100, 200],
                                'requireInteraction' => true,
                            ],
                            'fcm_options' => [
                                'link' => url('/karyawan/attendance'),
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                Log::info("FCM notification sent to token: " . substr($fcmToken, 0, 20) . '...');
                return true;
            }

            Log::error('FCM send failed: ' . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('FCM sendNotification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim notifikasi ke banyak device sekaligus
     */
    public function sendBulkNotifications(array $tokens, string $title, string $body): int
    {
        $successCount = 0;
        foreach ($tokens as $token) {
            if ($this->sendNotification($token, $title, $body)) {
                $successCount++;
            }
        }
        return $successCount;
    }
}
