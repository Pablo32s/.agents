<?php

namespace App\Core\Security;

use App\Config\DB;
use PDO;
use Exception;

/**
 * Servicio estándar de autorización y seguridad dinámica SAGE.
 * Valida grupos de Super Usuarios (55, 75) y roles específicos contra vw_UsuariosRolesPermisos.
 * Compatible con PHP 7.3.
 */
class AuthorizationService
{
    /** @var PDO */
    private $db;

    /** @var int */
    private $requiredRole;

    /** @var array */
    private $superUserGroups;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?: DB::getConnection();

        // Rol requerido para este módulo específico
        $this->requiredRole = (int)(getenv('REQUIRED_ROLE_ID') ?: (getenv('AUTH_REQUIRED_ROL_ID') ?: 0));

        // Grupos de Super Usuarios (acceso total/bypass para Devs, Testers y Administradores)
        $superGroupsRaw = (string)(getenv('SUPER_USER_GROUP') ?: '55,75');
        $this->superUserGroups = array_map('intval', array_filter(array_map('trim', explode(',', $superGroupsRaw))));
    }

    /**
     * Valida la sesión y los permisos del usuario en SAGE.
     *
     * @return array Matriz de permisos normalizada
     * @throws Exception Si la sesión es inválida (401) o los permisos son insuficientes (403)
     */
    public function checkAccess(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['usuario_id'] 
            ?? $_SESSION['idUsuario'] 
            ?? $_SESSION['documento'] 
            ?? $_SESSION['nombreUsuario'] 
            ?? null;

        if ($userId === null) {
            throw new Exception("Sesión inválida o expirada en SAGE.", 401);
        }

        // 1. Comprobación rápida en sesión: si ya posee el grupo de Super Usuario
        $sessionGroup = (int)($_SESSION['usuario_grupo'] ?? $_SESSION['idGrupo'] ?? 0);
        if (in_array($sessionGroup, $this->superUserGroups, true)) {
            return [
                'consultar'   => true,
                'agregar'     => true,
                'modificar'   => true,
                'eliminar'    => true,
                'isSuperUser' => true,
                'rol'         => $this->requiredRole
            ];
        }

        // 2. Consulta a la vista del sistema SAGE
        $stmt = $this->db->prepare(
            "SELECT idRol, idGrupo, consultar, agregar, modificar, eliminar
             FROM vw_UsuariosRolesPermisos
             WHERE (idUsuario = :id1 OR documento = :id2)"
        );
        $stmt->execute([':id1' => $userId, ':id2' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            throw new Exception("Acceso Denegado. El usuario no posee roles asignados en el sistema SAGE.", 403);
        }

        // 3. Comprobar si en alguna fila de la BD tiene el grupo de Super Usuario
        foreach ($rows as $row) {
            $rowGroup = (int)($row['idGrupo'] ?? 0);
            if (in_array($rowGroup, $this->superUserGroups, true)) {
                return [
                    'consultar'   => true,
                    'agregar'     => true,
                    'modificar'   => true,
                    'eliminar'    => true,
                    'isSuperUser' => true,
                    'rol'         => $this->requiredRole
                ];
            }
        }

        // 4. Comprobar si tiene el rol requerido para este módulo
        foreach ($rows as $row) {
            if ((int)($row['idRol'] ?? 0) === $this->requiredRole) {
                if (empty($row['consultar'])) {
                    throw new Exception("Permisos Insuficientes. Tu rol no posee habilitado el permiso de consulta.", 403);
                }

                return [
                    'consultar'   => !empty($row['consultar']),
                    'agregar'     => !empty($row['agregar']),
                    'modificar'   => !empty($row['modificar']),
                    'eliminar'    => !empty($row['eliminar']),
                    'isSuperUser' => false,
                    'rol'         => $this->requiredRole
                ];
            }
        }

        throw new Exception("Acceso Denegado. Se requiere el rol ID {$this->requiredRole} para acceder a este módulo.", 403);
    }
}
