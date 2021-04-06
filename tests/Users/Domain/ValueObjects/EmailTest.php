<?php

namespace Tests\Users\Domain\ValueObjects;

use App\Users\Domain\ValueObjects\Email;
use App\Users\Domain\ValueObjects\Exceptions\InvalidEmail;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function testEmailCannotHavewhitespaces()
    {
        $this->expectException(InvalidEmail::class);
        $this->expectErrorMessage('Your email cannot have whitespaces.');
        new Email('test@ email.com');
    }

    public function testInvalidEmailCanGiveException()
    {
        $this->expectException(InvalidEmail::class);
        $this->expectErrorMessage('Invalid email address.');
        new Email('inorrectEmail');
    }

    public function testEmailCanBePresentedLikeString()
    {
        $email = new Email('test@email.com');
        $this->assertSame('test@email.com', (string) $email);
    }
}
