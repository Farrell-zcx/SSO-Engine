<?php

namespace App\Libraries;

use Firebase\JWT\JWT;

class JwtHelper
{
    public static function getPrivateKey(): string
    {
        return file_get_contents(ROOTPATH . 'keys/private.pem');
    }

    public static function getPublicKey(): string
    {
        return file_get_contents(ROOTPATH . 'keys/public.pem');
    }

    /**
     * Generate access token (JWT RS256) untuk satu sesi login.
     * $jti dipakai bareng dengan refresh token terkait (1 sesi = 1 jti).
     */
    public static function generateAccessToken(array $user, string $jti): string
    {
        $issuedAt  = time();
        $expiresAt = $issuedAt + (15 * 60); // 15 menit

        // Cek apakah user adalah admin SSO
        $db = \Config\Database::connect();
        $isSsoAdmin = $db->table('sso_admins')->where('user_id', $user['id'])->countAllResults() > 0;

        $payload = [
            'iss'          => 'sso-engine',
            'sub'          => $user['id'],
            'jti'          => $jti,
            'email'        => $user['email'],
            'username'     => $user['username'],
            'is_sso_admin' => $isSsoAdmin,
            'iat'          => $issuedAt,
            'exp'          => $expiresAt,
        ];

        return JWT::encode($payload, self::getPrivateKey(), 'RS256');
    }
}