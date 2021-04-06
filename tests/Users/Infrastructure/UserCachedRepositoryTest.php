<?php

namespace Tests\Users\Infrastructure;

use App\Users\Domain\User;
use App\Users\Domain\UserHandler;
use App\Users\Domain\ValueObjects\Email;
use App\Users\Infrastructure\UserCachedRepository;
use Phinx\Config\Config;
use Phinx\Migration\Manager;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use PHPUnit\Framework\TestCase;
use support\bootstrap\Redis;
use support\DataBase;

class UserCachedRepositoryTest extends TestCase
{
    private static string $uuid = 'c6b794f6-64b3-44c5-8d15-ae791f857161';
    private static string $email = 'firstTestUser@fakemail.com';
    private static string $user_name = 'firstTestUser';
    private UserCachedRepository $user_cached_repository;

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
        (new Redis())::set('modified_user_list', null);
        $this->user_cached_repository = new UserCachedRepository();
    }

    public function testAddUserToModifiedUserList()
    {
        $this->user_cached_repository->addUserToModifiedUserList('c6b794f6-64b3-44c5-8d15-ae791f857161');
        $list = $this->user_cached_repository->getmodifiedUserList();
        $this->assertEquals([0 => 'c6b794f6-64b3-44c5-8d15-ae791f857161'], $list);
        $this->user_cached_repository->addUserToModifiedUserList('c6b794f6-1234-1234-1234-ae791f857161');
        $list = $this->user_cached_repository->getmodifiedUserList();
        $this->assertEquals(
            [
                0 => 'c6b794f6-64b3-44c5-8d15-ae791f857161',
                1 => 'c6b794f6-1234-1234-1234-ae791f857161'
            ],
            $list
        );
        $this->user_cached_repository->addUserToModifiedUserList('c6b794f6-64b3-44c5-8d15-ae791f857161');
        $list = $this->user_cached_repository->getmodifiedUserList();
        $this->assertEquals(
            [
                0 => 'c6b794f6-64b3-44c5-8d15-ae791f857161',
                1 => 'c6b794f6-1234-1234-1234-ae791f857161'
            ],
            $list
        );
        $this->user_cached_repository->removeUserFromModifiedUserList('c6b794f6-1234-1234-1234-ae791f857161');
        $list = $this->user_cached_repository->getmodifiedUserList();
        $this->assertEquals([0 => 'c6b794f6-64b3-44c5-8d15-ae791f857161'], $list);
    }

    public function testGetUserlByUUID()
    {
        $user = $this->user_cached_repository->getUserlByUUID('c6b794f6-64b3-44c5-8d15-ae791f857161');
        $this->assertEquals(self::$uuid, $user->uuid);
        $this->assertEquals(self::$user_name, $user->user_name);
        $this->assertEquals(self::$email, $user->email);
        $this->assertEquals(false, (bool) $user->active);
    }
}
