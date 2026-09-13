<?php

declare(strict_types=1);

namespace App\Enums;

enum PasswordChangeType: string
{
    case SelfChange  = 'self_change';
    case AdminReset  = 'admin_reset';
    case SystemReset = 'system_reset';
    case WebhookSync = 'webhook_sync';

    /**
     * Get array of all string values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get user-friendly label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::SelfChange  => 'Perubahan Mandiri',
            self::AdminReset  => 'Reset oleh Admin',
            self::SystemReset => 'Reset oleh Sistem',
            self::WebhookSync => 'Sinkronisasi Webhook',
        };
    }

    /**
     * Check if change is self-initiated.
     */
    public function isSelf(): bool
    {
        return $this === self::SelfChange;
    }

    /**
     * Check if change was triggered by administrator.
     */
    public function isAdmin(): bool
    {
        return $this === self::AdminReset;
    }

    /**
     * Check if change was system or automated.
     */
    public function isSystemOrWebhook(): bool
    {
        return $this === self::SystemReset || $this === self::WebhookSync;
    }
}
