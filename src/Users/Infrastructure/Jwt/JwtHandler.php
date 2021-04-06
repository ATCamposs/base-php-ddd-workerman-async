<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Jwt;

use Exception;
use Firebase\JWT\JWT;

class JwtHandler
{
    protected $jwt_secrect;
    protected $token;
    protected $issuedAt;
    protected $expire;
    protected $jwt;

    public function __construct()
    {
        $this->issuedAt = time();

        // Token Validity (3600 second = 1hr)
        $this->expire = $this->issuedAt + env('JWT_EXPIRE_MINUTES', 60) * 60;

        // Set your secret or signature
        $this->jwt_secrect = env('JWT_SECRET', 'this_is_my_secrect');
    }

    /** @param HttpHost $iss
    *   @param DataToEncode $data
    */
    public function jwtEncodeData(string $iss, array $data): string
    {

        $this->token = [
            //Adding the identifier to the token (who issue the token)
            "iss" => $iss,
            "aud" => $iss,
            // Adding the current timestamp to the token, for identifying that when the token was issued.
            "iat" => $this->issuedAt,
            // Token expiration
            "exp" => $this->expire,
            // Payload
            "data" => $data
        ];

        $this->jwt = JWT::encode($this->token, $this->jwt_secrect);
        return $this->jwt;
    }

    protected function errorMsg($msg)
    {
        return [
            'status' => 'fail',
            "message" => $msg
        ];
    }

    public function jwtDecodeData(string $jwt_token): array
    {
        try {
            $decode = JWT::decode($jwt_token, $this->jwt_secrect, array('HS256'));
            return [
                "status" => 'success',
                "data" => $decode->data
            ];
        } catch (\Firebase\JWT\ExpiredException $error) {
            return $this->returnFail(trans('Expired Token.'));
        } catch (\Firebase\JWT\SignatureInvalidException $error) {
            return $this->returnFail(trans('Signature verification failed.'));
        } catch (Exception $error) {
            return $this->returnFail(trans('Authentication error.'));
        }
    }

    private function returnFail(string $message): array
    {
        return [
            'status' => 'fail',
            'data' => $message
        ];
    }
}
