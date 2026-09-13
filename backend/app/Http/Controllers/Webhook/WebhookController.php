<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhook;

use App\Data\Webhook\LogtoWebhookEventData;
use App\Http\Controllers\AbstractController;
use App\Services\Webhook\LogtoWebhookHandler;
use Hypervel\Http\Request;
use Hypervel\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

/**
 * Class WebhookController
 *
 * Handles incoming real-time webhooks from Logto SSO and external auth providers
 * with cryptographic HMAC-SHA256 signature verification.
 */
class WebhookController extends AbstractController
{
    public function __construct(
        protected LogtoWebhookHandler $webhookHandler,
    ) {
    }

    /**
     * Handle incoming Logto Webhook event.
     */
    public function handleLogtoWebhook(Request $request): ResponseInterface
    {
        $rawPayload = (string) $request->getBody();
        $payload    = json_decode($rawPayload, true);

        if (! is_array($payload)) {
            $payload = $request->all();
        }

        if ($rawPayload === '') {
            $rawPayload = json_encode($payload) ?: '';
        }

        $signature = $request->header('logto-signature-sha256')
            ?? $request->header('x-logto-signature')
            ?? $request->header('authorization');

        // 1. Verify HMAC Signature
        if (! $this->webhookHandler->verifySignature($rawPayload, $signature, $payload)) {
            Log::warning('Security Alert: Invalid Logto webhook signature received', [
                'ip'        => $request->ip(),
                'signature' => $signature,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Tanda tangan webhook kriptografis tidak valid.',
            ], 401);
        }

        // 2. Validate payload structure
        if (! is_array($payload) || empty($payload)) {
            return response()->json([
                'success' => false,
                'message' => 'Format payload webhook JSON tidak valid.',
            ], 400);
        }

        // 3. Process event through handler
        $eventData = LogtoWebhookEventData::fromArray($payload);
        $result    = $this->webhookHandler->handleEvent($eventData);

        return response()->json([
            'success' => true,
            'message' => 'Event webhook berhasil diproses.',
            'data'    => $result,
        ]);
    }
}
