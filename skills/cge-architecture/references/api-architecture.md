# Arquitectura de Conexión y API — Módulos CGE / SAGE

Este documento describe el patrón de diseño utilizado para la persistencia de datos y la comunicación entre el Frontend (Vanilla JS) y el Backend (PHP 8.5 Puro) de forma 100% modular y desacoplada.

## 1. Conexión a la Base de Datos (Backend)

La arquitectura utiliza un **Bridge Pattern** para desacoplar el sistema moderno de una infraestructura legacy preexistente.

### Ubicación del Componente
- **Clase Central:** `src/Config/DB.php`
- **Dependencia Externa:** `/dic/includes/connDB.inc.php` (Archivo Legacy)

### Funcionamiento
1. El sistema no define las credenciales directamente en su código.
2. La clase `App\Config\DB::init()` busca el archivo legacy que contiene la configuración de los fierros (host, dbname, user, pass).
3. Captura una instancia global de **PDO** (variable `$database`) y la encapsula para su uso en los Repositorios.

### Código de Referencia (PHP 8.5 + SQL Server)
```php
<?php

declare(strict_types=1);

namespace App\Config;

use App\Core\Env;
use PDO;
use RuntimeException;

final class DB
{
    private static ?PDO $conn = null;

    public static function init(?string $envPath = null): PDO
    {
        if (self::$conn !== null) {
            return self::$conn;
        }

        if (isset($GLOBALS['database']) && $GLOBALS['database'] instanceof PDO) {
            self::$conn = $GLOBALS['database'];
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::configureSessionOptions(self::$conn);
            return self::$conn;
        }

        $moduleEnv = $envPath ?? dirname(__DIR__, 2) . '/.env';
        if (is_file($moduleEnv)) {
            Env::load($moduleEnv);
        }

        if (Env::get('DB_HOST') === '') {
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

        $host = Env::get('DB_HOST', defined('HOST') ? (string)constant('HOST') : '');
        if ($host === '') {
            throw new RuntimeException('No se pudo inicializar la conexión a la base de datos SAGE.');
        }

        $port   = Env::get('DB_PORT', '1433');
        $dbName = Env::get('DB_DATABASE', Env::get('DB_NAME', defined('BASE') ? (string)constant('BASE') : 'Sage_php'));
        $user   = Env::get('DB_USERNAME', Env::get('DB_USER', defined('USUARIO') ? (string)constant('USUARIO') : 'usuarioweb'));
        $pass   = Env::get('DB_PASSWORD', Env::get('DB_PASS', defined('PASS') ? (string)constant('PASS') : ''));
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

        throw new RuntimeException('No hay drivers PDO para SQL Server disponibles (sqlsrv o dblib).');
    }
}
```

---

## 2. Infraestructura de la API (Backend)

Se implementó un patrón **Front Controller** con un **Dispatcher** manual para manejar las solicitudes.

### Punto de Entrada Único
- **Archivo:** `public/api.php`
- **Flujo de Trabajo:**
    1. **Carga de Entorno:** Usa `App\Core\Env` para levantar el archivo `.env`.
    2. **Inyección de Dependencias:** Configura servicios en `config/services.php`.
    3. **Enrutamiento:** Define acciones en `config/routes.php`.
    4. **Normalización:** Unifica `$_GET`, `$_POST` y `php://input` (JSON) en un solo mapa de datos.
    5. **Despacho:** La clase `Dispatcher` instancia el `UseCase` correspondiente según el parámetro `action`.

### Estándar de Respuesta
Todas las respuestas (salvo exportaciones binarias) siguen este esquema JSON:
```json
{
  "status": "success | error",
  "data": { ... payload real ... },
  "message": "Descripción de la operación"
}
```

---

## 3. Módulo de Comunicación (Frontend)

El frontend es agnóstico a la implementación interna del servidor; solo conoce el endpoint y las acciones.

### Ubicación del Componente
- **Archivo:** `public/assets/js/modules/Api.js`

### Estrategia de Implementación
Usa el patrón **Service Layer** basado en `fetch` asíncrono.

```javascript
export const Api = {
    // Ejemplo de consulta (GET con parámetros ofuscados en q)
    fetchData: async (encryptedQueryToken) => {
        const res = await fetch(`api.php?q=${encodeURIComponent(encryptedQueryToken)}`);
        return await res.json();
    },

    // Ejemplo de persistencia (POST con JSON e IDs ofuscados)
    save: async (action, payload) => {
        const res = await fetch(`api.php?action=${encodeURIComponent(action)}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        return await res.json();
    }
};
```
