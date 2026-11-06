<?php

/**
 * Proxy seguro para entrega de documentación y archivos protegidos.
 * Estándar PHP 8.5.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Security\Crypto;
use App\Infrastructure\Ui\Http\ErrorPage;
use App\Config\DB;

// 1. Control de acceso: Authentication-First
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['usuario_id']
    ?? $_SESSION['idUsuario']
    ?? $_SESSION['documento']
    ?? null;

if (!$userId) {
    ErrorPage::render(401, 'No Autorizado', 'Debes iniciar sesión en la plataforma SAGE para ver este documento.');
}

// 2. Obtener y descifrar el token de archivo
$token = $_GET['token'] ?? $_GET['t'] ?? '';
if ($token === '') {
    ErrorPage::render(400, 'Solicitud Inválida', 'No se proporcionó un token de documento.');
}

$fileData = Crypto::decryptQuery($token);
if (empty($fileData) || empty($fileData['path'])) {
    ErrorPage::render(403, 'Enlace Inválido o Expirado', 'El token del archivo es incorrecto o ha sido alterado.');
}

$requestedPath = $fileData['path'];
$agenteId = $fileData['idAgente'] ?? 0;

// 3. Prevención de Directory Traversal: El módulo solo conoce SU PROPIO directorio de almacenamiento
$moduleStorageDir = getenv('STORAGE_PATH') ?: ($_SERVER['DOCUMENT_ROOT'] . '/storage/uploads');
$moduleStorageDir = rtrim($moduleStorageDir, '/');

// Validación estricta: la ruta solicitada debe pertenecer exclusivamente al almacenamiento de este módulo
$canonicalBase = realpath($moduleStorageDir) ?: $moduleStorageDir;
$isValidPrefix = (str_starts_with($requestedPath, $moduleStorageDir) || str_starts_with($requestedPath, $canonicalBase));

if (!$isValidPrefix || str_contains($requestedPath, '..')) {
    ErrorPage::render(403, 'Acceso Denegado', 'Ruta de archivo no autorizada o fuera del módulo.');
}

$realBasePath = $moduleStorageDir;

// 4. Migración "On-The-Fly" (al vuelo si tiene formato legacy)
if (!file_exists($requestedPath)) {
    // Si no existe en la ruta moderna, comprobar si existe en la ruta legacy y migrarlo
    $legacyFallback = $realBasePath . '/legacy/' . basename($requestedPath);
    if (file_exists($legacyFallback)) {
        $hash = substr(md5_file($legacyFallback), 0, 8);
        $entityId = $fileData['entityId'] ?? $fileData['id'] ?? $fileData['idAgente'] ?? 'doc';
        $newFilename = "{$entityId}_" . date('Ymd') . "_{$hash}.pdf";
        $newPath = $realBasePath . '/' . $newFilename;
        rename($legacyFallback, $newPath);

        // Si el módulo registra rutas en base de datos, actualizar de forma transparente:
        // $stmt = $pdo->prepare("UPDATE {tbl_modulo_archivos} SET ruta = :nuevaRuta WHERE ruta = :antiguaRuta");
        // $stmt->execute([':nuevaRuta' => $newPath, ':antiguaRuta' => $requestedPath]);

        $requestedPath = $newPath;
    } else {
        ErrorPage::render(404, 'Archivo No Encontrado', 'El documento solicitado no existe o fue dado de baja.');
    }
}

// 5. Entrega por Stream (Readfile) con cabeceras de seguridad
$mime = mime_content_type($requestedPath) ?: 'application/pdf';
$filename = basename($requestedPath);

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . filesize($requestedPath));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Limpiar buffer previo para no corromper el binario
if (ob_get_level()) {
    ob_end_clean();
}

readfile($requestedPath);
exit;
