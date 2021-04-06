<?php

declare(strict_types=1);

namespace App\Users\Domain;

use App\Users\Domain\ValueObjects\UserName;
use App\Users\Domain\ValueObjects\Email;
use App\Users\Domain\ValueObjects\Password;

interface UserRepositoryInterface
{
    public function getUserlByUUID(string $uuid): ?object;
    public function checkEmailInUse(Email $email): bool;
    public function getUserByEmail(Email $email): ?object;
    public function updateUserName(string $uuid, UserName $user_name): bool;
    public function updateEmail(string $uuid, Email $email): bool;
    public function updatePassword(string $uuid, Password $password): bool;
    public function register(
        string $uuid,
        UserName $user_name,
        Email $email,
        Password $password,
        bool $active,
        string $activation_code
    ): bool;
    public function activate(string $activation_code): bool;
    public function delete(string $uuid): bool;
}
