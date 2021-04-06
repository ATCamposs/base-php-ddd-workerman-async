<?php

declare(strict_types=1);

namespace App\Domain;

interface RepositoryInterface
{
    public function registerEmailSent(string $email, string $type): void;
    public function registerEmailSentFailed(string $email, string $type, string $error): void;
}
