<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Security\AuthorizationService;
use App\Core\ValueObject\Role;
use Exception;

/**
 * Middleware para validación dinámica de roles SAGE contra vw_UsuariosRolesPermisos.
 * Integra AuthorizationService (Super Usuarios 55, 75 y roles requeridos).
 * Estándar PHP 8.5.
 */
final class RoleMiddleware
{
    /**
     * Valida permisos contra SAGE y retorna el objeto Role.
     * Termina con JSON de error si no está autorizado.
     *
     * @param string $action Acción solicitada (ej: 'list', 'create', 'update', 'delete')
     */
    public static function authorize(string $action): Role
    {
        try {
            $authService = new AuthorizationService();
            $permissions = $authService->checkAccess();

            $role = new Role(
                $permissions,
                [(int)($permissions['rol'] ?? 0)],
                (bool)($permissions['isSuperUser'] ?? false),
            );

            // Si es Super Usuario (grupos 55, 75), posee bypass total
            if ($role->isAdmin()) {
                return $role;
            }

            // Validación granular por acción para usuarios comunes
            $actionLower = strtolower(trim($action));

            if (in_array($actionLower, ['create', 'add', 'save', 'store', 'insertar'], true) && !$role->canAdd()) {
                self::respondError(403, 'Permisos insuficientes: tu rol no tiene autorización de ESCRITURA (agregar).');
            }

            if (in_array($actionLower, ['update', 'edit', 'modify', 'modificar', 'actualizar'], true) && !$role->canModify()) {
                self::respondError(403, 'Permisos insuficientes: tu rol no tiene autorización de MODIFICACIÓN.');
            }

            if (in_array($actionLower, ['delete', 'remove', 'destroy', 'eliminar', 'baja'], true) && !$role->canDelete()) {
                self::respondError(403, 'Permisos insuficientes: tu rol no tiene autorización de ELIMINACIÓN.');
            }

            if (in_array($actionLower, ['list', 'get', 'show', 'status', 'consultar', 'ver', 'listar'], true) && !$role->canConsult()) {
                self::respondError(403, 'Permisos insuficientes: tu rol no tiene autorización de CONSULTA.');
            }

            return $role;
        } catch (Exception $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() <= 500) ? (int)$e->getCode() : 403;
            self::respondError($code, $e->getMessage());
        }
    }

    private static function respondError(int $code, string $message): never
    {
        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode([
            'status'  => 'error',
            'success' => false,
            'code'    => $code,
            'message' => $message,
            'error'   => $message,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
