<?php

/**
 * Punto de entrada visual del módulo (UI Web).
 * Aplica Gatekeeper de acceso antes de renderizar cualquier vista.
 * Compatible con PHP 7.3.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Security\AuthorizationService;
use App\Infrastructure\Ui\Http\ErrorPage;

try {
    // 1. Llave de acceso: debe tener al menos permiso de consulta para ver la pantalla
    $authService = new AuthorizationService();
    $permissions = $authService->checkAccess();

    if (empty($permissions['consultar']) && empty($permissions['isSuperUser'])) {
        ErrorPage::render(403, 'Acceso Denegado', 'Tu rol actual no posee permisos de consulta para este módulo.');
    }
} catch (\Throwable $e) {
    $code = $e->getCode() >= 400 && $e->getCode() <= 500 ? $e->getCode() : 403;
    ErrorPage::render($code, 'Control de Acceso', $e->getMessage());
}

// 2. Si pasó el gatekeeper, renderiza el layout limpio (sin lógica pesada embebida)
$isReadOnly = empty($permissions['agregar']) && empty($permissions['modificar']) && empty($permissions['isSuperUser']);
$isSuperUser = !empty($permissions['isSuperUser']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo CGE / SAGE</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body data-readonly="<?php echo $isReadOnly ? 'true' : 'false'; ?>">
    <header class="module-header">
        <h1>Gestión de Módulo</h1>
        <?php if (!$isReadOnly): ?>
            <button id="btn-nuevo" class="btn btn-primary">Nuevo Registro</button>
        <?php endif; ?>
    </header>

    <main class="module-content">
        <!-- Contenedor de tabla o componentes modulares -->
        <div id="app-root"></div>
    </main>

    <!-- Módulos ES6 desacoplados o Web Components -->
    <script type="module" src="assets/js/app.js"></script>
</body>
</html>
