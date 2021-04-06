<?php

declare(strict_types=1);

namespace App\Users\Domain\ValueObjects;

use App\Users\Domain\ValueObjects\Exceptions\InvalidEmail;

class Email
{
    private string $email;

    public function __construct(string $email)
    {
        $this->checkEmail($email);
    }

    private function checkEmail(string $email): void
    {
        if (preg_match('/^(?=.*[\\s])/', $email)) {
            throw new InvalidEmail(trans('Your email cannot have whitespaces.'));
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidEmail(trans('Invalid email address.'));
        }
        $this->email = $email;
    }

    public function __toString(): string
    {
        return $this->email;
    }
}
