<?php

declare(strict_types=1);

namespace App\Users\Infrastructure;

use App\Users\Domain\UserRepositoryInterface;
use App\Users\Domain\ValueObjects\Email;
use App\Users\Domain\ValueObjects\UserName;
use App\Users\Domain\ValueObjects\Password;
use DateTime;
use PDOException;
use support\bootstrap\Log;
use support\DataBase;

class UserRepositoryIlluminate implements UserRepositoryInterface
{
    private Database $illuminteDB;

    public function __construct()
    {
        $this->illuminteDB = new Database();
    }

    public function getUserlByUUID(string $uuid): ?object
    {
        $users_table = $this->illuminteDB::table('users');
        try {
            $selected_activation_hash = $users_table->where('uuid', $uuid);
            return $selected_activation_hash->first(['uuid', 'user_name', 'email', 'active', 'access_level']);
        } catch (PDOException $error) {
            Log::error($error->getMessage());
            return null;
        };
    }

    public function checkEmailInUse(Email $email): bool
    {
        $users_table = $this->illuminteDB::table('users');
        try {
            $selected_email = $users_table->select('email');
            $user_email = $selected_email->where('email', $email);
            if (!empty($user_email->first())) {
                return true;
            }
            return false;
        } catch (PDOException $error) {
            Log::error($error->getMessage());
            return null;
        };
    }

    public function getUserByEmail(Email $email): ?object
    {
        $users_table = $this->illuminteDB::table('users');
        try {
            $selected_email = $users_table->where('email', $email);
            return $selected_email->first([
                'uuid',
                'user_name',
                'email',
                'password',
                'active',
                'access_level'
            ]);
        } catch (PDOException $error) {
            Log::error($error->getMessage());
            return null;
        };
    }

    public function updateUserName(string $uuid, UserName $user_name): bool
    {
        $now = new DateTime();
        $users_table = $this->illuminteDB::table('users');
        try {
            $selected_user = $users_table->where('uuid', $uuid);
            if ($selected_user->update(['user_name' => $user_name, 'modified' => $now])) {
                return true;
            };
        } catch (PDOException $error) {
            Log::error($error->getMessage());
            return false;
        };
        return false;
    }

    public function updateEmail(string $uuid, Email $email): bool
    {
        $now = new DateTime();
        $users_table = $this->illuminteDB::table('users');
        try {
            $selected_user = $users_table->where('uuid', $uuid);
            if ($selected_user->update(['email' => $email, 'modified' => $now])) {
                return true;
            };
        } catch (PDOException $error) {
            Log::error($error->getMessage());
            return false;
        };
        return false;
    }

    public function updatePassword(string $uuid, Password $password): bool
    {
        $now = new DateTime();
        $users_table = $this->illuminteDB::table('users');
        try {
            $selected_user = $users_table->where('uuid', $uuid);
            if ($selected_user->update(['password' => $password, 'modified' => $now])) {
                return true;
            };
        } catch (PDOException $error) {
            Log::error($error->getMessage());
            return false;
        };
        return false;
    }

    public function register(
        string $uuid,
        UserName $user_name,
        Email $email,
        Password $password,
        bool $active,
        string $activation_code
    ): bool {
        $users_table = $this->illuminteDB::table('users');
        $now = new DateTime();
        try {
            return $users_table->insert([
                'uuid' => $uuid,
                'user_name' => $user_name,
                'email' => $email,
                'password' => password_hash((string) $password, PASSWORD_DEFAULT),
                'active' => $active,
                'access_level' => 0,
                'activation_hash' => $activation_code,
                'created' => $now,
                'modified' => $now
            ]);
        } catch (PDOException $error) {
            Log::error($error->getMessage());
            return false;
        };
    }

    public function activate(string $activation_code): bool
    {
        $now = new DateTime();
        $users_table = $this->illuminteDB::table('users');
        try {
            $selected_user = $users_table->where('activation_hash', $activation_code);
            if ($selected_user->update(['active' => 1, 'activation_hash' => null, 'modified' => $now])) {
                return true;
            };
        } catch (PDOException $error) {
            Log::error($error->getMessage());
            return false;
        };
        return false;
    }

    public function delete(string $uuid): bool
    {
        $users_table = $this->illuminteDB::table('users');
        $selected_user = $users_table->where('uuid', $uuid);
        try {
            if ($selected_user->delete()) {
                return true;
            };
        } catch (PDOException $error) {
            Log::error($error->getMessage());
            return false;
        };
        return false;
    }
}
