<?php

namespace Tests\Users\Domain\ValueObjects;

use App\Users\Domain\ValueObjects\Exceptions\InvalidPassword;
use PHPUnit\Framework\TestCase;
use App\Users\Domain\ValueObjects\Password;

class PasswordTest extends TestCase
{
    public function testPasswordCannotHaveWhitespaces()
    {
        $this->expectException(InvalidPassword::class);
        $this->expectErrorMessage('Your password cannot have whitespaces.');
        new Password('testing ');
        new Password(' testing');
        new Password('test ing');
    }

    public function testPasswordMustBe6CharactersLong()
    {
        $this->expectException(InvalidPassword::class);
        $this->expectErrorMessage('Your password must be at least 6 characters.');
        new Password('pass');
        new Password('123');
        new Password('t@st');
    }

    public function testPasswordMustBe15CharactersOrLess()
    {
        $this->expectException(InvalidPassword::class);
        $this->expectErrorMessage('Your password must be less than 15 characters.');
        new Password('ThisPasswordIsTooBig');
    }

    public function testPasswordMustBeOneOrMoreLowerCase()
    {
        $this->expectException(InvalidPassword::class);
        $this->expectErrorMessage('Your password must contain at least 1 lowercase.');
        new Password('PASSWORD');
    }

    public function testPasswordMustBeOneOrMoreUpperCase()
    {
        $this->expectException(InvalidPassword::class);
        $this->expectErrorMessage('Your password must contain at least 1 uppercase.');
        new Password('password');
    }

    public function testPasswordMustBeOneOrMoreNumber()
    {
        $this->expectException(InvalidPassword::class);
        $this->expectErrorMessage('Your password must contain at least 1 number.');
        new Password('PASSword');
    }

    public function testPasswordMustBePasswordInstance()
    {
        $password = new Password('OnePassWord123');
        $this->assertSame('App\Users\Domain\ValueObjects\Password', \get_class($password));
    }

    public function testEmailCanBePresentedLikeString()
    {
        $password = new Password('AaA123456');
        $this->assertSame('AaA123456', (string) $password);
    }
}
