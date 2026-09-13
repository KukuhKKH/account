<?php

declare(strict_types=1);

namespace App\Exceptions\User;

class CannotDeactivateProtectedAccountException extends UserDomainException
{
    public function __construct(string $message = 'Akun Superadmin tidak dapat dinonaktifkan demi kestabilan cluster.')
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return 422;
    }

    public function getErrorCode(): string
    {
        return 'CANNOT_DEACTIVATE_PROTECTED_ACCOUNT';
    }
}
