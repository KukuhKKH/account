<?php

declare(strict_types=1);

namespace App\Exceptions\User;

class CannotResetOwnPasswordException extends UserDomainException
{
    public function __construct(
        string $message = 'Anda tidak dapat mereset kata sandi akun Anda sendiri melalui endpoint ini. Silakan gunakan menu Profil Pengguna & Kunci Keamanan dengan menyertakan kata sandi lama.'
    ) {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return 403;
    }

    public function getErrorCode(): string
    {
        return 'CANNOT_RESET_OWN_PASSWORD';
    }
}
