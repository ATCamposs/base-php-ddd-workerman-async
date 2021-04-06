<?php

namespace Tests\Users\Domain;

use App\Users\Domain\Admin;
use App\Users\Domain\User;
use App\Users\Domain\UserHandler;
use Phinx\Config\Config;
use Phinx\Migration\Manager;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use PHPUnit\Framework\TestCase;
use support\DataBase;

class AdminTest extends TestCase
{
    private static string $uuid_user = 'c6b794f6-64b3-44c5-8d15-ae791f857161';
    private static string $email_user = 'firstTestUser@fakemail.com';
    private static string $user_name_user = 'firstTestUser';

    private static string $uuid_admin = 'c6d134f6-13b6-23c5-4d42-ae423f865361';
    private static string $email_admin = 'firstTestAdmin@fakemail.com';
    private static string $user_name_admin = 'firstTestAdmin';
    private static Admin $default_test_admin;

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
        DataBase::table('users')->where(['uuid' => self::$uuid_user])->delete();
        DataBase::table('users')->insert([
            'uuid' => self::$uuid_user,
            'user_name' => self::$user_name_user,
            'email' => self::$email_user,
            'password' => password_hash('passTest123', PASSWORD_DEFAULT),
            'active' => false,
            'created' => date('Y-m-d H:i:s'),
            'activation_hash' => 'newInvalidHashJustForTests1234'
        ]);
        DataBase::table('users')->where(['uuid' => self::$uuid_admin])->delete();
        DataBase::table('users')->insert([
            'uuid' => self::$uuid_admin,
            'user_name' => self::$user_name_admin,
            'email' => self::$email_admin,
            'password' => password_hash('passTest123', PASSWORD_DEFAULT),
            'active' => false,
            'access_level' => 5,
            'created' => date('Y-m-d H:i:s'),
            'activation_hash' => 'newInvalidHashJustForTests1234'
        ]);
        self::$default_test_admin = Admin::getAdminByUUID(self::$uuid_admin);
    }

    public function testAdminChangeUserUserNameWithWrongData()
    {
        $user = UserHandler::getUserByUUID(self::$uuid_user);
        $this->assertSame(self::$user_name_user, (string) $user->userName());
        $change_user_name = self::$default_test_admin->changeUserUserName('wronguuid', 'bad user name');
        $this->assertContains('fail', $change_user_name);
        $this->assertArrayHasKey('uuid', $change_user_name['data']);
        $change_user_name = self::$default_test_admin->changeUserUserName(self::$uuid_user, 'bad user name');
        $this->assertContains('fail', $change_user_name);
        $this->assertArrayHasKey('user_name', $change_user_name['data']);
        $user = UserHandler::getUserByUUID(self::$uuid_user);
        $this->assertSame(self::$user_name_user, (string) $user->userName());
    }

    public function testAdminChangeUserUserNameWithRightData()
    {
        $user = UserHandler::getUserByUUID(self::$uuid_user);
        $this->assertSame(self::$user_name_user, (string) $user->userName());
        $change_user_name = self::$default_test_admin->changeUserUserName(self::$uuid_user, 'neWUserName');
        $this->assertContains('success', $change_user_name);
        $this->assertArrayHasKey('user_name', $change_user_name['data']);
        $user = UserHandler::getUserByUUID(self::$uuid_user);
        $this->assertSame('neWUserName', (string) $user->userName());
    }

    public function testAdminChangeUserEmailWithWrongData()
    {
        $user = UserHandler::getUserByUUID(self::$uuid_user);
        $this->assertSame(self::$email_user, (string) $user->email());
        $change_email = self::$default_test_admin->changeUserEmail('wronguuid', 'bad email');
        $this->assertContains('fail', $change_email);
        $this->assertArrayHasKey('uuid', $change_email['data']);
        $change_email = self::$default_test_admin->changeUserEmail(self::$uuid_user, 'wrongemail');
        $this->assertContains('fail', $change_email);
        $this->assertArrayHasKey('email', $change_email['data']);
        $user = UserHandler::getUserByUUID(self::$uuid_user);
        $this->assertSame(self::$email_user, (string) $user->email());
    }

    public function testAdminChangeUserEmailWithWRightData()
    {
        $user = UserHandler::getUserByUUID(self::$uuid_user);
        $this->assertSame(self::$email_user, (string) $user->email());
        $change_email = self::$default_test_admin->changeUserEmail(self::$uuid_user, 'new@email.com');
        $this->assertContains('success', $change_email);
        $this->assertArrayHasKey('email', $change_email['data']);
        $user = UserHandler::getUserByUUID(self::$uuid_user);
        $this->assertSame('new@email.com', (string) $user->email());
    }

    public function testAdminActivateUserWithWrongData()
    {
        $user = UserHandler::getUserByUUID(self::$uuid_user);
        $this->assertSame(false, $user->active());
        $activate_user = self::$default_test_admin->activateUser('invalidUUID');
        $this->assertContains('fail', $activate_user);
        $this->assertArrayHasKey('uuid', $activate_user['data']);
        $user = UserHandler::getUserByUUID(self::$uuid_user);
        $this->assertSame(false, $user->active());
    }

    public function testAdminActivateUserWithRightData()
    {
        $user = UserHandler::getUserByUUID(self::$uuid_user);
        $this->assertSame(false, $user->active());
        $activate_user = self::$default_test_admin->activateUser(self::$uuid_user);
        $this->assertContains('success', $activate_user);
        $this->assertArrayHasKey('message', $activate_user['data']);
        $user = UserHandler::getUserByUUID(self::$uuid_user);
        $this->assertSame(true, $user->active());
    }

    public function testAdminChangeUserAccessLevelWithWrongData()
    {
        $user = UserHandler::getUserByUUID(self::$uuid_user);
        $this->assertSame(0, $user->accessLevel());
        $new_user_acess_level = self::$default_test_admin->changeUserAccessLevel('wronguuid', 1);
        $this->assertContains('fail', $new_user_acess_level);
        $this->assertArrayHasKey('uuid', $new_user_acess_level['data']);
        $new_user_acess_level = self::$default_test_admin->changeUserAccessLevel(self::$uuid_user, 5);
        $this->assertContains('fail', $new_user_acess_level);
        $this->assertArrayHasKey('access_level', $new_user_acess_level['data']);
    }

    public function testAdminChangeUserAccessLevelWithRightData()
    {
        $user = UserHandler::getUserByUUID(self::$uuid_user);
        $this->assertSame(0, $user->accessLevel());
        $new_user_acess_level = self::$default_test_admin->changeUserAccessLevel(self::$uuid_user, 1);
        $this->assertContains('success', $new_user_acess_level);
        $this->assertArrayHasKey('message', $new_user_acess_level['data']);
    }
}
