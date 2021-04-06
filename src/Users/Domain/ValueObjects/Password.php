<?php

declare(strict_types=1);

namespace App\Users\Domain\ValueObjects;

use App\Users\Domain\ValueObjects\Exceptions\InvalidPassword;

class Password
{
    private string $password;

    public function __construct(string $password)
    {
        $this->checkPassword($password);
    }

    private function checkPassword(string $password): void
    {
        if (preg_match('/^(?=.*[\\s])/', $password)) {
            throw new InvalidPassword(trans('Your password cannot have whitespaces.'));
        }
        if (strlen($password) < 6) {
            throw new InvalidPassword(trans('Your password must be at least 6 characters.'));
        }
        if (strlen($password) > 14) {
            throw new InvalidPassword(trans('Your password must be less than 15 characters.'));
        }
        if (!preg_match('/^(?=.*[a-z])/', $password)) {
            throw new InvalidPassword(trans('Your password must contain at least 1 lowercase.'));
        }
        if (!preg_match('/^(?=.*[A-Z])/', $password)) {
            throw new InvalidPassword(trans('Your password must contain at least 1 uppercase.'));
        }
        if (!preg_match('/^(?=.*[0-9])/', $password)) {
            throw new InvalidPassword(trans('Your password must contain at least 1 number.'));
        }
        $this->password = $password;
    }

    public function __toString(): string
    {
        return $this->password;
    }
}
