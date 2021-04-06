<?php

/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace process;

use App\Domain\RepositoryInterface;
use App\Infrastructure\RepositoryIlluminate;
use PHPMailer\PHPMailer\PHPMailer;
use Workerman\Connection\TcpConnection;

class AsyncPHPMailer
{
    protected PHPMailer $mail_service;
    protected RepositoryInterface $repository;

    public function onConnect(TcpConnection $connection)
    {
        $this->mail_service = new PHPMailer();
        $this->mail_service->isSMTP();
        //Debug your email
        //$this->mail_service->SMTPDebug = 2;
        $this->mail_service->Host = env('SMTP_HOST', null);
        $this->mail_service->Port = env('SMTP_PORT', null);
        $this->mail_service->SMTPAuth = true;
        $this->mail_service->Username = env('SMTP_USERNAME', null);
        $this->mail_service->Password = env('SMTP_PASSWORD', null);
        $this->mail_service->Timeout = 5;
        $this->mail_service->setFrom(env('DEFAULT_EMAIL', null), 'Your Name');
        //$mail->addReplyTo('test@hostinger-tutorials.com', 'Your Name');
        $this->repository = new RepositoryIlluminate();
    }

    public function onMessage(TcpConnection $connection, $data): void
    {
        $task_data = json_decode($data, true);
        $data = $task_data['args'];
        switch ($task_data['mail_type']) {
            case 'register_account':
                $this->sendRegisterEmail($data['user_name'], $data['email'], $data['activation_code']);
                break;
            default:
                break;
        }
    }

    protected function sendRegisterEmail($user_name, $email, $activation_code): void
    {
        $this->mail_service->addAddress($email);
        $this->mail_service->Subject = trans('Activate Your Account');
        $message = file_get_contents('./src/Users/Presentation/EmailsTemplate/newUserEmail.html');
        $message = str_replace('##user_name##', $user_name, $message);
        $activation_link = env('HOST', null) . '/users/activate?activation_code=' . $activation_code;
        $message = str_replace('##link_to_activate##', $activation_link, $message);
        $this->mail_service->msgHTML($message);
        //$this->mail_service->Body = 'This is a plain text message body';
        //$this->mail_service->addAttachment('test.txt');
        if ($this->mail_service->send()) {
            $error = explode('. ', $this->mail_service->ErrorInfo)[0];
            $this->repository->registerEmailSent($email, 'register');
            return;
        }
        $error = explode('. ', $this->mail_service->ErrorInfo)[0];
        $this->repository->registerEmailSentFailed($email, 'register', $error);
    }
}
