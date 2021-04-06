<?php

declare(strict_types=1);

namespace App\Users\Domain;

use App\Users\Domain\Exceptions\InvalidAccessLevel;
use App\Users\Domain\ValueObjects\UserName;
use App\Users\Domain\ValueObjects\Email;
use App\Users\Domain\ValueObjects\Exceptions\InvalidEmail;
use App\Users\Domain\ValueObjects\Exceptions\InvalidUserName;
use App\Users\Infrastructure\AdminRepositoryIlluminate;
use App\Users\Infrastructure\UserCachedRepository;
use App\Users\Infrastructure\UserRepositoryIlluminate;

class Admin extends User
{
    public const ACCESS_TABLE = [
        0 => 'User',
        1 => 'Moderator',
        5 => 'Admin'
    ];

    public function __construct(
        string $uuid,
        UserName $user_name,
        Email $email,
        bool $active = false,
        int $access_level = 0
    ) {
        parent::__construct($uuid, $user_name, $email, $active, $access_level);
        $this->checkAccessLevel($this->accessLevel());
    }

    protected function checkAccessLevel(int $access_level)
    {
        if ($access_level < 5) {
            throw new InvalidAccessLevel(trans('Invalid access level.'));
        }
    }

    protected function getRepository(): UserRepositoryInterface
    {
        return new UserRepositoryIlluminate();
    }

    protected function getCachedRepository(): UserCachedRepository
    {
        return new UserCachedRepository();
    }

    public function changeUserUserName(string $uuid, string $user_name): array
    {
        $user = UserHandler::getUserByUUID($uuid);
        if (!$user) {
            return ['status' => 'fail', 'data' => ['uuid' => trans('User not found.')]];
        }
        if ((string) $user->userName() === $user_name) {
            return [
                'status' => 'fail',
                'data' => ['user_name' => trans('The new username is the same as the old username.')]
            ];
        }
        try {
            $new_user_name = new UserName($user_name);
        } catch (InvalidUserName $error) {
            return [
                'status' => 'fail',
                'data' => ['user_name' => $error->getMessage()]
            ];
        }
        if ($this->getRepository()->updateUserName($user->uuid(), $new_user_name)) {
            $this->getCachedRepository()->addUserToModifiedUserList($user->uuid());
            return ['status' => 'success', 'data' => ['user_name' => trans('Username updated successfully.')]];
        }
        return ['status' => 'fail', 'data' => ['message' => trans('Error, please try again.')]];
    }

    public function changeUserEmail(string $uuid, string $email): array
    {
        $user = UserHandler::getUserByUUID($uuid);
        if (!$user) {
            return ['status' => 'fail', 'data' => ['uuid' => trans('User not found.')]];
        }
        if ((string) $user->email() === $email) {
            return ['status' => 'fail', 'data' => ['email' => trans('The new email is the same as the old email.')]];
        }
        try {
            $email = new Email($email);
        } catch (InvalidEmail $email_exception) {
            return ['status' => 'fail', 'data' => ['email' => $email_exception->getMessage()]];
        }
        if ($this->getRepository()->checkEmailInUse($email)) {
            return ['status' => 'fail', 'data' => ['email' => trans('This email already in use.')]];
        };
        if ($this->getRepository()->updateEmail($user->uuid(), $email)) {
            (new UserCachedRepository())->addUserToModifiedUserList($user->uuid());
            return ['status' => 'success', 'data' => ['email' => trans('Email updated successfully.')]];
        }
        return ['status' => 'fail', 'data' => ['message' => trans('Error, please try again.')]];
    }

    public function activateUser(string $uuid): array
    {
        $user = UserHandler::getUserByUUID($uuid);
        if (!$user) {
            return ['status' => 'fail', 'data' => ['uuid' => trans('User not found.')]];
        }
        if ($user->active()) {
            return [
                'status' => 'fail',
                'data' => ['message' => trans('User is already active.')]
            ];
        }
        if ((new AdminRepositoryIlluminate())->activateUser($user->uuid())) {
            (new UserCachedRepository())->addUserToModifiedUserList($user->uuid());
            return ['status' => 'success', 'data' => ['message' => trans('User successfully activated.')]];
        }
        return ['status' => 'fail', 'data' => ['message' => trans('Error, please try again.')]];
    }

    public function changeUserAccessLevel(string $uuid, $access_level): array
    {
        $user = UserHandler::getUserByUUID($uuid);
        if (!$user) {
            return ['status' => 'fail', 'data' => ['uuid' => trans('User not found.')]];
        }
        if (!key_exists($access_level, $this::ACCESS_TABLE) || $access_level >= $this->accessLevel()) {
            return ['status' => 'fail', 'data' => ['access_level' => trans('Invalid access level.')]];
        }
        if ((new AdminRepositoryIlluminate())->changeUserAccessLevel($user->uuid(), $access_level)) {
            (new UserCachedRepository())->addUserToModifiedUserList($user->uuid());
            return ['status' => 'success', 'data' => ['message' => trans('Access level successfully updated.')]];
        }
    }

    public static function getAdminByUUID(string $uuid): ?Admin
    {
        $user_from_db = (new UserRepositoryIlluminate())->getUserlByUUID($uuid);
        if (empty($user_from_db)) {
            return null;
        }
        return new Admin(
            $uuid,
            new UserName($user_from_db->user_name),
            new Email($user_from_db->email),
            (bool) $user_from_db->active,
            (int) $user_from_db->access_level
        );
    }
}
