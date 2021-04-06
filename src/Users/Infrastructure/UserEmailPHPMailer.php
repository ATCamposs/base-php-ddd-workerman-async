<?php

declare(strict_types=1);

namespace App\Users\Infrastructure;

use App\Users\Domain\UserEmailInterface;
use App\Users\Domain\ValueObjects\Email;
use App\Users\Domain\ValueObjects\UserName;
use Workerman\Connection\AsyncTcpConnection;

class UserEmailPHPMailer implements UserEmailInterface
{
    public function __construct()
    {
        $this->async_mail_connection = new AsyncTcpConnection('Text://127.0.0.1:12345');
    }

    public function sendRegisterEmail(UserName $user_name, Email $email, string $activation_code): void
    {
        $task_data = [
            'mail_type' => 'register_account',
            'args' => [
                'user_name' => (string) $user_name,
                'email' => (string) $email,
                'activation_code' => (string) $activation_code
            ],
        ];
        $this->async_mail_connection->send(json_encode($task_data));
        $this->async_mail_connection->connect();
    }
}
