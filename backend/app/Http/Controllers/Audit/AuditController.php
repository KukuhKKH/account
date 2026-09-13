<?php

declare(strict_types=1);

namespace App\Http\Controllers\Audit;

use App\Data\Audit\AuditFilterData;
use App\Exceptions\User\UserAccessDeniedException;
use App\Http\Controllers\AbstractController;
use App\Http\Requests\Audit\ListAuditLogRequest;
use App\Http\Resources\PasswordChangeLogResource;
use App\Http\Resources\UserSignInLogResource;
use App\Models\User;
use App\Policies\AuditPolicy;
use App\Services\Audit\AuditService;
use Hypervel\Http\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AuditController
 *
 * BFF Controller for immutable audit trail inspection, sign-in activity history,
 * and security telemetry metrics.
 */
class AuditController extends AbstractController
{
    public function __construct(
        protected AuditService $auditService,
        protected AuditPolicy  $auditPolicy,
    ) {
    }

    /**
     * Display listing of password mutation audit logs.
     */
    public function passwordLogs(ListAuditLogRequest $request): ResponseInterface
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! $this->auditPolicy->viewPasswordLogs($actor)) {
            throw new UserAccessDeniedException('Hanya Administrator yang memiliki wewenang melihat rekaman audit kata sandi.');
        }

        $filter = AuditFilterData::fromArray($request->validated());
        $result = $this->auditService->listPasswordChangeLogs($filter, $actor);

        return response()->json([
            'success' => true,
            'data'    => PasswordChangeLogResource::collection($result['items']),
            'meta'    => [
                'currentPage' => $result['page'],
                'perPage'     => $result['perPage'],
                'total'       => $result['total'],
                'lastPage'    => $result['lastPage'],
            ],
        ]);
    }

    /**
     * Display listing of user sign-in and access logs.
     */
    public function signInLogs(ListAuditLogRequest $request): ResponseInterface
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! $this->auditPolicy->viewSignInLogs($actor)) {
            throw new UserAccessDeniedException('Hanya Administrator yang memiliki wewenang melihat rekaman log akses login.');
        }

        $filter = AuditFilterData::fromArray($request->validated());
        $result = $this->auditService->listSignInLogs($filter, $actor);

        return response()->json([
            'success' => true,
            'data'    => UserSignInLogResource::collection($result['items']),
            'meta'    => [
                'currentPage' => $result['page'],
                'perPage'     => $result['perPage'],
                'total'       => $result['total'],
                'lastPage'    => $result['lastPage'],
            ],
        ]);
    }

    /**
     * Get security telemetry, active IP counts, and cryptographic defense metrics.
     */
    public function stats(Request $request): ResponseInterface
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! $this->auditPolicy->viewSecurityStats($actor)) {
            throw new UserAccessDeniedException('Akses statistik keamanan ditolak.');
        }

        $stats = $this->auditService->getSecurityStats($actor);

        return response()->json([
            'success' => true,
            'data'    => $stats,
        ]);
    }
}
