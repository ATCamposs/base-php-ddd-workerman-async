<?php

namespace Tests\Infrastructure;

use App\Infrastructure\UUIDGenerator;
use PHPUnit\Framework\TestCase;

class UUIDGeneratorTest extends TestCase
{
    public function testUUIDGeneratorTestRightV4UUID()
    {
        $uuid = UUIDGenerator::generate();
        $this->assertMatchesRegularExpression('/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/', $uuid);
    }
}
