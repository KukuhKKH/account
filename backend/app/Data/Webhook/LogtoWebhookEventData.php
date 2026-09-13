<?php

declare(strict_types=1);

namespace App\Data\Webhook;

final readonly class LogtoWebhookEventData
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public string  $event,
        public array   $data      = [],
        public ?string $createdAt = null,
        public ?string $sessionId = null,
        public ?string $userAgent = null,
        public ?string $ip        = null,
        public ?string $hookId    = null,
    ) {
    }

    /**
     * Create DTO from decoded webhook JSON array.
     *
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        $event     = (string) ($payload['event'] ?? $payload['type'] ?? 'unknown');
        $data      = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $createdAt = isset($payload['createdAt']) ? (string) $payload['createdAt'] : null;
        $sessionId = isset($payload['sessionId']) ? (string) $payload['sessionId'] : null;
        $userAgent = isset($payload['userAgent']) ? (string) $payload['userAgent'] : null;
        $ip        = isset($payload['ip']) ? (string) $payload['ip'] : null;
        $hookId    = isset($payload['hookId']) ? (string) $payload['hookId'] : null;

        return new self(
            event:     $event,
            data:      $data,
            createdAt: $createdAt,
            sessionId: $sessionId,
            userAgent: $userAgent,
            ip:        $ip,
            hookId:    $hookId,
        );
    }

    /**
     * Extract Logto user ID from event data payload.
     */
    public function getLogtoUserId(): ?string
    {
        if (isset($this->data['id']) && is_string($this->data['id']) && trim($this->data['id']) !== '') {
            return trim($this->data['id']);
        }

        if (isset($this->data['userId']) && is_string($this->data['userId']) && trim($this->data['userId']) !== '') {
            return trim($this->data['userId']);
        }

        return null;
    }

    /**
     * Extract user email from event data.
     */
    public function getEmail(): ?string
    {
        if (isset($this->data['primaryEmail']) && is_string($this->data['primaryEmail'])) {
            return strtolower(trim($this->data['primaryEmail']));
        }

        if (isset($this->data['email']) && is_string($this->data['email'])) {
            return strtolower(trim($this->data['email']));
        }

        return null;
    }

    /**
     * Extract user name from event data.
     */
    public function getName(): ?string
    {
        if (isset($this->data['name']) && is_string($this->data['name'])) {
            return trim($this->data['name']);
        }

        if (isset($this->data['username']) && is_string($this->data['username'])) {
            return trim($this->data['username']);
        }

        return null;
    }

    /**
     * Extract phone number from event data.
     */
    public function getPhone(): ?string
    {
        if (isset($this->data['primaryPhone']) && is_string($this->data['primaryPhone'])) {
            return trim($this->data['primaryPhone']);
        }

        if (isset($this->data['phone']) && is_string($this->data['phone'])) {
            return trim($this->data['phone']);
        }

        return null;
    }

    /**
     * Extract avatar URL from event data.
     */
    public function getAvatar(): ?string
    {
        if (isset($this->data['avatar']) && is_string($this->data['avatar'])) {
            return trim($this->data['avatar']);
        }

        return null;
    }

    /**
     * Extract custom data object from event data.
     *
     * @return array<string, mixed>
     */
    public function getCustomData(): array
    {
        if (isset($this->data['customData']) && is_array($this->data['customData'])) {
            return $this->data['customData'];
        }

        return [];
    }

    /**
     * Check if user is suspended.
     */
    public function isSuspended(): ?bool
    {
        if (isset($this->data['isSuspended']) && is_bool($this->data['isSuspended'])) {
            return $this->data['isSuspended'];
        }

        return null;
    }
}
