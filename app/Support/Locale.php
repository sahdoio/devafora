<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Single source of truth for the site's supported languages.
 */
class Locale
{
    /** @var array<int, string> */
    public const SUPPORTED = ['pt', 'en'];

    public const DEFAULT = 'pt';

    public const COOKIE = 'locale';

    /**
     * Normalize an arbitrary value to a supported locale, falling back to the default.
     */
    public static function sanitize(?string $locale): string
    {
        $locale = strtolower((string) $locale);

        return in_array($locale, self::SUPPORTED, true) ? $locale : self::DEFAULT;
    }

    public static function isSupported(?string $locale): bool
    {
        return in_array(strtolower((string) $locale), self::SUPPORTED, true);
    }
}
