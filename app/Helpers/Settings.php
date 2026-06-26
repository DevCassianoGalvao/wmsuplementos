<?php

declare(strict_types=1);

namespace Maia\Helpers;

class Settings
{
    private static array $cache = [];
    private static bool $loaded = false;

    public static function get(string $key, string $default = ''): string
    {
        if (!self::$loaded) {
            self::load();
        }
        return self::$cache[$key] ?? $default;
    }

    private static function load(): void
    {
        self::$loaded = true;
        try {
            $rows = db()->query('SELECT `key`, `value` FROM settings')->fetchAll();
            foreach ($rows as $row) {
                self::$cache[$row['key']] = (string)$row['value'];
            }
        } catch (\Throwable) {
            // silently fail if settings table not ready
        }
    }
}
