<?php

namespace App\Config;

use PDO;

/**
 * Bridge Pattern para conexión a base de datos.
 * Compatible con PHP 7.3.
 */
class DB
{
    /** @var PDO|null */
    private static $conn = null;

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
            // Standalone / Local fallback
            $host = getenv('DB_HOST') ?: 'localhost';
            $dbname = getenv('DB_NAME') ?: '';
            $user = getenv('DB_USER') ?: '';
            $pass = getenv('DB_PASS') ?: '';
            self::$conn = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
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
