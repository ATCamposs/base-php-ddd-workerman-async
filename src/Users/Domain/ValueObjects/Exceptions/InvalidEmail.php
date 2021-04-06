<?php

namespace App\Users\Domain\ValueObjects\Exceptions;

use DomainException;

class InvalidEmail extends DomainException
{
    public function __construct(string $exception)
    {
        parent::__construct($exception);
    }
}
