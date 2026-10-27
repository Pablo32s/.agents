# Administración de Roles y Seguridad Dinámica de SAGE

Este documento detalla la arquitectura de seguridad y autorización de módulos satélites en sistemas SAGE / CGE, reflejando fielmente la implementación estándar en producción mediante `AuthorizationService`.

---

## 1. Estructura de Datos de Seguridad: `vw_UsuariosRolesPermisos`

La autorización se centraliza a nivel de base de datos a través de la vista del sistema **`vw_UsuariosRolesPermisos`**. Esta vista expone la asociación de usuarios con roles y grupos, y una matriz lógica binaria (0/1):

```sql
SELECT idRol, idGrupo, consultar, agregar, modificar, eliminar
FROM vw_UsuariosRolesPermisos
WHERE (idUsuario = :id1 OR documento = :id2)
```

Campos principales:
*   **`idGrupo`**: Identificador del grupo del usuario (ej: 55, 75 para Super Usuarios).
*   **`idRol`**: Identificador del rol específico asignado a este módulo.
*   **`consultar`**: Permiso de lectura (0/1). Si está vacío o es 0, deniega el acceso con 403.
*   **`agregar`**: Permiso de inserción/escritura (0/1).
*   **`modificar`**: Permiso de actualización/edición (0/1).
*   **`eliminar`**: Permiso de borrado físico/lógico (0/1).

---

## 2. Configuración en el Entorno (`.env`)

```env
# =================================================================
# ADMINISTRACIÓN DE ROLES Y SEGURIDAD DINÁMICA SAGE
# =================================================================
# Grupos de Super Usuario (Devs, Testers, QA - Bypass total en sesión y BD):
SUPER_USER_GROUP=55,75

# Rol específico requerido para este módulo en vw_UsuariosRolesPermisos:
REQUIRED_ROLE_ID=576
```

---

## 3. Flujo del Servicio de Autorización (`AuthorizationService.php`)

El servicio ejecuta la validación en 4 pasos jerárquicos:

1. **Authentication-First**: Valida que exista sesión en PHP (`$_SESSION['usuario_id'] ?? $_SESSION['idUsuario'] ?? ...`). Sin sesión activa, lanza excepción HTTP 401.
2. **Bypass Rápido por Sesión**: Comprueba si `$_SESSION['usuario_grupo']` o `$_SESSION['idGrupo']` pertenece a `SUPER_USER_GROUP` (`55, 75`). Si coincide, retorna inmediatamente acceso total (`isSuperUser => true`) sin necesidad de consultar la base de datos.
3. **Consulta a `vw_UsuariosRolesPermisos`**: Si no vino en sesión, consulta la vista en la base de datos. Si no tiene registros, lanza HTTP 403.
4. **Bypass por Grupo en BD**: Si en alguna fila devuelta `idGrupo` pertenece a `SUPER_USER_GROUP` (`55, 75`), retorna acceso total (`isSuperUser => true`).
5. **Comprobación de Rol Requerido (`REQUIRED_ROLE_ID`)**:
   - Busca la fila donde `idRol === REQUIRED_ROLE_ID`.
   - Si `consultar` está vacío o es 0 -> lanza HTTP 403 ("Permisos Insuficientes. Tu rol no posee habilitado el permiso de consulta.").
   - Retorna la matriz de permisos booleanos (`consultar`, `agregar`, `modificar`, `eliminar`, `isSuperUser => false`, `rol => REQUIRED_ROLE_ID`).
6. **Denegación Final**: Si el usuario no posee el rol requerido ni pertenece a los super grupos, lanza HTTP 403 ("Acceso Denegado. Se requiere el rol ID...").

---

## 4. Integración con `RoleMiddleware.php`

En la capa HTTP (`public/api.php`), `RoleMiddleware::authorize($action)` consume `AuthorizationService` y aplica validación granular:
- Los Super Usuarios (`isSuperUser === true`) pasan libremente a cualquier acción.
- Los usuarios regulares se validan según la acción solicitada (`create` -> `canAdd()`, `update` -> `canModify()`, `delete` -> `canDelete()`, `list` -> `canConsult()`).
