<?php

declare(strict_types=1);

namespace App\Data\User;

final readonly class UserFilterData
{
    public function __construct(
        public ?string $search  = null,
        public ?string $role    = null,
        public ?string $status  = null,
        public int     $page    = 1,
        public int     $perPage = 15,
    ) {
    }

    /**
     * Create DTO from request parameters.
     *
     * @param array<string, mixed> $params
     */
    public static function fromArray(array $params): self
    {
        $search  = isset($params['search']) && ! empty(trim((string) $params['search'])) ? trim((string) $params['search']) : null;
        $role    = isset($params['role']) && ! empty(trim((string) $params['role'])) && $params['role'] !== 'all' ? trim((string) $params['role']) : null;
        $status  = isset($params['status']) && ! empty(trim((string) $params['status'])) && $params['status'] !== 'all' ? trim((string) $params['status']) : null;
        $page    = max(1, (int) ($params['page'] ?? 1));
        $perPage = max(1, min(100, (int) ($params['per_page'] ?? $params['perPage'] ?? 15)));

        return new self(
            search:  $search,
            role:    $role,
            status:  $status,
            page:    $page,
            perPage: $perPage,
        );
    }
}
