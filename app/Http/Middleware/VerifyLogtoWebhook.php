<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyLogtoWebhook
{
    /**
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $webhookSecret = config('services.logto.webhook_secret');
        if (!$webhookSecret) {
            Log::warning('Logto webhook secret not configured - webhook verification skipped');
            return response()->json(['message' => 'Server Configuration Error'], 500);
        }

        $signature = $request->header('logto-signature-sha-256')
            ?? $request->header('X-Logto-Signature')
            ?? $request->header('X-Signature')
            ?? $request->header('Signature');

        if (!$signature) {
            Log::warning('Logto webhook: missing signature header');
            return response('Unauthorized', 401);
        }

        $payload = $request->getContent();

        $computedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        if (!hash_equals($computedSignature, $signature)) {
            $base64Signature = base64_encode(hash_hmac('sha256', $payload, $webhookSecret, true));

            if (!hash_equals($base64Signature, $signature)) {
                Log::warning('Logto webhook: signature mismatch', [
                    'provided' => $signature,
                    'ip' => $request->ip()
                ]);

                return response()->json(['message' => 'Invalid Signature'], 401);
            }
        }

        return $next($request);
    }
}
