<?php

declare(strict_types=1);

namespace App\Config;

use App\Core\Env;
use PDO;
use RuntimeException;

/**
 * Database Bridge Pattern para el ecosistema SAGE / CGE (PHP 8.5 + SQL Server).
 * Soporta conexión heredada del servidor ($GLOBALS['database'] / connDB.inc.php),
 * variables de entorno locales (.env) y credenciales centrales de SAGE (pregase/includes/.env).
 */
final class DB
{
    private static ?PDO $conn = null;

    public static function init(?string $envPath = null): PDO
    {
        if (self::$conn !== null) {
            return self::$conn;
        }

        // 1. Si ya existe $database instanciada en el contexto global de SAGE
        if (isset($GLOBALS['database']) && $GLOBALS['database'] instanceof PDO) {
            self::$conn = $GLOBALS['database'];
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::configureSessionOptions(self::$conn);
            return self::$conn;
        }

        // 2. En servidor Apache SAGE (/var/www/html/comun.php presente), intentar puente connDB.inc.php
        $isCli = (PHP_SAPI === 'cli' || !isset($_SERVER['REQUEST_METHOD']));
        if (!$isCli && is_file('/var/www/html/comun.php')) {
            $docRoot = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? '/var/www/html'), '/');
            $legacyCandidates = [
                $docRoot . '/dic/includes/connDB.inc.php',
                dirname(__DIR__, 3) . '/dic/includes/connDB.inc.php',
            ];
            global $database;
            foreach ($legacyCandidates as $legacyPath) {
                if (is_file($legacyPath)) {
                    require_once $legacyPath;
                    if (isset($database) && $database instanceof PDO) {
                        self::$conn = $database;
                        self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                        self::configureSessionOptions(self::$conn);
                        return self::$conn;
                    }
                }
            }
        }

        // 3. Cargar .env local del módulo o fallback al .env central de SAGE
        $moduleEnv = $envPath ?? dirname(__DIR__, 2) . '/.env';
        if (is_file($moduleEnv)) {
            Env::load($moduleEnv);
        }

        if (Env::get('DB_HOST') === '' && Env::get('DEV_DB_HOST') === '') {
            $candidateEnvs = [
                dirname(__DIR__, 3) . '/pregase/includes/.env',
                '/var/www/html/pregase/includes/.env',
                '/mnt/servidor_sage_desarrollo/pregase/includes/.env',
            ];
            foreach ($candidateEnvs as $sharedEnv) {
                if (is_file($sharedEnv)) {
                    Env::load($sharedEnv);
                    if (Env::get('DB_HOST') !== '') {
                        break;
                    }
                }
            }
        }

        $host = Env::get('DB_HOST', Env::get('DEV_DB_HOST', defined('HOST') ? (string)constant('HOST') : ''));
        if ($host === '') {
            throw new RuntimeException('No se pudo inicializar la conexión a la base de datos SAGE (DB_HOST no definido).');
        }

        $port   = Env::get('DB_PORT', '1433');
        $dbName = Env::get('DB_DATABASE', Env::get('DB_NAME', Env::get('DEV_DB_DATABASE', defined('BASE') ? (string)constant('BASE') : 'Sage_php')));
        $user   = Env::get('DB_USERNAME', Env::get('DB_USER', Env::get('DEV_DB_USERNAME', defined('USUARIO') ? (string)constant('USUARIO') : 'usuarioweb')));
        $pass   = Env::get('DB_PASSWORD', Env::get('DB_PASS', Env::get('DEV_DB_PASSWORD', defined('PASS') ? (string)constant('PASS') : '')));
        $driver = Env::get('DB_DRIVER', 'sqlsrv');

        $dsn = self::buildDsn($driver, $host, $port, $dbName);

        self::$conn = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        self::configureSessionOptions(self::$conn);

        return self::$conn;
    }

    public static function getConnection(): PDO
    {
        return self::$conn ?? self::init();
    }

    public static function connect(): PDO
    {
        return self::getConnection();
    }

    private static function configureSessionOptions(PDO $pdo): void
    {
        $pdo->exec('SET DATEFORMAT ymd');
        $pdo->exec('SET QUOTED_IDENTIFIER ON');
        $pdo->exec('SET ARITHABORT ON');
        $pdo->exec('SET ANSI_NULLS ON');
        $pdo->exec('SET CONCAT_NULL_YIELDS_NULL ON');
        $pdo->exec('SET ANSI_WARNINGS ON');
        $pdo->exec('SET ANSI_PADDING ON');
    }

    private static function buildDsn(string $driver, string $host, string $port, string $database): string
    {
        $available = PDO::getAvailableDrivers();

        if (in_array('sqlsrv', $available, true) && $driver === 'sqlsrv') {
            return "sqlsrv:Server={$host},{$port};Database={$database};Encrypt=false;TrustServerCertificate=true";
        }

        if (in_array('dblib', $available, true)) {
            return "dblib:host={$host}:{$port};dbname={$database};charset=UTF-8";
        }

        if (in_array('sqlsrv', $available, true)) {
            return "sqlsrv:Server={$host},{$port};Database={$database};Encrypt=false;TrustServerCertificate=true";
        }

        throw new RuntimeException(
            'No hay drivers PDO para SQL Server disponibles (sqlsrv o dblib). Disponibles: [' . implode(', ', $available) . ']'
        );
    }
}
