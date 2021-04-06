<?php

namespace Tests\Infrastructure;

use App\Infrastructure\RepositoryIlluminate;
use support\DataBase;
use PHPUnit\Framework\TestCase;
use Phinx\Config\Config;
use Phinx\Migration\Manager;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

class RepositoryIlluminateTest extends TestCase
{
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
    }

    public function setUp(): void
    {
        $this->repository_illuminate = new RepositoryIlluminate();
    }

    public function testRegisterEmailSent()
    {
        $this->repository_illuminate->registerEmailSent('email@email.com', 'register');
        $email_sent = DataBase::table('emails_sent')->where(['email' => 'email@email.com'])->first();
        $this->assertSame('email@email.com', $email_sent->email);
        $this->assertSame('register', $email_sent->type);
    }

    public function testRegisterEmailSentFailed()
    {
        $this->repository_illuminate->registerEmailSentFailed('email@email.com', 'register', 'smtpError');
        $email_sent_error = DataBase::table('emails_failed')->where(['email' => 'email@email.com'])->first();
        $this->assertSame('email@email.com', $email_sent_error->email);
        $this->assertSame('register', $email_sent_error->type);
        $this->assertSame('smtpError', $email_sent_error->error);
    }
}
