<?php

namespace App\Support;

/**
 * Kirim push FCM (HTTP v1) tanpa Composer: OAuth2 service account (RS256 JWT) + cURL.
 * Kredensial dibaca dari base_path('firebase-service-account.json') — di luar web root.
 */
class FcmSender
{
    private $projectId;
    private $clientEmail;
    private $privateKey;
    private $tokenUri;
    private $accessToken = null;
    private $accessExpiry = 0;

    public function __construct(array $credentials)
    {
        $this->projectId = $credentials['project_id'] ?? '';
        $this->clientEmail = $credentials['client_email'] ?? '';
        $this->privateKey = $credentials['private_key'] ?? '';
        $this->tokenUri = $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token';
    }

    public static function fromFile($path)
    {
        if (!is_file($path)) return null;
        $json = json_decode(file_get_contents($path), true);
        return is_array($json) ? new static($json) : null;
    }

    /** Kirim notifikasi ke semua perangkat milik satu anggota. Kembalikan jumlah terkirim. */
    public static function pushToAnggota($noAnggota, $title, $body, array $data = [])
    {
        $sender = self::fromFile(base_path('firebase-service-account.json'));
        if (!$sender) return 0;
        $tokens = \App\DeviceToken::where('fid_anggota', $noAnggota)->pluck('token')->all();
        $n = 0;
        foreach ($tokens as $token) {
            if ($sender->send($token, $title, $body, $data)) $n++;
        }
        return $n;
    }

    public function send($token, $title, $body, array $data = [])
    {
        try {
            $access = $this->accessToken();
            if (!$access || !$this->projectId) return false;
            $message = [
                'token' => $token,
                'notification' => ['title' => $title, 'body' => $body],
            ];
            if ($data) $message['data'] = $data;
            $payload = json_encode(['message' => $message]);
            $url = 'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send';
            $resp = $this->post($url, $payload, ['Authorization: Bearer ' . $access, 'Content-Type: application/json']);
            $decoded = json_decode($resp, true);
            return is_array($decoded) && !isset($decoded['error']);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function accessToken()
    {
        if ($this->accessToken && time() < $this->accessExpiry - 60) return $this->accessToken;
        $now = time();
        $header = $this->b64(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->b64(json_encode([
            'iss' => $this->clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $this->tokenUri,
            'iat' => $now,
            'exp' => $now + 3600,
        ]));
        $unsigned = $header . '.' . $payload;
        $sig = '';
        openssl_sign($unsigned, $sig, $this->privateKey, OPENSSL_ALGO_SHA256);
        $jwt = $unsigned . '.' . $this->b64($sig);
        $resp = $this->post($this->tokenUri, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]), ['Content-Type: application/x-www-form-urlencoded']);
        $data = json_decode($resp, true) ?: [];
        $this->accessToken = $data['access_token'] ?? null;
        $this->accessExpiry = time() + (int) ($data['expires_in'] ?? 3600);
        return $this->accessToken;
    }

    private function b64($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function post($url, $body, array $headers = [])
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 15,
        ]);
        $out = curl_exec($ch);
        curl_close($ch);
        return $out === false ? '' : (string) $out;
    }
}