<?php

declare(strict_types=1);

namespace App\Users\Domain;

interface TokenSecurityHandlerInterface
{
    public static function encrypt(array $data): string;
    public static function decrypt(string $jwt_auth_token): array;
    public function checkAuthorization(string $token);
}
