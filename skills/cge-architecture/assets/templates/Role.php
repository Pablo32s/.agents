<?php

declare(strict_types=1);

namespace App\Core\ValueObject;

/**
 * Value Object para encapsular permisos SAGE de vw_UsuariosRolesPermisos.
 * Soporta roles múltiples, granularidad (CRUD) y bypass de administradores.
 * Estándar PHP 8.5.
 */
final readonly class Role
{
    /**
     * @param array<string, bool|int|string> $permissions
     * @param array<int, int> $assignedRoles
     */
    public function __construct(
        public array $permissions,
        public array $assignedRoles = [],
        public bool $isAdmin = false,
    ) {}

    /**
     * Factoría para crear un rol de Administrador con acceso total (grupos 55, 75).
     *
     * @param array<int, int> $adminRoles
     */
    public static function createAdmin(array $adminRoles = [55, 75]): self
    {
        return new self([
            'consultar' => true,
            'agregar'   => true,
            'modificar' => true,
            'eliminar'  => true,
        ], $adminRoles, true);
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    /**
     * @return array<int, int>
     */
    public function getAssignedRoles(): array
    {
        return $this->assignedRoles;
    }

    public function canConsult(): bool
    {
        return $this->isAdmin || !empty($this->permissions['consultar']);
    }

    public function canAdd(): bool
    {
        return $this->isAdmin || !empty($this->permissions['agregar']);
    }

    public function canModify(): bool
    {
        return $this->isAdmin || !empty($this->permissions['modificar']);
    }

    public function canDelete(): bool
    {
        return $this->isAdmin || !empty($this->permissions['eliminar']);
    }

    /**
     * Determina si tiene permisos de solo lectura (consulta sí, pero no puede escribir).
     */
    public function isReadOnly(): bool
    {
        return $this->canConsult() && !$this->canAdd() && !$this->canModify() && !$this->canDelete();
    }
}
