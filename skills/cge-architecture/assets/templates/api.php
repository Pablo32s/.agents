<?php

/**
 * Front Controller único para la API CGE / SAGE.
 * Registra diagnósticos técnicos en logs diarios (10 días de retención) y devuelve mensajes amigables al usuario.
 * Estándar PHP 8.5.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\DB;
use App\Core\Dispatcher;
use App\Core\Env;
use App\Core\Logger;
use App\Core\Middleware\RoleMiddleware;
use App\Core\Security\Crypto;

$logger = new Logger(__DIR__ . '/../logs');

try {
    Env::load(__DIR__ . '/../.env');
    $db = DB::init();

    if (!empty($_GET['q']) && is_string($_GET['q'])) {
        $unpacked = Crypto::decryptQuery($_GET['q']);
        $_GET = array_merge($_GET, $unpacked);
    }

    $action = $_GET['action'] ?? $_POST['action'] ?? 'listar';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    RoleMiddleware::authorize((string)$action);

    if ($action !== 'export') {
        header('Content-Type: application/json; charset=utf-8');
    }

    $dispatcher = new Dispatcher($db);
    $response = $dispatcher->dispatch((string)$action, (string)$method);

    if ($action !== 'export') {
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
} catch (\Throwable $e) {
    $isClientError = ($e instanceof \InvalidArgumentException) || ($e->getCode() >= 400 && $e->getCode() < 500);
    $httpCode = $isClientError ? ((int)$e->getCode() >= 400 ? (int)$e->getCode() : 400) : 500;

    $traceId = $logger->exception($e, [
        'action' => $_GET['action'] ?? $_POST['action'] ?? 'unknown',
    ]);

    $userMessage = $isClientError
        ? $e->getMessage()
        : "No se pudo completar la consulta en este momento. Por favor, intente nuevamente (Ref: {$traceId}).";

    if (!headers_sent()) {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode([
        'success' => false,
        'traceId' => $traceId,
        'error'   => $userMessage,
    ], JSON_UNESCAPED_UNICODE);
}
