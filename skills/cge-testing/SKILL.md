---
name: cge-testing
description: "Trigger: cge-test, testear modulo, test local, probar endpoint, testear api, test db, test auth, phpunit cge, pruebas locales sage. Comprehensive protocol and CLI toolkit for testing CGE/SAGE modules locally against real development databases without Apache, curl, or mock data."
license: Apache-2.0
metadata:
  author: pabloponce
  version: "1.0"
---

# CGE Testing — Protocolo y Herramientas de Testeo Local para SAGE

> **MANDATO ARQUITECTÓNICO**: En el ecosistema CGE/SAGE está estrictamente prohibido intentar levantar servidores Apache locales o depender de llamadas HTTP `curl` para probar la lógica de negocio y los endpoints. 
> Todo testeo debe ejecutarse de forma **directa, determinista y local** utilizando el ejecutable estándar **`cge-test`**, contra la **base de datos de desarrollo real** (mediante `.env`), resolviendo el Gatekeeper de seguridad mediante inyección de sesión SAGE sin alterar el código de producción.

---

## 1. El Problema que Resuelve este Skill

En el entorno tradicional de SAGE:
1. **Acoplamiento Legacy a Apache**: El sistema busca `/dic/includes/connDB.inc.php` a través de `$_SERVER['DOCUMENT_ROOT']`. En local este archivo no existe.
2. **Bloqueo del Gatekeeper SAGE (HTTP 401/403)**: `AuthorizationService` y `RoleMiddleware` verifican `$_SESSION['usuario_id']` y consultan `vw_UsuariosRolesPermisos`. Desde consola o sin sesión iniciada en el navegador, el sistema bloquea cualquier petición.
3. **Cero Datos Mockeados**: Los desarrolladores y agentes de IA deben probar contra datos reales de la base de desarrollo (vía VPN o local), no con stubs ficticios que ocultan problemas de compatibilidad de tipos o SQL Server.

---

## 2. Los Tres Pilares de la Solución

### A. Conector Híbrido (`src/Config/DB.php`)
El conector a base de datos debe priorizar el archivo `.env` local. Si no existe, recurre al conector legacy `/dic/`:

```php
// 1. Si existe configuración en .env, conectar directo vía PDO (desarrollo/testing)
$host = getenv('DB_HOST') ?: (getenv('DEV_DB_HOST') ?: '');
$dbname = getenv('DB_NAME') ?: (getenv('DB_DATABASE') ?: '');
$user = getenv('DB_USER') ?: (getenv('DEV_DB_USERNAME') ?: '');
$pass = getenv('DB_PASS') ?: (getenv('DEV_DB_PASSWORD') ?: '');

if (!empty($host) && !empty($dbname) && !empty($user)) {
    // Conexión directa a SQL Server vía sqlsrv o dblib
    self::$conn = new PDO("dblib:host={$host};dbname={$dbname};charset=UTF-8", $user, $pass, $options);
    return;
}

// 2. Fallback: En producción o servidor Apache remoto usa el bridge legacy
$legacyPath = ($docRoot ? rtrim($docRoot, '/') : '/var/www/html') . '/dic/includes/connDB.inc.php';
if (file_exists($legacyPath)) {
    require_once $legacyPath;
    self::$conn = $database;
}
```

### B. Inyección de Sesión SAGE en CLI
Para testear sin navegador, el entorno CLI inicializa `$_SESSION` antes de invocar el despachador:
- **Modo Desarrollador (Bypass)**: `$_SESSION['usuario_id'] = 9999; $_SESSION['usuario_grupo'] = 55;` (grupo SuperUsuario 55 o 75 para acceso total a todas las operaciones).
- **Modo Usuario Real (Auditoría de Roles)**: `$_SESSION['usuario_id'] = '<DNI_REAL>'; $_SESSION['usuario_grupo'] = 0;` (consulta la vista real `vw_UsuariosRolesPermisos` en la base de datos para validar si ese agente tiene permisos de `consultar`, `agregar`, etc.).

---

## 3. Herramienta CLI Obligatoria: `cge-test`

El comando estándar `cge-test` está instalado en `~/.local/bin/cge-test` y disponible como alias en el ecosistema.

### 1. Verificar Conexión a la Base de Datos de Desarrollo
Prueba la conexión real definida en el `.env` del módulo:
```bash
cge-test competenciaDocente --check-db
# O desde dentro de la carpeta del módulo:
cge-test --check-db
```
*Salida esperada:*
```text
[cge-test] Verificando conexión a base de datos de desarrollo...
  Host:     192.168.1.50
  Database: SAGE_DESARROLLO
  User:     usr_desarrollo
✔ CONEXIÓN EXITOSA (14.2 ms)
  Base activa: SAGE_DESARROLLO
  Motor:       Microsoft SQL Server 2019...
```

### 2. Auditar Roles y Permisos de un Usuario Real en SAGE
Consulta la vista `vw_UsuariosRolesPermisos` en la base de datos de desarrollo para un documento o ID dado:
```bash
cge-test competenciaDocente --check-auth 28123456
```
*Salida esperada:*
```text
[cge-test] Consultando permisos SAGE para usuario: 28123456 en BD real...
✔ 2 rol(es) encontrados:
idRol      | idGrupo    | Consultar  | Agregar    | Modificar  | Eliminar  
--------------------------------------------------------------------
206        | 10         | SÍ         | SÍ         | NO         | NO        
```

### 3. Probar Endpoints de `api.php` Directamente
Ejecuta cualquier acción de la API pasando parámetros en formato JSON:
```bash
# Acción GET simple
cge-test competenciaDocente health_check

# Acción con payload JSON (POST)
cge-test competenciaDocente list '{"page": 1, "limit": 10, "buscar": "profesor"}'

# Probar con un usuario específico para validar que el Gatekeeper aplique sus restricciones:
cge-test competenciaDocente guardar '{"titulo": "Nuevo Titulo"}' --user=28123456 --group=0
```

---

## 4. Requisitos de Entorno en Linux (Driver SQL Server)

Para que PHP CLI en Linux conecte directamente a SQL Server sin Apache ni librerías propietarias complejas:
- Driver recomendado: **`pdo_dblib`** (FreeTDS).
- Instalación en Pop!_OS / Ubuntu / Debian:
  ```bash
  sudo apt install -y php-sybase
  ```
- Verificación: `php -m | grep -i pdo_dblib` (debe retornar `pdo_dblib`).

---

## 5. Reglas para Agentes y Modelos de IA

1. **Nunca pedir `curl` al usuario para validar endpoints**: Usa `cge-test <modulo> <action> '<json>'`.
2. **Validar antes de dar por terminada una refactorización**: Ejecuta `--check-db` y luego el endpoint refactorizado con `cge-test`.
3. **No versionar `.env`**: El archivo `.env` local contiene credenciales reales de desarrollo y está estrictamente excluido del repositorio Git y de `cge-sync push`.
