<?php

declare(strict_types=1);

namespace App\Data\Audit;

final readonly class AuditFilterData
{
    public function __construct(
        public ?string $search     = null,
        public ?string $changeType = null,
        public ?int    $userId     = null,
        public ?string $startDate  = null,
        public ?string $endDate    = null,
        public int     $page       = 1,
        public int     $perPage    = 15,
    ) {
    }

    /**
     * Create DTO from request parameters.
     *
     * @param array<string, mixed> $params
     */
    public static function fromArray(array $params): self
    {
        $search     = isset($params['search']) && ! empty(trim((string) $params['search'])) ? trim((string) $params['search']) : null;
        $changeType = isset($params['change_type']) && ! empty(trim((string) $params['change_type'])) && $params['change_type'] !== 'all' ? trim((string) $params['change_type']) : null;
        $userId     = isset($params['user_id']) && is_numeric($params['user_id']) ? (int) $params['user_id'] : null;
        $startDate  = isset($params['start_date']) && ! empty(trim((string) $params['start_date'])) ? trim((string) $params['start_date']) : null;
        $endDate    = isset($params['end_date']) && ! empty(trim((string) $params['end_date'])) ? trim((string) $params['end_date']) : null;
        $page       = max(1, (int) ($params['page'] ?? 1));
        $perPage    = max(1, min(100, (int) ($params['per_page'] ?? $params['perPage'] ?? 15)));

        return new self(
            search:     $search,
            changeType: $changeType,
            userId:     $userId,
            startDate:  $startDate,
            endDate:    $endDate,
            page:       $page,
            perPage:    $perPage,
        );
    }
}
