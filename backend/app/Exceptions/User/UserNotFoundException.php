<?php

declare(strict_types=1);

namespace App\Exceptions\User;

class UserNotFoundException extends UserDomainException
{
    public function __construct(string $message = 'Pengguna tidak ditemukan.')
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return 404;
    }

    public function getErrorCode(): string
    {
        return 'USER_NOT_FOUND';
    }
}
