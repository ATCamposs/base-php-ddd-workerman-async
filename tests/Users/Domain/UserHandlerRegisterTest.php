<?php

namespace Tests\Users\Domain;

use App\Users\Domain\UserHandler;
use PHPUnit\Framework\TestCase;

class UserHandlerRegisterTest extends TestCase
{
    public function testUserregisterUserWithWrongUserNameData()
    {
        $user = UserHandler::registerUser('test space', 'email@email.com', 'PassWd123');
        $this->assertContains('fail', $user);
        $this->assertArrayHasKey('user_name', $user['data']);

        $user = UserHandler::registerUser('no', 'email@email.com', 'PassWd123');
        $this->assertContains('fail', $user);
        $this->assertArrayHasKey('user_name', $user['data']);

        $user = UserHandler::registerUser('UserNameMuchLongerToBeAccepted', 'email@email.com', 'PassWd123');
        $this->assertContains('fail', $user);
        $this->assertArrayHasKey('user_name', $user['data']);

        $user = UserHandler::registerUser('ús^ŝname', 'email@email.com', 'PassWd123');
        $this->assertContains('fail', $user);
        $this->assertArrayHasKey('user_name', $user['data']);
    }

    public function testUserregisterUserWithWrongEmailData()
    {
        $user = UserHandler::registerUser('UserName', 'e mail@email.com', 'PassWd123');
        $this->assertContains('fail', $user);
        $this->assertArrayHasKey('email', $user['data']);

        $user = UserHandler::registerUser('UserName', 'emailemail.com', 'PassWd123');
        $this->assertContains('fail', $user);
        $this->assertArrayHasKey('email', $user['data']);
    }

    public function testUserregisterUserWithWrongPasswordData()
    {
        $user = UserHandler::registerUser('UserName', 'email@email.com', 'pass word');
        $this->assertContains('fail', $user);
        $this->assertArrayHasKey('password', $user['data']);

        $user = UserHandler::registerUser('UserName', 'email@email.com', 'Tiny');
        $this->assertContains('fail', $user);
        $this->assertArrayHasKey('password', $user['data']);

        $user = UserHandler::registerUser('UserName', 'email@email.com', 'TooLongPasswordToBeUsed1234');
        $this->assertContains('fail', $user);
        $this->assertArrayHasKey('password', $user['data']);

        $user = UserHandler::registerUser('UserName', 'email@email.com', 'justlowercase');
        $this->assertContains('fail', $user);
        $this->assertArrayHasKey('password', $user['data']);

        $user = UserHandler::registerUser('UserName', 'email@email.com', 'JUSTUPPERCASE');
        $this->assertContains('fail', $user);
        $this->assertArrayHasKey('password', $user['data']);

        $user = UserHandler::registerUser('UserName', 'email@email.com', 'NeedNumber');
        $this->assertContains('fail', $user);
        $this->assertArrayHasKey('password', $user['data']);
    }
}
