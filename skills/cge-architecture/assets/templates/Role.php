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
    public function __construct(
        public array $permissions,
        public array $assignedRoles = [],
        public bool $isAdmin = false,
    ) {}

    /**
     * Factoría para crear un rol de Administrador con acceso total (roles 55, 75).
     */
    public static function createAdmin(array $adminRoles = [55, 75]): self
    {
        return new self([
            'consultar' => 1,
            'agregar'   => 1,
            'modificar' => 1,
            'eliminar'  => 1,
        ], $adminRoles, true);
    }

    public function canConsult(): bool
    {
        return $this->isAdmin || (bool)($this->permissions['consultar'] ?? false);
    }

    public function canAdd(): bool
    {
        return $this->isAdmin || (bool)($this->permissions['agregar'] ?? false);
    }

    public function canModify(): bool
    {
        return $this->isAdmin || (bool)($this->permissions['modificar'] ?? false);
    }

    public function canDelete(): bool
    {
        return $this->isAdmin || (bool)($this->permissions['eliminar'] ?? false);
    }

    /**
     * Determina si tiene permisos de solo lectura (consulta sí, pero no puede escribir).
     */
    public function isReadOnly(): bool
    {
        return $this->canConsult() && !$this->canAdd() && !$this->canModify() && !$this->canDelete();
    }
}
