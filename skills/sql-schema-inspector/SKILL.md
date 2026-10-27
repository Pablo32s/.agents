---
name: sql-schema-inspector
description: "Trigger: mcp mssql, sql server, ver tablas, estructura tablas, schema db, audita externa, consultar base, mssql_describe_table. Inspect SQL Server schema and cross-verify database definitions safely using MCP."
license: Apache-2.0
metadata:
  author: pabloponce
  version: "1.0"
---

## Activation Contract

Use this skill when:
- Designing or refactoring modules under `cge-architecture` and needing exact column types, nullability, or constraints.
- Verifying legacy SQL queries or stored procedures against real database tables in SAGE / CGE.
- Mapping database columns to PHP 7.3 Entities, DTOs, and ValueObjects.
- Checking real table structures or sample data without risking table locks.

## Core Rules & Guardrails

1. **Zero Locking (READ UNCOMMITTED / NOLOCK)**:
   - When inspecting tables, NEVER perform queries that take Shared Locks (`S` locks), especially if pointing to high-volume or production databases.
   - All tool calls automatically enforce `READ UNCOMMITTED` / `WITH (NOLOCK)`.
2. **Context Poisoning Prevention**:
   - NEVER request a full dump of all tables in SAGE (there are hundreds of legacy tables).
   - Always filter using `mssql_list_tables` with a specific prefix (e.g. `tb_Agentes%`, `vw_Usuarios%`, `tb_Liquidacion%`).
3. **Cross-Verification Protocol for `cge-architecture`**:
   - Extract raw column references from legacy PHP files (via `legacy-schema-mapping`).
   - Call `mssql_describe_table(table_name)` for each referenced table.
   - Confirm exact SQL types (`smalldatetime`, `varchar`, `int`, `decimal`) and nullability.
   - Map strictly into PHP 7.3 types (using PHPDoc annotations, null coalescing, and exact validation).
4. **Data Sampling Limits**:
   - To inspect format/conventions of existing records, use `mssql_get_table_sample(table_name, limit: 5)`. Never query open-ended datasets.

## Available MCP Tools

* `mssql_list_tables(search_pattern, include_views)`: Find tables matching pattern.
* `mssql_describe_table(table_name)`: Get columns, types, lengths, nullability, PK, and FKs.
* `mssql_get_table_sample(table_name, limit)`: Safe 5-row sample with `WITH (NOLOCK)`.
* `mssql_read_query(query, limit)`: Read-only SELECT capped at 100 rows with `READ UNCOMMITTED`.
