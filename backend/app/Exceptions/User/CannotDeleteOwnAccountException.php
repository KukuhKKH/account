<?php

declare(strict_types=1);

namespace App\Exceptions\User;

class CannotDeleteOwnAccountException extends UserDomainException
{
    public function __construct(string $message = 'Anda tidak dapat menghapus akun Anda sendiri.')
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return 422;
    }

    public function getErrorCode(): string
    {
        return 'CANNOT_DELETE_SELF';
    }
}
