<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtService
{
    private string $privateKey;
    private string $publicKey;
    private string $algorithm = 'RS256';
    private string $issuer;

    public function __construct()
    {
        $privateKeyPath = ROOTPATH . 'keys/private.pem';
        $publicKeyPath = ROOTPATH . 'keys/public.pem';

        if (!file_exists($privateKeyPath) || !file_exists($publicKeyPath)) {
            throw new Exception("RSA keys not found. Please ensure keys/private.pem and keys/public.pem exist.");
        }

        $this->privateKey = file_get_contents($privateKeyPath);
        $this->publicKey = file_get_contents($publicKeyPath);
        
        // Define issuer, you can also fetch this from env('app.baseURL')
        $this->issuer = env('app.baseURL', 'http://localhost');
    }

    /**
     * Generate JWT Token
     *
     * @param array $payloadData Custom data to include in the token payload
     * @param int $expiry Time to live in seconds (default 3600 = 1 hour)
     * @return string
     */
    public function generateToken(array $payloadData, int $expiry = 3600): string
    {
        $issuedAt = time();
        $expireAt = $issuedAt + $expiry;

        $payload = [
            'iat'  => $issuedAt,
            'exp'  => $expireAt,
            'iss'  => $this->issuer,
            'data' => $payloadData,
        ];

        return JWT::encode($payload, $this->privateKey, $this->algorithm);
    }

    /**
     * Validate and decode JWT Token
     *
     * @param string $token
     * @return object|false Returns the decoded payload if valid, false otherwise
     */
    public function validateToken(string $token)
    {
        try {
            return JWT::decode($token, new Key($this->publicKey, $this->algorithm));
        } catch (Exception $e) {
            log_message('error', '[JwtService] Token validation failed: ' . $e->getMessage());
            return false;
        }
    }
}
