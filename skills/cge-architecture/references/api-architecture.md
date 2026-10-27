# Arquitectura de Conexión y API — Módulos CGE / SAGE

Este documento describe el patrón de diseño utilizado para la persistencia de datos y la comunicación entre el Frontend (Vanilla JS) y el Backend (PHP 7.3 Puro) de forma 100% modular y desacoplada.

## 1. Conexión a la Base de Datos (Backend)

La arquitectura utiliza un **Bridge Pattern** para desacoplar el sistema moderno de una infraestructura legacy preexistente.

### Ubicación del Componente
- **Clase Central:** `src/Config/DB.php`
- **Dependencia Externa:** `/dic/includes/connDB.inc.php` (Archivo Legacy)

### Funcionamiento
1. El sistema no define las credenciales directamente en su código.
2. La clase `App\Config\DB::init()` busca el archivo legacy que contiene la configuración de los fierros (host, dbname, user, pass).
3. Captura una instancia global de **PDO** (variable `$database`) y la encapsula para su uso en los Repositorios.

### Código de Referencia (PHP 7.3 Compatible)
```php
<?php
namespace App\Config;

use PDO;

class DB {
    /** @var PDO|null */
    private static $conn = null;

    public static function init(): void {
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
            // Fallback para entornos de desarrollo / standalone
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

    public static function getConnection(): PDO {
        if (self::$conn === null) {
            self::init();
        }
        return self::$conn;
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
