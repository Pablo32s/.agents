<?php

declare(strict_types=1);

namespace App\Core\Security;

use App\Config\DB;
use App\Core\Env;
use App\Infrastructure\Ui\Http\ErrorPage;
use Exception;
use PDO;
use Throwable;

/**
 * Servicio estándar de autorización y seguridad dinámica SAGE (PHP 8.5).
 * Usa las variables nativas de sesión SAGE ($_SESSION['usuario_id'] y $_SESSION['usuario_grupo']).
 * Otorga acceso directo y total a los grupos de Administradores / Super Usuarios (55, 75).
 * Valida roles habilitados (REQUIRED_ROLE_ID) e idMenuprincipal (MENU_PRINCIPAL_ID) contra dbo.vw_UsuariosRolesPermisos.
 * Contempla la convención SAGE donde permiso = 1 es habilitado y 3 (o 0) es denegado.
 */
final class AuthorizationService
{
    private readonly PDO $db;

    /** @var array<int, int> */
    private readonly array $allowedRoles;

    /** @var array<int, int> */
    private readonly array $allowedMenus;

    /** @var array<int, int> */
    private readonly array $superUserGroups;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? DB::getConnection();

        $rolesRaw = Env::get('REQUIRED_ROLE_ID', Env::get('AUTH_REQUIRED_ROL_ID', '0'));
        $this->allowedRoles = array_values(array_filter(array_map('intval', explode(',', $rolesRaw))));

        $menusRaw = Env::get('MENU_PRINCIPAL_ID', '');
        $this->allowedMenus = array_values(array_filter(array_map('intval', explode(',', $menusRaw))));

