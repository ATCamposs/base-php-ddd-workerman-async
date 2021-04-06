<?php

namespace Tests\Users\Domain;

use App\Users\Domain\User;
use App\Users\Domain\UserHandler;
use App\Users\Domain\ValueObjects\Email;
use Phinx\Config\Config;
use Phinx\Migration\Manager;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use PHPUnit\Framework\TestCase;
use support\DataBase;

class UserTest extends TestCase
{
    private static string $uuid = 'c6b794f6-64b3-44c5-8d15-ae791f857161';
    private static string $email = 'firstTestUser@fakemail.com';
    private static string $user_name = 'firstTestUser';
    private static User $default_test_user;

    public static function setUpBeforeClass(): void
    {
        $configArray = require('phinx.php');
        $configArray['environments']['test'] = [
            'adapter'    => 'sqlite',
            'connection' => DataBase::connection()->getPdo()
        ];
        $config = new Config($configArray);
        $manager = new Manager($config, new StringInput(' '), new NullOutput());
        $manager->migrate('test');
        $manager->seed('test');
        // Insert User 1 inside DB
        DataBase::table('users')->where(['uuid' => self::$uuid])->delete();
        DataBase::table('users')->insert([
            'uuid' => self::$uuid,
            'user_name' => self::$user_name,
            'email' => self::$email,
            'password' => password_hash('passTest123', PASSWORD_DEFAULT),
            'active' => false,
            'created' => date('Y-m-d H:i:s'),
            'activation_hash' => 'newInvalidHashJustForTests1234'
        ]);
        self::$default_test_user = UserHandler::getUserByUUID(self::$uuid);
    }

    public function testUserUpdateUserNameWithWrongData()
    {
        $update_username = self::$default_test_user->updateUserName('bad user name');
        $this->assertContains('fail', $update_username);
        $this->assertArrayHasKey('user_name', $update_username['data']);
    }

    public function testUserUpdateUserNameWithDuplicatedData()
    {
        $update_username = self::$default_test_user->updateUserName(self::$user_name);
        $this->assertContains('fail', $update_username);
        $this->assertArrayHasKey('user_name', $update_username['data']);
    }

    public function testUserUpdateUserNameWithRightData()
    {
        $update_username = self::$default_test_user->updateUserName('newUserName');
        $this->assertContains('success', $update_username);
        $this->assertArrayHasKey('user_name', $update_username['data']);
    }

    public function testUserUpdateEmailWithWrongData()
    {
        $update_username = self::$default_test_user->updateEmail('wrongemail.com');
        $this->assertContains('fail', $update_username);
        $this->assertArrayHasKey('email', $update_username['data']);
    }

    public function testUserUpdateEmailWithDuplicatedData()
    {
        $update_username = self::$default_test_user->updateEmail(self::$email);
        $this->assertContains('fail', $update_username);
        $this->assertArrayHasKey('email', $update_username['data']);
    }

    public function testUserUpdateEmailWithRightData()
    {
        $update_username = self::$default_test_user->updateEmail('newemail@email.com');
        $this->assertContains('success', $update_username);
        $this->assertArrayHasKey('email', $update_username['data']);
    }

    public function testUserUpdatePasswordWithWrongData()
    {
        $update_username = self::$default_test_user->updatePassword('GoodPass123', 'WrongConfirm');
        $this->assertContains('fail', $update_username);
        $this->assertArrayHasKey('password', $update_username['data']);

        $update_username = self::$default_test_user->updatePassword('WrongPass', 'GoodPass123');
        $this->assertContains('fail', $update_username);
        $this->assertArrayHasKey('password', $update_username['data']);

        $update_username = self::$default_test_user->updatePassword('BadPass', 'BadPass');
        $this->assertContains('fail', $update_username);
        $this->assertArrayHasKey('password', $update_username['data']);
    }

    public function testUserUpdatePasswordWithRightData()
    {
        $update_username = self::$default_test_user->updatePassword('GoodPass123', 'GoodPass123');
        $this->assertContains('success', $update_username);
        $this->assertArrayHasKey('password', $update_username['data']);
    }
}
