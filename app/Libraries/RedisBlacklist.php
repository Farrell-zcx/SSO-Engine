<?php

namespace App\Libraries;

use Predis\Client as PredisClient;

class RedisBlacklist
{
    private PredisClient $redis;

    /**
     * Prefix untuk key Redis, agar tidak bentrok dengan data lain.
     */
    private const KEY_PREFIX = 'sso_blacklist:jti:';

    /**
     * TTL default = 900 detik (15 menit).
     * Sama dengan lifetime access token, sehingga entry otomatis
     * terhapus setelah access token expired secara natural.
     */
    private const DEFAULT_TTL = 900;

    public function __construct()
    {
        $this->redis = new PredisClient([
            'scheme'   => 'tcp',
            'host'     => env('redis.host', '127.0.0.1'),
            'port'     => (int) env('redis.port', 6379),
            'password' => env('redis.password', null) ?: null,
            'database' => (int) env('redis.database', 0),
        ]);
    }

    /**
     * Masukkan satu JTI ke blacklist.
     */
    public function add(string $jti, int $ttl = self::DEFAULT_TTL): void
    {
        try {
            $key = self::KEY_PREFIX . $jti;
            $this->redis->setex($key, $ttl, '1');
        } catch (\Exception $e) {
            log_message('error', 'Redis connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Masukkan banyak JTI sekaligus.
     * dalam satu round-trip ke Redis.
     */
    public function addMany(array $jtis, int $ttl = self::DEFAULT_TTL): void
    {
        if (empty($jtis)) {
            return;
        }

        try {
            $this->redis->pipeline(function ($pipe) use ($jtis, $ttl) {
                foreach ($jtis as $jti) {
                    $pipe->setex(self::KEY_PREFIX . $jti, $ttl, '1');
                }
            });
        } catch (\Exception $e) {
            log_message('error', 'Redis connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Cek apakah JTI ada di blacklist.
     * Return true = token sudah di revoke, harus ditolak.
     */
    public function isBlacklisted(string $jti): bool
    {
        try {
            return (bool) $this->redis->exists(self::KEY_PREFIX . $jti);
        } catch (\Exception $e) {
            return false;
        }
    }
}
