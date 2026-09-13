<?php

declare(strict_types=1);

namespace App\Exceptions\User;

class UserAlreadyExistsException extends UserDomainException
{
    public function __construct(string $message = 'Alamat email sudah terdaftar dalam sistem.')
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return 409;
    }

    public function getErrorCode(): string
    {
        return 'USER_ALREADY_EXISTS';
    }
}
