<?php

namespace App\Users\Domain\ValueObjects\Exceptions;

use DomainException;

class InvalidPassword extends DomainException
{
    public function __construct(string $exception)
    {
        parent::__construct($exception);
    }
}
