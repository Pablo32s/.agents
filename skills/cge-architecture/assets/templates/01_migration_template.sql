-- =================================================================
-- MIGRACIÓN DE BASE DE DATOS CGE / SAGE
-- Versión: 01_inicial.sql
-- Descripción: Creación de estructura inicial / cambios de esquema
-- =================================================================

-- 1. Tablas nuevas con prefijo consistente
CREATE TABLE IF NOT EXISTS tbl_modulo_ejemplo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    id_usuario_creacion INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_estado (estado),
    INDEX idx_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Registro o ajuste de rol en la configuración (si aplica)
-- INSERT INTO roles_modulos (id_rol, modulo, accion) VALUES (555, 'modulo_ejemplo', 'consultar');
