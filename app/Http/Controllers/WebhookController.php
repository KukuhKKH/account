<?php

namespace App\Http\Controllers;

use App\Services\WebhookHandler;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected WebhookHandler $webhookHandler,
    ) {}

    /**
     * Handle incoming webhook from Logto
     */
    public function handleLogtoWebhook(Request $request): Response
    {
        try {
            $payload = $request->json()->all();

            Log::info('Logto webhook received', [
                'event'     => $payload['event'] ?? null,
                'data_keys' => array_keys($payload['data'] ?? []),
            ]);

            $event = $payload['event'] ?? null;
            $data  = $payload['data'] ?? [];

            match ($event) {
                'User.Created'  => $this->webhookHandler->handleUserCreated($data),
                'User.Data.Updated'  => $this->webhookHandler->handleUserUpdated($data),
                'User.Deleted'  => $this->webhookHandler->handleUserDeleted($data),
                'User.SignedIn' => $this->webhookHandler->handleUserSignedIn($data),
                default         => Log::warning('Unknown Logto webhook event', ['event' => $event]),
            };

            return response('OK', 200);
        } catch (Exception $e) {
            Log::error('Logto webhook error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response('Internal Server Error', 500);
        }
    }
}
