<?php

declare(strict_types=1);

namespace App\Users\Infrastructure;

use App\Users\Domain\AdminRepositoryInterface;
use DateTime;
use PDOException;
use support\bootstrap\Log;
use support\DataBase;

class AdminRepositoryIlluminate implements AdminRepositoryInterface
{
    private Database $illuminteDB;

    public function __construct()
    {
        $this->illuminteDB = new Database();
    }

    public function activateUser(string $uuid): bool
    {
        $now = new DateTime();
        $users_table = $this->illuminteDB::table('users');
        try {
            $selected_user = $users_table->where('uuid', $uuid);
            if ($selected_user->update(['active' => 1, 'activation_hash' => null, 'modified' => $now])) {
                return true;
            };
        } catch (PDOException $error) {
            Log::error($error->getMessage());
            return false;
        };
        return false;
    }

    public function changeUserAccessLevel(string $uuid, int $access_level): bool
    {
        $now = new DateTime();
        $users_table = $this->illuminteDB::table('users');
        try {
            $selected_user = $users_table->where('uuid', $uuid);
            if ($selected_user->update(['access_level' => $access_level, 'modified' => $now])) {
                return true;
            };
        } catch (PDOException $error) {
            Log::error($error->getMessage());
            return false;
        };
        return false;
    }
}
