<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Cargador ligero de variables de entorno (.env) sin dependencias externas.
 * Estándar PHP 8.5.
 */
final class Env
{
    /** @var array<string, string> */
    private static array $vars = [];

    public static function load(?string $path = null): void
    {
        $resolvedPath = $path ?? dirname(__DIR__, 2) . '/.env';
        if (!is_file($resolvedPath) || !is_readable($resolvedPath)) {
            return;
        }

        $lines = file($resolvedPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            return;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            $parts = explode('=', $trimmed, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = trim($parts[0]);
            $val = trim($parts[1], " \t\n\r\0\x0B\"'");

            if ($key !== '') {
                self::$vars[$key] = $val;
                if (getenv($key) === false) {
                    putenv("{$key}={$val}");
                }
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
            }
        }
    }

    public static function get(string $key, string $default = ''): string
    {
        if (isset(self::$vars[$key])) {
            return self::$vars[$key];
        }

        $envVal = getenv($key);
        if ($envVal !== false) {
            return (string)$envVal;
        }

        return isset($_ENV[$key]) ? (string)$_ENV[$key] : $default;
    }
}
