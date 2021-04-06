<?php

declare(strict_types=1);

namespace App\Users\Domain;

interface AdminRepositoryInterface
{
    public function activateUser(string $uuid): bool;
    public function changeUserAccessLevel(string $uuid, int $access_level): bool;
}
