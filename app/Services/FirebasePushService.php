<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class FirebasePushService
{
    private string $projectId;
    private string $serviceAccountPath;
    private array $_data;
    private ?string $accessToken = null;

    public function __construct()
    {
        $this->projectId = config('firebase.project_id');
        $this->serviceAccountPath = config('firebase.service_account_path');
        $this->_data = ['action' => 'open_feature', 'feature_id' => 'new_dashboard'];
    }

    /**
     * Envoie une notification à tous les utilisateurs via topic
     */
    public function sendToAllUsers(string $title, string $body, array $data = []): array
    {
        $data = (empty($data) || is_null($data)) ? $this->_data : $data;
        return $this->sendToTopic('all_users', $title, $body, $data);
    }

    /**
     * Envoie une notification à un topic spécifique
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): array
    {
        try {
            $message = [
                'message' => [
                    'topic' => $topic,
                    'notification' => [
                        'title' => $title,
                        'body' => $body
                    ],
                    'data' => $this->convertDataToStrings($data),
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'channel_id' => 'default',
                            //'icon' => 'ic_notification',
                            //'color' => '#FF6B6B'
                        ]
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'alert' => [
                                    'title' => $title,
                                    'body' => $body
                                ],
                                'badge' => 1,
                                'sound' => 'default'
                            ]
                        ]
                    ]
                ]
            ];

            $result = $this->sendMessage($message);

            // Log pour debug
            Log::info('Firebase notification sent to topic', [
                'topic' => $topic,
                'title' => $title,
                'success' => $result['success']
            ]);

            return $result;

        } catch (Exception $e) {
            Log::error('Firebase notification error', [
                'error' => $e->getMessage(),
                'topic' => $topic
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Envoie une notification à un token spécifique
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): array
    {
        $title = (empty($title)) ? "New Message from Aramisc" : $title;
        $data = (empty($data) || is_null($data)) ? $this->_data : $data;
        try {
            $message = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body
                    ],
                    'data' => $data,
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'channel_id' => 'default'
                        ]
                    ]
                ]
            ];

            $result = $this->sendMessage($message);
            return $result;

        } catch (Exception $e) {
            Log::error('Firebase notification error', [
                'error' => $e->getMessage(),
                'token' => substr($token, 0, 20) . '...'
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Envoie une notification à plusieurs tokens
     */
    public function sendToMultipleTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        $results = [];
        $successful = 0;
        $failed = 0;

        foreach ($tokens as $token) {
            $result = $this->sendToToken($token, $title, $body, $data);
            $results[] = $result;

            if ($result['success']) {
                $successful++;
            } else {
                $failed++;
            }

            // Petite pause pour éviter de surcharger l'API
            usleep(100000); // 100ms
        }

        Log::info('Firebase bulk notification completed', [
            'total' => count($tokens),
            'successful' => $successful,
            'failed' => $failed
        ]);

        return [
            'success' => $successful > 0,
            'total' => count($tokens),
            'successful' => $successful,
            'failed' => $failed,
            'results' => $results
        ];
    }

    /**
     * Envoie une notification à tous les utilisateurs de la DB
     */
    public function sendToAllUsersFromDatabase(string $title, string $body, array $data = []): array
    {
        $data = (empty($data) || is_null($data)) ? $this->_data : $data;
        // Récupère tous les tokens FCM depuis la base de données

        $tokens = User::whereNotNull('device_token')
            ->where('device_token', '!=', '')
            ->pluck('device_token')
            ->toArray();

        if (empty($tokens)) {
            Log::warning('No FCM tokens found in database');
            return [
                'success' => false,
                'error' => 'Aucun token FCM trouvé dans la base de données'
            ];
        }

        return $this->sendToMultipleTokens($tokens, $title, $body, $data);
    }

    /**
     * Génère un token d'accès OAuth2
     */
    private function getAccessToken(): ?string
    {
        // Cache le token pendant 50 minutes (il expire après 1h)
        return Cache::remember('firebase_access_token', 50 * 60, function () {
            return $this->generateAccessToken();
        });
    }

    /**
     * Génère un nouveau token d'accès
     */
    private function generateAccessToken(): ?string
    {
        try {
            if (!file_exists($this->serviceAccountPath)) {
                throw new Exception("Service account file not found: {$this->serviceAccountPath}");
            }

            $serviceAccount = json_decode(file_get_contents($this->serviceAccountPath), true);

            if (!$serviceAccount) {
                throw new Exception("Invalid service account file");
            }

            // Crée le JWT
            $header = json_encode(['typ' => 'JWT', 'alg' => 'RS256']);
            $now = time();
            $payload = json_encode([
                'iss' => $serviceAccount['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now
            ]);

            $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
            $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

            $signature = '';
            openssl_sign($base64Header . '.' . $base64Payload, $signature, $serviceAccount['private_key'], 'SHA256');
            $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

            $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;

            // Échange le JWT contre un token d'accès
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'] ?? null;
            }

            Log::error('Firebase token generation failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Firebase token generation error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Envoie le message via l'API HTTP v1
     */
    private function sendMessage(array $message): array
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return [
                'success' => false,
                'error' => 'Impossible de générer le token d\'accès'
            ];
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json'
        ])->post($url, $message);

        $result = [
            'success' => $response->successful(),
            'http_code' => $response->status(),
            'response' => $response->json(),
            'raw_response' => $response->body()
        ];

        if (!$response->successful()) {
            Log::error('Firebase API error', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);
        }

        return $result;
    }

    /**
     * Convertit les données en strings (requis par Firebase)
     */
    private function convertDataToStrings(array $data): array
    {
        return array_map(function ($value) {
            return is_string($value) ? $value : json_encode($value);
        }, $data);
    }

    /**
     * Valide un token FCM
     */
    public function validateToken(string $token): bool
    {
        $result = $this->sendToToken($token, 'Test', 'Validation du token', ['test' => 'true']);
        return $result['success'];
    }
}