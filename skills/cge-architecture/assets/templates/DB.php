<?php

declare(strict_types=1);

namespace App\Config;

use PDO;

/**
 * Bridge Pattern para conexión a base de datos SAGE / CGE.
 * Estándar PHP 8.5.
 */
final class DB
{
    private static ?PDO $conn = null;

    public static function init(): void
    {
        if (self::$conn !== null) {
            return;
        }

        global $database;
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        $legacyPath = $docRoot . '/dic/includes/connDB.inc.php';

        if (file_exists($legacyPath)) {
            require_once $legacyPath;
            self::$conn = $database;
        } else {
            // Standalone / Local fallback (SAGE development standard)
            $driver = getenv('DB_DRIVER') ?: 'dblib';
            $host   = getenv('DB_HOST') ?: 'localhost';
            $dbname = getenv('DB_NAME') ?: '';
            $user   = getenv('DB_USER') ?: '';
            $pass   = getenv('DB_PASS') ?: '';

            $dsn = match ($driver) {
                'sqlsrv' => "sqlsrv:Server={$host};Database={$dbname}",
                'mysql'  => "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                default  => "dblib:host={$host};dbname={$dbname};charset=UTF-8",
            };

            self::$conn = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
    }

    public static function getConnection(): PDO
    {
        if (self::$conn === null) {
            self::init();
        }
        return self::$conn;
    }
}
