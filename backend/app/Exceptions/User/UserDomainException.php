<?php

declare(strict_types=1);

namespace App\Exceptions\User;

use RuntimeException;
use Throwable;

abstract class UserDomainException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Associated HTTP status code for this domain exception.
     */
    abstract public function getStatusCode(): int;

    /**
     * Machine-readable error code for API clients.
     */
    abstract public function getErrorCode(): string;
}
