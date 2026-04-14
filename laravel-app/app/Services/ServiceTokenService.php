<?php

namespace App\Services;

use Firebase\JWT\JWT;

class ServiceTokenService
{
    public function generate(): string
    {
        $now = time();

        $payload = [
            'iss' => 'laravel-app',
            'aud' => 'nest-validator',
            'iat' => $now,
            'exp' => $now + 300,
            'scope' => 'internal-service',
        ];

        return JWT::encode(
            $payload,
            config('services.service_jwt.secret'),
            'HS256'
        );
    }
}