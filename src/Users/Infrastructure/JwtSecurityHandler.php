<?php

declare(strict_types=1);

namespace App\Users\Infrastructure;

use App\Users\Domain\TokenSecurityHandlerInterface;
use App\Users\Application\UserServices;
use App\Users\Infrastructure\Jwt\JwtHandler;

class JwtSecurityHandler implements TokenSecurityHandlerInterface
{
    public static function encrypt(array $data): string
    {
        return (new JwtHandler())->jwtEncodeData(env('HOST', null), $data);
    }

    public static function decrypt(string $jwt_auth_token): array
    {
        return (new JwtHandler())->jwtDecodeData($jwt_auth_token);
    }

    public function checkAuthorization(string $token): array
    {
        $token_decrypted = $this->decrypt($token);
        if ($token_decrypted['status'] !== 'success' || !isset($token_decrypted['data']->uuid)) {
            return $token_decrypted;
        }
        return (new UserServices())->checkTokenAuthenticationAndReturnUser($token_decrypted['data']->uuid);
    }
}
