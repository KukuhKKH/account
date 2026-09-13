<?php

declare(strict_types=1);

namespace App\Services\Audit;

final readonly class AuditContext
{
    public function __construct(
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {
    }
}
