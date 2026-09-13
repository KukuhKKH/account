<?php

declare(strict_types=1);

namespace App\Enums;

enum UserStatus: string
{
    case Active    = 'active';
    case Suspended = 'suspended';

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
            self::Active    => 'Aktif',
            self::Suspended => 'Ditangguhkan',
        };
    }

    /**
     * Check if status is active.
     */
    public function isActive(): bool
    {
        return $this === self::Active;
    }

    /**
     * Check if status is suspended.
     */
    public function isSuspended(): bool
    {
        return $this === self::Suspended;
    }
}
