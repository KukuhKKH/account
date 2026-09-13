<?php

declare(strict_types=1);

namespace App\Exceptions\User;

class CannotDeleteLastAdministratorException extends UserDomainException
{
    public function __construct(string $message = 'Tidak dapat menghapus Administrator atau Superadmin terakhir demi kestabilan sistem.')
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return 422;
    }

    public function getErrorCode(): string
    {
        return 'CANNOT_DELETE_LAST_ADMIN';
    }
}
