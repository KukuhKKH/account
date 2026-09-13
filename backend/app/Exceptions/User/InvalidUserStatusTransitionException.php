<?php

declare(strict_types=1);

namespace App\Exceptions\User;

class InvalidUserStatusTransitionException extends UserDomainException
{
    public function __construct(string $message = 'Transisi status akun tidak valid atau tidak diizinkan.')
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return 422;
    }

    public function getErrorCode(): string
    {
        return 'INVALID_STATUS_TRANSITION';
    }
}
