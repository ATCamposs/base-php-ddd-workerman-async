<?php

namespace Tests\Users\Infrastructure\Jwt;

use App\Users\Infrastructure\Jwt\JwtHandler;
use PHPUnit\Framework\TestCase;

class JwtHandlerTest extends TestCase
{
    public function testJWTEncodeData()
    {
        $token = (new JwtHandler())->jwtEncodeData(env('HOST'), ['name' => 'testName']);
        $this->assertEquals(true, is_string($token));
    }

    public function testJWTDecodeData()
    {
        $token = (new JwtHandler())->jwtEncodeData(env('HOST'), ['name' => 'testName']);
        $decoded_data = (new JwtHandler())->jwtDecodeData($token);
        $this->assertContains('success', $decoded_data);
    }
}
