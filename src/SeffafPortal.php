<?php
/**
 * Ankara Seffaf Portal API Client
 *
 * @author  TKA
 * @license MIT
 * @link    https://seffaf.ankara.bel.tr
 */

namespace SeffafPortal;

class Client
{
    const BASE_URL = 'https://seffafservice.ankara.bel.tr/TeoServerCoreSeffaf';

    private $key;
    private $iv;
    private $token;
    private $refreshToken;
    private $user;

    /**
     * Kullanici girisi yap
     *
     * @param string $username Kullanici adi
     * @param string $password Sifre
     * @return array|null Kullanici bilgileri veya null
     */
    public function login($username, $password)
    {
        // AES key al
        $keyData = $this->request('GET', '/SystemService/TeoUser/GetKey/' . urlencode($username));

        if (!$keyData) {
            return null;
        }

        // Key ve IV ayir (3 boslukla ayrilmis)
        $parts = explode('   ', $keyData);
        $this->key = $parts[0];
        $this->iv = $parts[1] ?? '';

        // Credentials sifrele
        $credentials = json_encode([
            'Password' => $password,
            'UserName' => $username
        ]);

        $encrypted = $this->encrypt($credentials);

        // Authenticate
        $response = $this->request('POST', '/SystemService/TeoUser/Authenticate', '"' . $encrypted . '"');

        if ($response) {
            $this->user = json_decode($response, true);
            $this->token = $this->user['Token'] ?? null;
            $this->refreshToken = $this->user['RefreshToken'] ?? null;
            return $this->user;
        }

        return null;
    }

    /**
     * AES-256-CFB sifreleme (PKCS7 padding ile)
     */
    private function encrypt($data)
    {
        // PKCS7 padding ekle (CryptoJS uyumlu)
        $blockSize = 16;
        $pad = $blockSize - (strlen($data) % $blockSize);
        $paddedData = $data . str_repeat(chr($pad), $pad);

        $encrypted = openssl_encrypt(
            $paddedData,
            'aes-256-cfb',
            $this->key,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            $this->iv
        );

        return base64_encode($encrypted);
    }

    /**
     * API istegi gonder
     */
    public function request($method, $endpoint, $body = null, $useAuth = false)
    {
        $url = self::BASE_URL . $endpoint;

        $headers = ['Content-Type: application/json'];

        if ($useAuth && $this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception('cURL Error: ' . $error);
        }

        if ($httpCode >= 200 && $httpCode < 300 && !empty($response)) {
            return $response;
        }

        return null;
    }

    /**
     * Sifrelenmis POST istegi
     */
    public function encryptedPost($endpoint, $data)
    {
        $encrypted = $this->encrypt(json_encode($data));
        return $this->request('POST', $endpoint, '"' . $encrypted . '"', true);
    }

    /**
     * Token al
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Kullanici bilgisi al
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Giris yapildi mi?
     */
    public function isLoggedIn()
    {
        return $this->token !== null;
    }

    /**
     * Katmanlari listele
     */
    public function getLayers()
    {
        if (!$this->user) {
            return [];
        }

        $layers = [];
        foreach ($this->user['Components'] ?? [] as $comp) {
            if ($comp['ComponentType'] == 3) {
                $layers[] = [
                    'id' => $comp['Id'],
                    'name' => $comp['Name'],
                    'path' => $comp['RolePath']
                ];
            }
        }

        return $layers;
    }
}
