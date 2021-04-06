<?php

declare(strict_types=1);

namespace App\Users\Domain\ValueObjects;

use App\Users\Domain\ValueObjects\Exceptions\InvalidUserName;

class UserName
{
    private string $user_name;

    public function __construct(string $user_name)
    {
        $this->checkUserName($user_name);
    }

    private function checkUserName(string $user_name): void
    {
        if (preg_match('/^(?=.*[\\s])/', $user_name)) {
            throw new InvalidUserName(trans('Your username cannot have whitespaces.'));
        }
        if (strlen($user_name) < 3) {
            throw new InvalidUserName(trans('Your username must be at least 3 characters.'));
        }
        if (strlen($user_name) > 25) {
            throw new InvalidUserName(trans('Your username must be less than 26 characters.'));
        }
        if (!preg_match('/^[A-z]+$/m', $user_name)) {
            throw new InvalidUserName(trans('Your username cannot have special characters.'));
        }
        $this->user_name = $user_name;
    }

    public function __toString(): string
    {
        return $this->user_name;
    }
}
