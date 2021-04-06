<?php

declare(strict_types=1);

namespace App\Users\Domain;

use App\Infrastructure\UUIDGenerator;
use App\Users\Domain\ValueObjects\UserName;
use App\Users\Domain\ValueObjects\Email;
use App\Users\Domain\ValueObjects\Exceptions\InvalidEmail;
use App\Users\Domain\ValueObjects\Exceptions\InvalidPassword;
use App\Users\Domain\ValueObjects\Exceptions\InvalidUserName;
use App\Users\Domain\ValueObjects\Password;
use App\Users\Infrastructure\UserEmailPHPMailer;
use App\Users\Infrastructure\UserRepositoryIlluminate;

class UserHandler extends User
{
    public static function registerUser(string $user_name, string $email, string $password): array
    {
        try {
            $user_name = new UserName($user_name);
            $email = new Email($email);
            $password = new Password($password);
        } catch (InvalidUserName $user_name_exception) {
            return ['status' => 'fail', 'data' => ['user_name' => $user_name_exception->getMessage()]
            ];
        } catch (InvalidEmail $email_exception) {
            return ['status' => 'fail', 'data' => ['email' => $email_exception->getMessage()]
            ];
        } catch (InvalidPassword $password_exception) {
            return ['status' => 'fail', 'data' => ['password' => $password_exception->getMessage()]
            ];
        }
        if ((new UserRepositoryIlluminate())->checkEmailInUse($email)) {
            return ['status' => 'fail', 'data' => ['email' => trans('This email already in use.')]];
        };
        $activation_code = sha1((string) $user_name . strtotime('now'));
        $uuid = UUIDGenerator::generate();
        if ((new UserRepositoryIlluminate())->register($uuid, $user_name, $email, $password, false, $activation_code)) {
            (new UserEmailPHPMailer())->sendRegisterEmail($user_name, $email, $activation_code);
            return ['status' => 'success', 'data' => ['message' => trans('You have successfully registered.')]
            ];
        }
        return ['status' => 'fail', 'data' => ['message' => trans('User cannot be registered, please try again.')]
        ];
    }

    public static function getUserByUUID(string $uuid): ?User
    {
        $user_from_db = (new UserRepositoryIlluminate())->getUserlByUUID($uuid);
        if (empty($user_from_db)) {
            return null;
        }
        return new User(
            $uuid,
            new UserName($user_from_db->user_name),
            new Email($user_from_db->email),
            (bool) $user_from_db->active,
            (int) $user_from_db->access_level
        );
    }

    public static function getUserByEmail(Email $email): ?User
    {
        $user_from_db = (new UserRepositoryIlluminate())->getUserByEmail($email);
        if (empty($user_from_db)) {
            return null;
        }
        return new User(
            $user_from_db->uuid,
            new UserName($user_from_db->user_name),
            new Email($user_from_db->email),
            (bool) $user_from_db->active,
            (int) $user_from_db->access_level
        );
    }
}
