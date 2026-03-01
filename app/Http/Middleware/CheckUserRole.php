<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }

        $user = auth()->user();

        $hasRequiredRole = false;
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                $hasRequiredRole = true;
                break;
            }
        }

        if (!$hasRequiredRole) {
            abort(403, 'Unauthorized. You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