        $superGroupsRaw = Env::get('SUPER_USER_GROUP', '55,75');
        $this->superUserGroups = array_values(array_filter(array_map('intval', explode(',', $superGroupsRaw))));
    }

    /**
     * Resuelve el ID de usuario activo en la sesión SAGE priorizando $_SESSION['usuario_id'].
     */
    public static function resolveCurrentUserId(): ?int
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        $userId = $_SESSION['usuario_id']
            ?? $_SESSION['idUsuario']
            ?? $_SESSION['idusuario']
            ?? $_SESSION['usuario']
            ?? $_SESSION['documento']
            ?? null;

        if ($userId === null || $userId === '') {
            return null;
        }

        return (int)$userId;
    }

    /**
     * Resuelve el grupo del usuario activo priorizando $_SESSION['usuario_grupo'].
     */
    public static function resolveCurrentGroupId(): int
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        return (int)($_SESSION['usuario_grupo'] ?? $_SESSION['idGrupo'] ?? 0);
    }

    /**
     * Gatekeeper para vistas HTML: valida el acceso o renderiza ErrorPage 401/403 inmediatamente.
     *
     * @return array{consultar: bool, agregar: bool, modificar: bool, eliminar: bool, isSuperUser: bool, usuarioId: int, usuarioNombre: string, rol: int}
     */
    public static function ensurePageAccess(): array
    {
        try {
            $instance = new self();
            return $instance->checkAccess();
        } catch (Throwable $e) {
            $code = ($e->getCode() === 401) ? 401 : 403;
            ErrorPage::render($code, 'Acceso Restringido', $e->getMessage());
        }
    }

    /**
     * @return array{consultar: bool, agregar: bool, modificar: bool, eliminar: bool, isSuperUser: bool, usuarioId: int, usuarioNombre: string, rol: int}
     * @throws Exception
     */
    public function checkAccess(): array
    {
        $sessionGroup = self::resolveCurrentGroupId();
        $isSuperUser = in_array($sessionGroup, $this->superUserGroups, true);

        $usuarioIdInt = self::resolveCurrentUserId();

        if ($isSuperUser && ($usuarioIdInt === null || $usuarioIdInt <= 0)) {
            $usuarioIdInt = 1;
        }

        if ($usuarioIdInt === null || $usuarioIdInt <= 0) {
            throw new Exception('Sesión inválida o expirada en SAGE. Por favor, reingresá al sistema.', 401);
        }

        $usuarioNombre = (string)($_SESSION['usuario_nombre'] ?? $_SESSION['nombreUsuario'] ?? "Usuario #{$usuarioIdInt}");

        // 1. Bypass rápido para usuario 1 o grupos Super User (55, 75)
        if ($usuarioIdInt === 1 || $isSuperUser) {
            return [
                'consultar'     => true,
                'agregar'       => true,
                'modificar'     => true,
                'eliminar'      => true,
                'isSuperUser'   => true,
                'usuarioId'     => $usuarioIdInt,
                'usuarioNombre' => $usuarioNombre,
                'rol'           => $this->allowedRoles[0] ?? 5,
            ];
        }

        // 2. Consulta a la vista institucional del sistema SAGE
        $stmt = $this->db->prepare(
            'SELECT idRol, idGrupo, idMenuprincipal, consultar, agregar, modificar, eliminar
             FROM dbo.vw_UsuariosRolesPermisos WITH (NOLOCK)
             WHERE (idUsuario = :id1 OR Documento = :id2)'
        );
        $stmt->bindValue(':id1', $usuarioIdInt, PDO::PARAM_INT);
        $stmt->bindValue(':id2', $usuarioIdInt, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            throw new Exception('Acceso Denegado. El usuario no posee roles asignados en el sistema SAGE.', 403);
        }

        // 3. Comprobar si en alguna fila de la BD tiene el grupo de Super Usuario (55, 75)
        foreach ($rows as $row) {
            $rowGroup = (int)($row['idGrupo'] ?? 0);
            if ($rowGroup > 0 && in_array($rowGroup, $this->superUserGroups, true)) {
                return [
                    'consultar'     => true,
                    'agregar'       => true,
                    'modificar'     => true,
                    'eliminar'      => true,
                    'isSuperUser'   => true,
                    'usuarioId'     => $usuarioIdInt,
                    'usuarioNombre' => $usuarioNombre,
                    'rol'           => (int)($row['idRol'] ?? 5),
                ];
            }
        }

        // 4. Comprobar roles habilitados o menús principales asociados
        $canConsult = false;
        $canAdd = false;
        $canModify = false;
        $canDelete = false;
        $matchedRole = 0;

        foreach ($rows as $row) {
            $idRol = (int)($row['idRol'] ?? 0);
            $idMenu = (int)($row['idMenuprincipal'] ?? 0);

            $matchesRole = in_array($idRol, $this->allowedRoles, true);
            $matchesMenu = $idMenu > 0 && in_array($idMenu, $this->allowedMenus, true);

            if ($matchesRole || $matchesMenu) {
                if (self::isPermEnabled($row['consultar'] ?? null)) {
                    $canConsult = true;
                    $matchedRole = $idRol;
                }
                if (self::isPermEnabled($row['agregar'] ?? null)) {
                    $canAdd = true;
                }
                if (self::isPermEnabled($row['modificar'] ?? null)) {
                    $canModify = true;
                }
                if (self::isPermEnabled($row['eliminar'] ?? null)) {
                    $canDelete = true;
                }
            }
        }

        if (!$canConsult) {
            throw new Exception('Permisos Insuficientes. Tu rol actual no posee habilitado el permiso de consulta.', 403);
        }

        return [
            'consultar'     => $canConsult,
            'agregar'       => $canAdd,
            'modificar'     => $canModify,
            'eliminar'      => $canDelete,
            'isSuperUser'   => false,
            'usuarioId'     => $usuarioIdInt,
            'usuarioNombre' => $usuarioNombre,
            'rol'           => $matchedRole,
        ];
    }

    private static function isPermEnabled(mixed $val): bool
    {
        if ($val === null || $val === '') {
            return false;
        }
        $intVal = (int)$val;
        // En SAGE: 1 = Habilitado, 3 o 0 = Deshabilitado
        return $intVal === 1 || ($intVal > 0 && $intVal < 3);
    }
}
