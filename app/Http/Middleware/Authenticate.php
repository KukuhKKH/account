<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            session()->put('url.intended', $request->url());

            return redirect()->route('auth.login')
                ->with('warning', 'Please login to continue.');
        }

        return $next($request);
    }
}
