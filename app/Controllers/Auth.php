<?php

namespace App\Controllers;

use App\Models\ApplicationModel;
use App\Models\UserModel;
use App\Models\RefreshTokenModel;
use App\Libraries\JwtHelper;
use Ramsey\Uuid\Uuid;

class Auth extends BaseController
{
    public function authorize()
    {
        $clientId    = $this->request->getGet('client_id');
        $redirectUri = $this->request->getGet('redirect_uri');
        $state       = $this->request->getGet('state');

        if (empty($clientId) || empty($redirectUri) || empty($state)) {
            return $this->response->setStatusCode(400)
                ->setBody('Parameter client_id, redirect_uri, dan state wajib diisi.');
        }

        $applicationModel = new ApplicationModel();
        $app = $applicationModel->where('client_id', $clientId)->first();

        if (!$app) {
            return $this->response->setStatusCode(400)
                ->setBody('client_id tidak dikenali / tidak terdaftar.');
        }

        if ($app['redirect_uri'] !== $redirectUri) {
            return $this->response->setStatusCode(400)
                ->setBody('redirect_uri tidak cocok dengan yang terdaftar untuk client ini.');
        }

        session()->set('oauth_context', [
            'client_id'    => $clientId,
            'redirect_uri' => $redirectUri,
            'state'        => $state,
        ]);

        return view('auth/login', [
            'client_name' => $app['name'],
            'error'       => session()->getFlashdata('error'),
        ]);
    }

    public function attemptLogin()
    {
        $context = session()->get('oauth_context');

        if (!$context) {
            return redirect()->to('/authorize')
                ->with('error', 'Sesi otorisasi tidak ditemukan atau kedaluwarsa. Ulangi proses login dari aplikasi asal.');
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return redirect()->back()
                ->with('error', 'Email atau password salah.')
                ->withInput();
        }

        $applicationModel = new ApplicationModel();
        $app = $applicationModel->where('client_id', $context['client_id'])->first();

        $jti = Uuid::uuid4()->toString();

        $accessToken = JwtHelper::generateAccessToken($user, $jti);

        $refreshTokenPlain = bin2hex(random_bytes(32));
        $refreshTokenHash  = hash('sha256', $refreshTokenPlain);

        $refreshTokenModel = new RefreshTokenModel();
        $refreshTokenModel->insert([
            'id'             => Uuid::uuid4()->toString(),
            'user_id'        => $user['id'],
            'application_id' => $app['id'],
            'jti'            => $jti,
            'token_hash'     => $refreshTokenHash,
            'expires_at'     => date('Y-m-d H:i:s', time() + (7 * 24 * 60 * 60)),
            'revoked'        => 0,
        ]);

        session()->remove('oauth_context');

        $redirectUrl = $context['redirect_uri'] . '?' . http_build_query([
            'access_token'  => $accessToken,
            'refresh_token' => $refreshTokenPlain,
            'state'         => $context['state'],
        ]);

        return redirect()->to($redirectUrl);
    }

    /**
     * POST /api/test-login
     *
     * Endpoint khusus development untuk testing via Postman.
     * Logic identik dengan attemptLogin(), bedanya:
     * - Menerima JSON body (bukan form POST + session)
     * - Mengembalikan JSON (bukan redirect)
     * - HANYA aktif di CI_ENVIRONMENT = development
     */
    public function testLogin()
    {
        // GUARD: hanya aktif di environment development
        if (ENVIRONMENT !== 'development') {
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => 'not_found']);
        }

        $json      = $this->request->getJSON(true);
        $email     = $json['email'] ?? null;
        $password  = $json['password'] ?? null;
        $clientId  = $json['client_id'] ?? null;

        // Validasi input
        if (empty($email) || empty($password) || empty($clientId)) {
            return $this->response->setStatusCode(400)
                ->setJSON([
                    'error'   => 'bad_request',
                    'message' => 'Parameter email, password, dan client_id wajib diisi.',
                ]);
        }

        // Validasi client_id
        $applicationModel = new ApplicationModel();
        $app = $applicationModel->where('client_id', $clientId)->first();

        if (!$app) {
            return $this->response->setStatusCode(400)
                ->setJSON([
                    'error'   => 'invalid_client',
                    'message' => 'client_id tidak dikenali / tidak terdaftar.',
                ]);
        }

        // Validasi kredensial
        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return $this->response->setStatusCode(401)
                ->setJSON([
                    'error'   => 'invalid_credentials',
                    'message' => 'Email atau password salah.',
                ]);
        }

        // Generate JTI, access token, dan refresh token
        $jti = Uuid::uuid4()->toString();

        $accessToken = JwtHelper::generateAccessToken($user, $jti);

        $refreshTokenPlain = bin2hex(random_bytes(32));
        $refreshTokenHash  = hash('sha256', $refreshTokenPlain);

        $refreshTokenModel = new RefreshTokenModel();
        $refreshTokenModel->insert([
            'id'             => Uuid::uuid4()->toString(),
            'user_id'        => $user['id'],
            'application_id' => $app['id'],
            'jti'            => $jti,
            'token_hash'     => $refreshTokenHash,
            'expires_at'     => date('Y-m-d H:i:s', time() + (7 * 24 * 60 * 60)),
            'revoked'        => 0,
        ]);

        return $this->response->setStatusCode(200)
            ->setJSON([
                'access_token'  => $accessToken,
                'refresh_token' => $refreshTokenPlain,
            ]);
    }
}