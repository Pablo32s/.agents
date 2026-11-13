-- ==============================================================================
-- MIGRACIÓN DE BASE DE DATOS CGE / SAGE (MICROSOFT SQL SERVER / T-SQL)
-- Versión: 01_create_tables.sql
-- Descripción: Creación idempotente de estructura inicial en SQL Server
-- ==============================================================================

SET ANSI_NULLS ON;
SET QUOTED_IDENTIFIER ON;
GO

-- 1. Creación idempotente de tabla en dbo
IF OBJECT_ID(N'dbo.tb_ModuloEjemplo', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.tb_ModuloEjemplo (
        idModuloEjemplo INT IDENTITY(1,1) NOT NULL,
        codigo VARCHAR(50) NOT NULL,
        descripcion VARCHAR(255) NOT NULL,
        activo BIT NOT NULL CONSTRAINT DF_tb_ModuloEjemplo_activo DEFAULT (1),
        idUsuarioCreacion INT NOT NULL,
        fechaCreacion DATETIME2(0) NOT NULL CONSTRAINT DF_tb_ModuloEjemplo_fechaCreacion DEFAULT (SYSDATETIME()),
        fechaModificacion DATETIME2(0) NULL,
        CONSTRAINT PK_tb_ModuloEjemplo PRIMARY KEY CLUSTERED (idModuloEjemplo ASC),
        CONSTRAINT UQ_tb_ModuloEjemplo_codigo UNIQUE (codigo)
    );
END;
GO

-- 2. Índices no agrupados idempotentes
IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'IX_tb_ModuloEjemplo_activo'
      AND object_id = OBJECT_ID(N'dbo.tb_ModuloEjemplo')
)
BEGIN
    CREATE NONCLUSTERED INDEX IX_tb_ModuloEjemplo_activo
        ON dbo.tb_ModuloEjemplo (activo)
        INCLUDE (codigo, descripcion);
END;
GO
