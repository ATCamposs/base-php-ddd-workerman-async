<?php

namespace Tests\Users\Domain;

use App\Users\Infrastructure\JwtSecurityHandler;
use PHPUnit\Framework\TestCase;

class JwtSecurityHandlerTest extends TestCase
{
    public function setUp(): void
    {
        $this->jwt_handler = new JwtSecurityHandler();
    }

    public function testEncryptAndDecryptData()
    {
        $encrypted_data = $this->jwt_handler->encrypt(['name' => 'testName']);
        $this->assertEquals(true, is_string($encrypted_data));
        $decrypted_data = $this->jwt_handler->decrypt($encrypted_data);
        $this->assertEquals('testName', $decrypted_data['data']->name);
        $this->assertContains('success', $decrypted_data);
    }
}
