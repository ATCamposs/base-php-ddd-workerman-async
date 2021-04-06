<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\RepositoryInterface;
use DateTime;
use PDOException;
use support\bootstrap\Log;
use support\DataBase;

class RepositoryIlluminate implements RepositoryInterface
{
    private Database $illuminteDB;

    public function __construct()
    {
        $this->illuminteDB = new Database();
    }

    public function registerEmailSent(string $email, string $type): void
    {
        $emails_failed_table = $this->illuminteDB::table('emails_sent');
        try {
            $emails_failed_table->insert([
                'email' => $email,
                'type' => $type,
                'created' => new DateTime()
            ]);
        } catch (PDOException $error) {
            Log::error($error->getMessage());
        };
    }

    public function registerEmailSentFailed(string $email, string $type, string $error): void
    {
        $emails_failed_table = $this->illuminteDB::table('emails_failed');
        try {
            $emails_failed_table->insert([
                'email' => $email,
                'type' => $type,
                'error' => $error,
                'created' => new DateTime()
            ]);
        } catch (PDOException $error) {
            Log::error($error->getMessage());
        };
    }
}
