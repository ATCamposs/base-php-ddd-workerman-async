<?php

declare(strict_types=1);

namespace App\Users\Application;

use App\Users\Domain\TokenSecurityHandlerInterface;
use App\Users\Domain\UserHandler;
use App\Users\Domain\UserRepositoryInterface;
use App\Users\Domain\ValueObjects\Email;
use App\Users\Domain\ValueObjects\Exceptions\InvalidEmail;
use App\Users\Domain\ValueObjects\Exceptions\InvalidPassword;
use App\Users\Domain\ValueObjects\Password;
use App\Users\Infrastructure\JwtSecurityHandler;
use App\Users\Infrastructure\UserCachedRepository;
use App\Users\Infrastructure\UserRepositoryIlluminate;

class UserServices
{
    private UserRepositoryInterface $user_repository;
    private TokenSecurityHandlerInterface $token_security;
    private $user_cached_repository;

    public function __construct()
    {
        $this->user_repository = new UserRepositoryIlluminate();
        $this->user_cached_repository = new UserCachedRepository();
        $this->token_security = new JwtSecurityHandler();
    }

    public function login(string $email, string $password): array
    {
        try {
            $email = new Email($email);
            $password = new Password($password);
        } catch (InvalidEmail $email_exception) {
            return [
                'status' => 'fail',
                'data' => ['email' => $email_exception->getMessage()]
            ];
        } catch (InvalidPassword $password_exception) {
            return [
                'status' => 'fail',
                'data' => ['password' => $password_exception->getMessage()]
            ];
        }
        $user =  $this->user_repository->getUserByEmail($email);
        if (empty($user)) {
            return ['status' => 'fail', 'data' => ['email' => trans('User not found.')]];
        }
        if ((bool) $user->active === false) {
            return ['status' => 'fail', 'data' => ['email' => trans('User inactive.')]];
        }
        if (password_verify((string) $password, (string) $user->password)) {
            $token = $this->token_security::encrypt(["uuid" => $user->uuid]);
            return ['status' => 'success', 'data' => ['token' => $token]];
        };
        return ['status' => 'fail', 'data' => ['password' => trans('Invalid Password.')]];
    }

    public function register(string $user_name, string $email, string $password, string $confirm_password): array
    {
        if ($password !== $confirm_password) {
            return ['status' => 'fail', 'data' => [
                'password' => 'The 2 passwords must be the same.',
                'confirm_password' => 'The 2 passwords must be the same.'
            ]];
        }
        return UserHandler::registerUser($user_name, $email, $password, $confirm_password);
    }

    public function activate(string $activation_code): array
    {
        if ($this->user_repository->activate($activation_code)) {
            return [
                'status' => 'success',
                'data' => ['message' => trans('User successfully activated.')]
            ];
        }
        return [
            'status' => 'fail',
            'data' => ['message' => trans('Invalid activation code.')]
        ];
    }

    public function delete(string $uuid): array
    {
        if ($this->user_repository->delete($uuid)) {
            return [
                'status' => 'success',
                'data' => ['message' => trans('User successfully removed.')]
            ];
        }
        return [
            'status' => 'fail',
            'data' => ['message' => trans('Invalid User.')]
        ];
    }

    //Use the cachedRepository
    public function checkTokenAuthenticationAndReturnUser(string $uuid): array
    {
        $user = $this->user_cached_repository->getUserlByUUID($uuid);
        if (empty($user)) {
            return ['status' => 'fail', 'data' => ['token' => trans('You authorization code is invalid.')]];
        }
        if ((bool) $user->active === false) {
            return ['status' => 'fail', 'data' => ['email' => trans('User inactive.')]];
        }
        return ['status' => 'success', 'data' => ['user' => $user]];
    }
}
