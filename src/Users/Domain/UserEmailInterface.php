<?php

declare(strict_types=1);

namespace App\Users\Domain;

use App\Users\Domain\ValueObjects\Email;
use App\Users\Domain\ValueObjects\UserName;

interface UserEmailInterface
{
    public function sendRegisterEmail(UserName $user_name, Email $email, string $activation_code): void;
}
