<?php

declare(strict_types=1);

namespace App\Exceptions\User;

class UserAccessDeniedException extends UserDomainException
{
    public function __construct(string $message = 'Anda tidak memiliki hak akses untuk melakukan tindakan ini.')
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return 403;
    }

    public function getErrorCode(): string
    {
        return 'ACCESS_DENIED';
    }
}
