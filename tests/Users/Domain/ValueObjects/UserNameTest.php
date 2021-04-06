<?php

namespace Tests\Users\Domain\ValueObjects;

use App\Users\Domain\ValueObjects\Exceptions\InvalidUserName;
use PHPUnit\Framework\TestCase;
use App\Users\Domain\ValueObjects\UserName;

class UserNameTest extends TestCase
{
    public function testNameMustBe3CharactersLong()
    {
        $this->expectException(InvalidUserName::class);
        $this->expectErrorMessage('Your username must be at least 3 characters.');
        new UserName('hi');
    }

    public function testNameMustBe26CharactersOrLess()
    {
        $this->expectException(InvalidUserName::class);
        $this->expectErrorMessage('Your username must be less than 26 characters.');
        new UserName('ABiggerNameCannotBeSettedHereAndMustHaveAnException');
    }

    public function testNameCannotHaveAccent()
    {
        $this->expectException(InvalidUserName::class);
        $this->expectErrorMessage('Your username cannot have special characters.');
        new UserName("O'Donnell,Chris");
        $this->expectException(InvalidUserName::class);
        new UserName("André");
        $this->expectException(InvalidUserName::class);
        new UserName("Antônio");
    }

    public function testNameCannotHaveSpaceAtTheBegginingAtTheMiddleAndAtTheEnd()
    {
        $this->expectException(InvalidUserName::class);
        $this->expectErrorMessage('Your username cannot have whitespaces.');
        new UserName('User name');
        new UserName(' UserName');
        new UserName('UserName ');
    }

    public function testNameCannotHaveSpecialCharacters()
    {
        $this->expectException(InvalidUserName::class);
        new UserName('<h1>Hello WorldÆØÅ!</h1>');
        $this->expectException(InvalidUserName::class);
        new UserName('<h1>Hello World!</h1>');
    }

    public function testNameCanBePresentedLikeString()
    {
        $user_name = new UserName('Test');
        $this->assertSame('Test', (string) $user_name);
        $user_name = new UserName('Anothername');
        $this->assertSame('Anothername', (string) $user_name);
    }
}
