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

class UserHandlerTest extends TestCase
{
    private static string $uuid = 'c6b794f6-64b3-44c5-8d15-ae791f857161';
    private static string $email = 'firstTestUser@fakemail.com';
    private static string $user_name = 'firstTestUser';

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
    }

    public function testUserGetUserByUUIDWithWrongData()
    {
        $user = UserHandler::getUserByUUID('wronguuid');
        $this->assertSame(null, $user);
    }

    public function testUserGetUserByUUIDWithRightData()
    {
        $user = UserHandler::getUserByUUID(self::$uuid);
        $this->assertSame('App\Users\Domain\User', get_class($user));
    }

    public function testUserGetUserByEmailWithWrongData()
    {
        $email = new Email('wrong@email.com');
        $user = UserHandler::getUserByEmail($email);
        $this->assertSame(null, $user);
    }

    public function testUserGetUserByEmailWithRightData()
    {
        $email = new Email(self::$email);
        $user = UserHandler::getUserByEmail($email);
        $this->assertSame('App\Users\Domain\User', get_class($user));
    }
}
