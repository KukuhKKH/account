<?php

declare(strict_types=1);

namespace App\Exceptions\User;

class InvalidCurrentPasswordException extends UserDomainException
{
    public function __construct(string $message = 'Kata sandi lama yang Anda masukkan salah.')
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return 422;
    }

    public function getErrorCode(): string
    {
        return 'INVALID_CURRENT_PASSWORD';
    }
}
