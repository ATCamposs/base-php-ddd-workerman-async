<?php

namespace App\Users\Domain\Exceptions;

use DomainException;

class InvalidAccessLevel extends DomainException
{
    public function __construct(string $exception)
    {
        parent::__construct($exception);
    }
}
