<?php

namespace App\Controllers;

use App\Models\ApplicationModel;
use App\Models\RefreshTokenModel;
use App\Models\UserModel;
use App\Libraries\JwtHelper;

class Sso extends BaseController
{
    /**
     * GET /public-key
     *
     * Mengembalikan public key PEM untuk verifikasi JWT oleh client.
     * Endpoint ini bersifat publik (tanpa auth).
     */
    public function publicKey()
    {
        $publicKeyPath = ROOTPATH . 'keys/public.pem';

        if (!file_exists($publicKeyPath)) {
            return $this->response
                ->setStatusCode(500)
                ->setContentType('text/plain')
                ->setBody('Public key not available.');
        }

        $publicKey = file_get_contents($publicKeyPath);

        return $this->response
            ->setStatusCode(200)
            ->setContentType('text/plain')
            ->setHeader('Cache-Control', 'public, max-age=86400')
            ->setBody($publicKey);
    }

    /**
     * POST /refresh-token
     *
     * Menerima refresh token lama dan client_id, memvalidasi keabsahannya,
     * merotasi refresh token di database (UPDATE), lalu menerbitkan 
     * access token baru dan refresh token baru.
     */
    public function refreshToken()
    {
        $json = $this->request->getJSON(true);
        $refreshTokenPlain = $json['refresh_token'] ?? null;
        $clientId          = $json['client_id'] ?? null;

        // Validasi input
        if (empty($refreshTokenPlain) || empty($clientId)) {
            return $this->response->setStatusCode(400)
                ->setJSON([
                    'error'   => 'bad_request',
                    'message' => 'Parameter refresh_token dan client_id wajib diisi.',
                ]);
        }

        // Validasi client_id
        $applicationModel = new ApplicationModel();
        $app = $applicationModel->where('client_id', $clientId)->first();

        if (!$app) {
            return $this->response->setStatusCode(400)
                ->setJSON([
                    'error'   => 'invalid_client',
                    'message' => 'client_id tidak dikenali.',
                ]);
        }

        // Hash & lookup refresh token
        $tokenHash = hash('sha256', $refreshTokenPlain);

        $refreshTokenModel = new RefreshTokenModel();
        $storedToken = $refreshTokenModel
            ->where('token_hash', $tokenHash)
            ->where('application_id', $app['id'])
            ->where('revoked', 0)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->first();

        if (!$storedToken) {
            return $this->response->setStatusCode(401)
                ->setJSON([
                    'error'   => 'invalid_token',
                    'message' => 'Refresh token tidak valid, sudah kadaluarsa, atau sudah di-revoke.',
                ]);
        }

        // Fetch user
        $userModel = new UserModel();
        $user = $userModel->find($storedToken['user_id']);

        if (!$user) {
            return $this->response->setStatusCode(401)
                ->setJSON([
                    'error'   => 'invalid_token',
                    'message' => 'User tidak ditemukan.',
                ]);
        }

        // Cek akses aplikasi memastikan akses belum dicabut
        $accessModel = new \App\Models\UserApplicationAccessModel();
        $access = $accessModel->where('user_id', $user['id'])
                              ->where('application_id', $app['id'])
                              ->where('revoked_at', null)
                              ->first();
                              
        if (!$access) {
            return $this->response->setStatusCode(403)
                ->setJSON([
                    'error'   => 'application_access_denied',
                    'message' => 'Anda tidak lagi memiliki akses ke aplikasi ini. Hubungi admin SSO.',
                ]);
        }

        // Rotate: generate refresh token baru, update row yang sama
        $newRefreshTokenPlain = bin2hex(random_bytes(32));
        $newRefreshTokenHash  = hash('sha256', $newRefreshTokenPlain);

        $refreshTokenModel->update($storedToken['id'], [
            'token_hash' => $newRefreshTokenHash,
            'expires_at' => date('Y-m-d H:i:s', time() + (7 * 24 * 60 * 60)),
        ]);

        // Generate access token baru dengan JTI yang SAMA
        $accessToken = JwtHelper::generateAccessToken($user, $storedToken['jti']);

        return $this->response->setStatusCode(200)
            ->setJSON([
                'access_token'  => $accessToken,
                'refresh_token' => $newRefreshTokenPlain,
            ]);
    }

    /**
     * POST /logout
     *
     * Single Logout: merevoke semua refresh token milik user
     * di semua aplikasi, lalu memasukkan semua JTI aktif ke
     * blacklist Redis agar access token yang beredar langsung invalid.
     */
    public function logout()
    {
        $json = $this->request->getJSON(true);
        $refreshTokenPlain = $json['refresh_token'] ?? null;

        // Validasi input
        if (empty($refreshTokenPlain)) {
            return $this->response->setStatusCode(400)
                ->setJSON([
                    'error'   => 'bad_request',
                    'message' => 'Parameter refresh_token wajib diisi.',
                ]);
        }

        // Hash & lookup refresh token yang dikirim
        $tokenHash = hash('sha256', $refreshTokenPlain);

        $refreshTokenModel = new RefreshTokenModel();
        $storedToken = $refreshTokenModel
            ->where('token_hash', $tokenHash)
            ->where('revoked', 0)
            ->first();

        if (!$storedToken) {
            return $this->response->setStatusCode(401)
                ->setJSON([
                    'error'   => 'invalid_token',
                    'message' => 'Refresh token tidak valid atau sudah di revoke.',
                ]);
        }

        $userId = $storedToken['user_id'];

        // Ambil semua refresh token aktif milik user 
        $activeTokens = $refreshTokenModel
            ->where('user_id', $userId)
            ->where('revoked', 0)
            ->findAll();

        // Kumpulkan semua JTI untuk di-blacklist
        $jtis = array_column($activeTokens, 'jti');

        // Revoke semua refresh token di database
        $activeIds = array_column($activeTokens, 'id');
        if (!empty($activeIds)) {
            $refreshTokenModel
                ->whereIn('id', $activeIds)
                ->set(['revoked' => 1])
                ->update();
        }

        // Blacklist semua JTI di Redis (TTL 15 menit)
        $blacklist = new \App\Libraries\RedisBlacklist();
        $blacklist->addMany($jtis);

        return $this->response->setStatusCode(200)
            ->setJSON([
                'message' => 'Logout berhasil. Semua sesi telah diakhiri.',
            ]);
    }
}
