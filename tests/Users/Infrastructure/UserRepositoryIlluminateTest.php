<?php

namespace Tests\Users\Infrastructure;

use App\Users\Domain\User;
use App\Users\Domain\UserHandler;
use App\Users\Domain\UserRepositoryInterface;
use App\Users\Domain\ValueObjects\Email;
use App\Users\Domain\ValueObjects\Password;
use App\Users\Domain\ValueObjects\UserName;
use App\Users\Infrastructure\UserRepositoryIlluminate;
use Phinx\Config\Config;
use Phinx\Migration\Manager;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use PHPUnit\Framework\TestCase;
use support\DataBase;

class UserRepositoryIlluminateTest extends TestCase
{
    private static string $uuid = 'c6b794f6-64b3-44c5-8d15-ae791f857161';
    private static string $email = 'firstTestUser@fakemail.com';
    private static string $user_name = 'firstTestUser';
    private UserRepositoryInterface $user_repository;

    public static function setUpBeforeClass(): void
    {
        $configArray = require('phinx.php');
        $configArray['environments']['test'] = [
            'adapter'    => 'sqlite',
            'connection' => DataBase::connection()->getPdo()
        ];
        $config = new Config($configArray);
        $manager = new Manager($config, new StringInput(' '), new NullOutput());
        $manager->rollback('test');
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

    public function setUp(): void
    {
        $this->user_repository = new UserRepositoryIlluminate();
    }

    public function testGetUserAndEmailWithWrongUUID()
    {
        $user = $this->user_repository->getUserlByUUID('1234-123-123-1234');
        $this->assertEquals(null, $user);
    }

    public function testGetUserAndEmailWithRightUUID()
    {
        $user = $this->user_repository->getUserlByUUID(self::$uuid);
        $this->assertEquals(self::$uuid, $user->uuid);
        $this->assertEquals(self::$user_name, $user->user_name);
        $this->assertEquals(self::$email, $user->email);
        $this->assertEquals(false, (bool)$user->active);
    }

    public function testGetUserByEmailWithWrongEmail()
    {
        $user = $this->user_repository->getUserByEmail(new Email('wrong@email.com'));
        $this->assertEquals(null, $user);
    }

    public function testGetUserByEmailWithRightEmail()
    {
        $user = $this->user_repository->getUserByEmail(new Email(self::$email));
        $this->assertEquals(self::$uuid, $user->uuid);
        $this->assertEquals(self::$user_name, $user->user_name);
        $this->assertEquals(self::$email, $user->email);
        $this->assertEquals(false, empty($user->password));
        $this->assertEquals(false, (bool)$user->active);
    }

    public function testUpdateUserNameWithWrongUUID()
    {
        $update_user_name = $this->user_repository->updateUserName('1234-123-123-123-1234', new UserName('UserName'));
        $this->assertEquals(false, $update_user_name);
    }

    public function testUpdateUserNameWithRightUUID()
    {
        $user = $this->user_repository->getUserlByUUID(self::$uuid);
        $this->assertEquals(self::$user_name, $user->user_name);
        $update_user_name = $this->user_repository->updateUserName(self::$uuid, new UserName('UserName'));
        $this->assertEquals(true, $update_user_name);
        $user = $this->user_repository->getUserlByUUID(self::$uuid);
        $this->assertEquals('UserName', $user->user_name);
    }

    public function testUpdateEmailWithWrongUUID()
    {
        $update_email = $this->user_repository->updateEmail('1234-123-123-123-1234', new Email('email@test.com'));
        $this->assertEquals(false, $update_email);
    }

    public function testUpdateEmailWithRightUUID()
    {
        $user = $this->user_repository->getUserlByUUID(self::$uuid);
        $this->assertEquals(self::$email, $user->email);
        $update_email = $this->user_repository->updateEmail(self::$uuid, new Email('new@email.com'));
        $this->assertEquals(true, $update_email);
        $user = $this->user_repository->getUserlByUUID(self::$uuid);
        $this->assertEquals('new@email.com', $user->email);
    }

    public function testUpdatePasswordWithWrongUUID()
    {
        $update_password = $this->user_repository->updatePassword('1234-123-123-123-1234', new Password('PassWord1'));
        $this->assertEquals(false, $update_password);
    }

    public function testUpdatePasswordWithRightUUID()
    {
        $update_password = $this->user_repository->updatePassword(self::$uuid, new Password('PassWord1'));
        $this->assertEquals(true, $update_password);
    }

    public function testRegisterWithDuplicatedUUID()
    {
        $register = $this->user_repository->register(
            self::$uuid,
            new UserName('UserName'),
            new Email('email@test.com'),
            new Password('PassWord123'),
            false,
            '13213213123213123'
        );
        $this->assertEquals(false, $register);
    }

    public function testRegisterWithRightData()
    {
        $register = $this->user_repository->register(
            '12345678-1234-1234-123456789123',
            new UserName('UserName'),
            new Email('email@test.com'),
            new Password('PassWord123'),
            false,
            '13213213123213123'
        );
        $this->assertEquals(true, $register);
    }

    public function testActiveUserWithWrongActivationCode()
    {
        $user_activated = $this->user_repository->activate('123123213123213');
        $this->assertEquals(false, $user_activated);
    }

    public function testActiveUserWithRightActivationCode()
    {
        $user_activated = $this->user_repository->activate('newInvalidHashJustForTests1234');
        $this->assertEquals(true, $user_activated);
    }

    public function testDeleteUserWithWrongUUID()
    {
        $user_deleted = $this->user_repository->delete('123123213123213');
        $this->assertEquals(false, $user_deleted);
    }

    public function testDeleteUserWithRightUUID()
    {
        $user_deleted = $this->user_repository->delete(self::$uuid);
        $this->assertEquals(true, $user_deleted);
    }
}
