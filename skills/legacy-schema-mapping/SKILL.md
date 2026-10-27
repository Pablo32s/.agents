---
name: legacy-schema-mapping
description: "Trigger: mapear, mapeo, mapear tablas, legacy schema, map tables, refactorizar modulo, analizar modulo legacy, schema mapping. Analyze legacy PHP/SQL codebases and produce a structured database and session map without context inflation."
license: Apache-2.0
metadata:
  author: pabloponce
  version: "1.0"
---

## Activation Contract

Use this skill when:
- Starting a refactoring or modernization task on an existing legacy repository (PHP/SQL/CGE/SAGE).
- The user requests a schema mapping or impact analysis (*"mapeá las tablas"*, *"analizá qué tablas toca este módulo"*).
- Planning database migrations or Clean Architecture extractions from legacy scripts.

Do not use this skill when:
- Creating a greenfield (brand new) module from scratch with no pre-existing code.

## Hard Rules

1. **Zero Parent Context Inflation (MANDATORY)**: The orchestrator MUST NEVER read all legacy files into its main conversation. It MUST delegate the full codebase scan to an OpenCode worker.
2. **Worker Execution Protocol**:
   - Dispatch the worker using `opencode/big-pickle` (fallback: `google/gemini-3.1-flash-lite`).
   - The contract prompt sent to the worker MUST be written in English.
   - The worker must create or overwrite strictly `docs/schema_mapping.md`.
3. **Required Report Structure**:
   `docs/schema_mapping.md` must contain:
   - **Table & View Matrix**: Table name, operation (SELECT, INSERT, UPDATE, DELETE), and source file references.
   - **Column Inventory**: Key columns read vs mutated (crucial for DTOs and Entities).
   - **Session State Dependencies**: All `$_SESSION` keys detected (e.g. `usuario_id`, `idUsuario`, `documento`).
   - **Legacy Actions & Endpoints**: Query params or actions handled (e.g. `action=guardar`, `action=list`).
   - **Special DB Logic**: Stored procedures, triggers, views (`vw_UsuariosRolesPermisos`), or raw complex queries.

## Execution Steps

1. **Pre-flight Check**: Verify Git status (`git rev-parse --is-inside-work-tree`). Ensure `docs/` directory exists.
2. **Worker Dispatch**: Execute OpenCode in background to perform the scan:
   ```bash
   mkdir -p docs ~/.local/state/opencode-worker
   opencode run --agent worker -m opencode/big-pickle --auto "Task: Perform a complete legacy schema and dependency mapping of all PHP, SQL, and INC files in this repository.
Target file to write: docs/schema_mapping.md.
Requirements:
Scan all codebase files and produce a structured Markdown document containing:
1. Tables and Views: Table name, SQL operations (SELECT/INSERT/UPDATE/DELETE), and file paths.
2. Columns: Columns read vs columns written/updated per table.
3. Session Dependencies: All \$_SESSION keys accessed.
4. Endpoints & Actions: All 'action' or request parameters handled.
5. Critical Business SQL: Highlight stored procedures, views (e.g. vw_UsuariosRolesPermisos), or complex joins.
Do NOT modify any code or other files. Only write docs/schema_mapping.md." > ~/.local/state/opencode-worker/last_run.log 2>&1
   ```
3. **Result Presentation**: Once the worker finishes, the orchestrator reads ONLY `docs/schema_mapping.md` and presents a concise summary (tables count, main entities, identified risks) to the user to begin the refactoring plan.
