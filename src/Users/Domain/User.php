<?php

declare(strict_types=1);

namespace App\Users\Domain;

use App\Users\Domain\ValueObjects\UserName;
use App\Users\Domain\ValueObjects\Email;
use App\Users\Domain\ValueObjects\Exceptions\InvalidEmail;
use App\Users\Domain\ValueObjects\Exceptions\InvalidPassword;
use App\Users\Domain\ValueObjects\Exceptions\InvalidUserName;
use App\Users\Domain\ValueObjects\Password;
use App\Users\Infrastructure\UserCachedRepository;
use App\Users\Infrastructure\UserRepositoryIlluminate;

class User
{
    private string $uuid;
    private UserName $user_name;
    private Email $email;
    private Password $password;
    private bool $active;
    private int $access_level;

    public function __construct(
        string $uuid,
        UserName $user_name,
        Email $email,
        bool $active = false,
        int $access_level = 0
    ) {
        $this->uuid = $uuid;
        $this->user_name = $user_name;
        $this->email = $email;
        $this->active = (bool) $active;
        $this->access_level = $access_level;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    protected function getRepository(): UserRepositoryInterface
    {
        return new UserRepositoryIlluminate();
    }

    protected function getCachedRepository(): UserCachedRepository
    {
        return new UserCachedRepository();
    }

    public function userName(): UserName
    {
        return $this->user_name;
    }

    public function updateUserName(string $user_name): array
    {
        if ((string) $this->user_name === $user_name) {
            return [
                'status' => 'fail',
                'data' => ['user_name' => trans('The new username is the same as the old username.')]
            ];
        }
        try {
            $this->user_name = new UserName($user_name);
        } catch (InvalidUserName $error) {
            return [
                'status' => 'fail',
                'data' => ['user_name' => $error->getMessage()]
            ];
        }
        if ($this->getRepository()->updateUserName($this->uuid, $this->user_name)) {
            $this->getCachedRepository()->addUserToModifiedUserList($this->uuid);
            return [
                'status' => 'success',
                'data' => ['user_name' => trans('Username updated successfully.')]
            ];
        }
        return [
            'status' => 'fail',
            'data' => ['message' => trans('Error, please try again.')]
        ];
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function updateEmail(string $email): array
    {
        if ((string) $this->email === $email) {
            return [
                'status' => 'fail',
                'data' => ['email' => trans('The new email is the same as the old email.')]
            ];
        }
        try {
            $email = new Email($email);
        } catch (InvalidEmail $email_exception) {
            return [
                'status' => 'fail',
                'data' => ['email' => $email_exception->getMessage()]
            ];
        }
        if ($this->getRepository()->checkEmailInUse($email)) {
            return ['status' => 'fail', 'data' => ['email' => trans('This email already in use.')]];
        };
        if ($this->getRepository()->updateEmail($this->uuid, $email)) {
            $this->getCachedRepository()->addUserToModifiedUserList($this->uuid);
            return [
                'status' => 'success',
                'data' => ['email' => trans('Email updated successfully.')]
            ];
        }
        return [
            'status' => 'fail',
            'data' => ['message' => trans('Error, please try again.')]
        ];
    }

    public function password(): Password
    {
        return $this->password;
    }

    public function updatePassword(string $password, string $confirm_password): array
    {
        if ($password !== $confirm_password) {
            return [
                'status' => 'fail',
                'data' => ['password' => trans('The 2 passwords must be the same.')]
            ];
        }
        try {
            $this->password = new Password($password);
        } catch (InvalidPassword $password_exception) {
            return [
                'status' => 'fail',
                'data' => ['password' => $password_exception->getMessage()]
            ];
        }
        if ($this->getRepository()->updatePassword($this->uuid, $this->password)) {
            return [
                'status' => 'success',
                'data' => ['password' => trans('Password updated successfully.')]
            ];
        }
        return [
            'status' => 'fail',
            'data' => ['message' => trans('Error, please try again.')]
        ];
    }

    public function active(): bool
    {
        return $this->active;
    }

    public function accessLevel(): int
    {
        return $this->access_level;
    }
}
