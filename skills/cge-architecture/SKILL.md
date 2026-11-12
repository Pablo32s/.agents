---
name: cge-architecture
description: "Trigger: cge, sage, cge-architecture, php cge, liquidaciones, gestion-alumnos, proyecto-cge, nuevo modulo cge, /mnt/servidor_sage_desarrollo, /mnt/servidor_sage_produccion, local_sage_desarrollo. Standard Clean Architecture for CGE/SAGE systems in PHP 8.5 with modular feature packs."
license: Apache-2.0
metadata:
  author: pabloponce
  version: "3.5"
---

## Activation Contract

Use this skill when:
- Creating, modifying, or refactoring modules in the CGE / SAGE ecosystem.
- Building PHP 8.5 Clean Architecture applications with Vanilla JS frontend.
- Implementing SAGE dynamic authorization, database bridges, unified error pages, ID obfuscation, or optional feature packs.
- The active workspace or conversation touches `/mnt/servidor_sage_desarrollo/`, `/mnt/servidor_sage_produccion/`, or `~/local_sage_desarrollo/`.

### Automatic Redirection Rule (Samba/CIFS Mount Guard)
If the conversation or workspace is opened inside `/mnt/servidor_sage_desarrollo/<modulo>` or `/mnt/servidor_sage_produccion/<modulo>`:
1. Immediately extract the module name `<modulo>`.
2. **DO NOT** perform broad reads, searches, or edits directly on the `/mnt/` mount.
3. Check if `~/local_sage_desarrollo/<modulo>` exists. If not (or if pulling latest code from server), execute:
   ```bash
   cge-sync pull <modulo>
   ```
   *(Alias: `cge-pull <modulo>`). This copies the module via rsync, initializes Git if absent, creates the `desarrollo` base branch, and switches to the dedicated `desarrollo-local` branch. DO NOT run raw `rsync` directly; running `cge-sync` ensures permission approval is granted once by the user and reused across all repositories.*
4. **Mandatory Knowledge Graph Indexing (`cbm index`)**: IMMEDIATELY after pulling or opening a module — and BEFORE any code exploration, search, or modification — execute:
   ```bash
   cbm index <modulo>
   ```
   You MUST ALSO re-run `cbm index <modulo>` after creating, deleting, renaming, or refactoring files/classes so the AST Knowledge Graph stays 100% synchronized at all times.
5. Perform ALL operations (exploration, coding, git, tests) exclusively inside `~/local_sage_desarrollo/<modulo>/` on branch `desarrollo-local`.
   - **Exploration & Navigation (Graph-First)**: Always ensure `cbm index <modulo>` is up to date first, then use `cbm` (`cbm arch <modulo>`, `cbm status <modulo>`) or `codebase-memory-mcp` tools to explore architecture, dependencies, and symbols without reading full files. Use `readfile` (or native file viewer) for precise line inspections.
   - **Direct Surgical Editing**: Apply targeted changes and refactoring directly using native tools (`replace_file_content`, `write_to_file`) or `writefile`. DO NOT delegate single-file surgical fixes to a worker model (avoids double-work and token waste).
   - **Syntax Validation & Re-Indexing**: Immediately verify edited files with `cge-lint <target_file>` (or `phplint <target_file>`) and run `cbm index <modulo>` after structural changes.
6. **Quality Control & Git Audit**: As orchestrator, audit changes via `git status` and `cge-git <modulo> diff` (or `git diff desarrollo`) on `desarrollo-local`. Reserve `opencode-worker` strictly for bulk mechanical changes across multiple files.
7. Run `cge-sync push <modulo>` (or `cge-sync <modulo>`) to push changes back to `/mnt/servidor_sage_desarrollo/<modulo>/`.
8. When preparing the PR, target the pull request against the `desarrollo` branch.

## Core Invariable Rules (Always Active)

1. **Modern PHP 8.5 Strict POO & PSR-4 (MANDATORY)**:
   - 100% Modern PHP 8.5 standard (target server runtime: PHP 8.5). Zero legacy PHP 7.x compatibility shims.
   - **Strict Typing Everywhere**: Declare `declare(strict_types=1);` in all PHP files. Follow PSR-4 and 100% SOLID.
   - **Typed Properties, Constants & Constructor Promotion**: Use native typed properties (`private int $id;`), typed class constants (`private const int RETENTION_DAYS = 10;`), and constructor property promotion for Value Objects and DTOs (`public function __construct(public readonly string $id) {}`).
   - **Readonly Classes, Asymmetric Visibility & Property Hooks**: Mark immutable entities and DTOs as `readonly` (`final readonly class`), or use asymmetric visibility (`public private(set) string $status`) and property hooks where controlled mutation is required.
   - **PHP 8.5 Native Idioms**: Leverage the pipe operator (`|>`), `clone($obj, ['prop' => $val])` for immutable state updates, `#[Override]` attribute on interface/parent implementations, `#[NoDiscard]` where return values are critical, and native array helpers (`array_first()`, `array_last()`, `str_contains()`, `str_starts_with()`, `str_ends_with()`).
   - **Enums, Match & Nullsafe (`?->`)**: Use backed `enum` for domain states/actions, `match` expressions instead of `switch`, and `?->` for optional object graphs.
   - **No Dynamic Properties**: Forbidden. All properties must be declared explicitly with strict types.
2. **Exclusive Frontend Standards — custom-ui & Legacy Browser Compatibility (MANDATORY)**:
   - ALL frontend UI/UX development MUST EXCLUSIVELY use the `custom-ui` skill. NO OTHER UI framework, library, or arbitrary CSS style is permitted.
   - **Legacy Client Browser Guard (Chrome 85–109 / Win7 / Firefox ESR)**: Target school and office PCs with outdated browsers. Use 100% consolidated Vanilla JS (ES6/ES2020) and classic pure CSS (Flexbox, Grid, `:root` hex/rgba variables). **STRICTLY FORBIDDEN** in frontend code: CSS `:has()`, native CSS nesting, `oklch()`, `color-mix()`, `@container`, `popover` API, CSS Anchor Positioning, `@starting-style`, `Object.groupBy()`, or `Array.prototype.toSorted()`.
   - Pure Vanilla JavaScript with zero monoliths: split into ES modules (`public/assets/js/modules/`) or native Web Components (`customElements.define`) with Shadow DOM (`<style>` injection).
   - Use `Api.js` Service Layer for all server calls.
   - Design Tokens (:root) in harmonious Blue & Slate tones (`--bg-main: #f0f4f8;`, `--bg-dark-main: #0b1120;`, `--primary-500: #3b82f6;`, etc.). Strictly avoid pure white (`#fff`) or cold gray backgrounds. Clean CSS Grid & Flexbox, micro-animations, standard NameThatUI naming conventions.
3. **Thin Entry Points & Gatekeeper (MANDATORY)**:
   - `public/index.php` and `public/api.php` MUST be thin despachadores (< 60 lines).
   - **Gatekeeper in `index.php`**: MUST validate `AuthorizationService::checkAccess()` before rendering ANY HTML layout. If `consultar` is disabled, renders `ErrorPage::render(403)` immediately.
   - **Gatekeeper in `api.php`**: `RoleMiddleware::authorize($action)` checks permissions before routing. Never embed SQL, business logic, or large HTML in entry points.
4. **Core Architectural Patterns**:
   - **Database Bridge**: Encapsulate legacy `/dic/includes/connDB.inc.php` via `App\Config\DB::init()`.
   - **Front Controller**: Single entry point `public/api.php` with `Dispatcher` and isolated `UseCase` classes.
   - **SAGE Dynamic Security**: Validate against `vw_UsuariosRolesPermisos` using `RoleMiddleware` and `Role` ValueObject. MUST support:
     - Admin roles bypass `AUTH_ADMIN_ROLES=55,75` (full access for Devs and QA).
     - Multi-role authorization (`AUTH_ALLOWED_ROLES=550,555` or `AUTH_ROLE_READONLY`/`AUTH_ROLE_READWRITE`).
     - Granular permissions mapping (`consultar`, `agregar`, `modificar`, `eliminar`).
   - **Unified Error Handling**: Render `App\Infrastructure\Ui\Http\ErrorPage` with inline SVGs and Material Design 3.
   - **Database Migrations Protocol**: Versioned sequential SQL scripts in `migrations/`: `01_create_tables.sql`, `02_add_columns.sql`.
5. **ID & Query Param Obfuscation (Anti-IDOR & Privacy)**:
   - **NO Raw Database IDs**: The frontend (buttons, HTML attributes `data-id`, JSON payloads) must NEVER receive integer database IDs. All exposed IDs must be encrypted via `App\Core\Security\Crypto::encryptId()` and decrypted on arrival via `Crypto::decryptId()`.
   - **NO Semantic GET Query Strings**: Do NOT expose readable parameters like `?documento=123&idAgente=45`. The entire query block must be packed into a single encrypted token: `?q=TOKEN` via `Crypto::encryptQuery()`, automatically unpacked by `public/api.php`.
6. **Legacy Refactoring Protocol**:
   - Before modifying legacy code, perform a targeted schema mapping of affected tables and columns using `codebase-memory-mcp` (CBM) and local DB tools to conserve context tokens.
7. **Date Handling Standard (Anti-MDY / SQL Server Invariant)**:
   - SAGE SQL Server default collation runs in `us_english` with `DateFormat = mdy`.
   - NEVER pass localized date strings (`d/m/Y`) to SQL queries.
   - All PHP 8.5 DTOs and Repositories MUST format dates in canonical ISO-8601 (`Y-m-d` or `Y-m-d H:i:s`) or use explicit SQL style 120 (`CONVERT(datetime, :fecha, 120)`). Use `DateTimeImmutable` or Date ValueObjects to guarantee zero date-swapping.
8. **Local Development, CBM Indexing, Git Control & Server Sync Protocol (Zero-Lag Workflow)**:
   - **Local Workspace**: All module coding, indexing, Git commits, and tests MUST run locally inside `~/local_sage_desarrollo/<modulo>/`. NEVER edit directly on `/mnt/servidor_sage_desarrollo` (CIFS over VPN creates severe I/O lag and stalls editors/agents).
   - **Mandatory Always-On Indexing (`cbm index <modulo>`)**: It is STRICTLY MANDATORY to run `cbm index <modulo>` at the start of work on any module (right after `cge-sync pull <modulo>`) and after any structural change (new/renamed/deleted classes, methods, or files). Never explore a module with `grep` or full-file reads without indexing it in `codebase-memory-mcp` first.
   - **Git Branching Strategy**: Every local module MUST be versioned with Git on a dedicated working branch `desarrollo-local` (branched from `desarrollo`). This branch is used by the orchestrator to track `git diff` and control all changes (whether direct surgical edits or delegated bulk worker tasks). The PR is subsequently created against or merged into `desarrollo`.
   - **No Raw Rsync - Use `cge-sync` / Aliases**: NEVER execute raw `rsync` commands directly. Always use `cge-sync pull <modulo>` (or `cge-pull`) to pull from server and initialize `desarrollo-local`, and `cge-sync push <modulo>` (or `cge-sync <modulo>`) to sync back to Samba. This allows the user to approve the command once in the IDE UI and reuse permissions.
   - **Automated Sync Authorization**: The assistant/agent is explicitly authorized and expected to execute `cge-sync push` (or `cge-sync <modulo>`) after each batch of code changes or refactorings.
   - **Git Execution & Inspection (`cge-git`)**: NEVER run commands with dynamic directory flags like `git -C <modulo_path> ...` as they break prefix-matching and trigger permission prompts for each path. Instead, use `cge-git <modulo> <comando_git...>` (or set `Cwd` directly to `~/local_sage_desarrollo/<modulo>/` / `$SAGE_LOCAL_WORKSPACE/<modulo>/`). `cge-git` standardizes the executable so the user authorizes it once ("Always allow") for any Git operation across any module.
   - **AI Artifacts Local Isolation (MANDATORY)**: All task tracking files (`odd/tasks/`, `sdd/`, `openspec/`, `.atl/`, `scratch/`) are strictly local developer metadata for AI orchestration. They MUST be excluded via `.gitignore` and NEVER committed to the `desarrollo` branch, and NEVER synced to the remote server via `cge-sync`. The `desarrollo` branch and pull requests must only contain clean application code, business logic, configuration, migrations, and documentation.
   - **Sync Mechanism**: `cge-sync` mirrors the local module to `/mnt/servidor_sage_desarrollo/<modulo>/` via optimized `rsync` (excluding `.git`, `.env`, AI artifacts `odd/`, `sdd/`, `openspec/`, `.atl/`, `scratch/`, and vendor artifacts), preserving Samba mount web permissions (`0755` / `usuarioweb`) so the user can immediately test by refreshing the browser.

9. **Daily Rotating File Logger (10-Day Retention) & Safe Frontend Error Messages (MANDATORY)**:
   - **Daily `.log` Files with 10-Day Retention**: Every module MUST log errors, warnings, and diagnostic events into `logs/error-YYYY-MM-DD.log` and `logs/app-YYYY-MM-DD.log` via `App\Core\Logger`. The logger MUST automatically prune daily `.log` files older than **10 days** (`RETENTION_DAYS = 10`).
   - **Developer & AI Context in `.log` Only**: Full technical diagnostics (`exception` class, raw `$e->getMessage()`, SQL state, `file`, `line`, stack `trace`, request payload, `usuario_id`, and `TraceID`) MUST be recorded exclusively in the `.log` file.
   - **User-Friendly Frontend Messages**: NEVER expose raw SQL errors, stack traces, file paths, or internal exception messages to the frontend UI.
     - For controlled domain/validation errors (`InvalidArgumentException` or HTTP 400–499), return the clear, human-readable validation message.
     - For unexpected server/database errors (`Throwable` / `PDOException` / HTTP 500), log the full exception in `logs/error-YYYY-MM-DD.log` and send a clear, concise message for a regular user (e.g., `"No se pudo completar la operación en este momento. Por favor, intente nuevamente (Ref: <TraceID>)."`).

## Modular Feature Packs (Optional Services)

To avoid overengineering (YAGNI), prompt the user or inspect requirements for which Feature Packs to activate:

```text
[ ] mail-validation      -> Gatekeeper de interfaz + ValidateMail.svg + notificaciones
[ ] pdf-reports          -> Reportes tabulares con TCPDFGenerator
[ ] document-assembly    -> Merge de imágenes/PDFs con Ghostscript (SharedFileStorage)
[ ] secure-file-proxy    -> Tokens AES + verArchivo.php + streaming seguro + migración on-the-fly
```

If the user did not specify the required services in the initial prompt, present this checklist and ask which ones to activate before generating code.

## Mandatory Tool Wrappers & Aliases (Strict Execution Protocol)

All agents, orchestrators, OpenCode workers, and models MUST EXCLUSIVELY use the standard wrappers and aliases below. Any attempt to use raw commands, dynamic directory flags, or ad-hoc scripts is STRICTLY PROHIBITED:

| Operation | Command & Aliases | Purpose & Enforcement |
|---|---|---|
| **File Reading** | `readfile <target_file> [start] [end]`<br>*(Aliases: `cge-read`, `cge_read`)* | Reads entire files or exact line ranges without individual permission prompts. Raw `cat`, `head`, `sed` are forbidden. |
| **PHP Syntax Linter** | `cge-lint <target_file>`<br>*(Aliases: `phplint`, `cge_lint`)* | Validates PHP 8.5 syntax without individual prompts. Mandatory check after EVERY PHP file modification. |
| **Git Operations** | `cge-git <modulo> <args...>`<br>*(Alias: `cge_git`)* | Git operations targeting the local module cleanly without dynamic `-C` paths. Raw `git -C /path/...` is forbidden. |
| **Server Sync** | `cge-sync pull <modulo>` *(Aliases: `cge-pull`, `cge_pull`)*<br>`cge-sync push <modulo>` *(Aliases: `cge-push`, `cge_push`, `cge-sync`)* | Safe bidirectional rsync between local workspace and Samba server. Raw `rsync` or direct edits in `/mnt/` are forbidden. |
| **Skills Sync** | `sync-skills check`<br>`sync-skills pull`<br>`sync-skills push [msg]` | Sincroniza `.agents` entre equipos (notebook, PC, VM). Chequeo obligatorio antes de editar skills. |
| **File Writing** | `writefile <target_file> <source>`<br>`cat << 'EOF' \| writefile <target>` | Atomic file write/copy without shell escaping or string truncation. |
| **Python Scratch** | `pyexec <script.py>`<br>*(Alias: `pyrun`)* | Executes scratch data-processing or migration scripts without path permissions. Raw `python3 <path>` is forbidden. |
| **AST & Symbol Graph** | `cbm index <modulo>`<br>`cbm <subcommand>` *(CLI: `cbm arch`, `cbm status`, `cbm list`)* | **MANDATORY**: Run `cbm index <modulo>` before exploring any module and after structural edits to keep the AST graph 100% indexed at all times. |
| **Mechanical Worker** | `worker <target_file> <contract>` | Ollama local worker for bulk multi-file mechanical boilerplate only. |
| **Local API & DB Test** | `cge-test <modulo> <action> [payload]`<br>*(Aliases: `cge_test`)* | Tests API endpoints, validates SQL Server development DB (`--check-db`) and SAGE permissions (`--check-auth`) without Apache or curl. |

## References & Assets

- Local Testing & DB Protocol: [cge-testing skill](../cge-testing/SKILL.md)


- ID & Query Obfuscation: [references/id-obfuscation.md](references/id-obfuscation.md)
- API & DB Bridge Architecture: [references/api-architecture.md](references/api-architecture.md)
- SAGE Security & Dynamic Roles: [references/sage-security.md](references/sage-security.md)
- Error Pages Guide: [references/error-pages.md](references/error-pages.md)
- PDF Reports Standard: [references/pdf-reports.md](references/pdf-reports.md)
- Document Assembly (Ghostscript): [references/document-assembly.md](references/document-assembly.md)
- Secure File Proxy: [references/secure-file-proxy.md](references/secure-file-proxy.md)
- Mail Validation: [references/mail-validation.md](references/mail-validation.md)
- Reusable Templates: [assets/templates/](assets/templates/)
- Local Sync Utility: `~/.local/bin/cge-sync` (CLI: `cge-sync pull/push/diff`, `cge-pull`, `cge-push`, aliases: `cge_sync`, `cge_pull`, `cge_push`, `cge_rsync`)
- Local Git Utility: `~/.local/bin/cge-git` (CLI: `cge-git <modulo> <comando...>`, alias: `cge_git`)
- Local Inspection & Write Utilities: `~/.local/bin/readfile` (`cge-read`) and `~/.local/bin/writefile`
- Local PHP Syntax Linter: `~/.local/bin/cge-lint` (CLI: `cge-lint <archivo_o_modulo>`, aliases: `cge_lint`, `phplint`)
- Codebase Memory Graph: `~/.local/bin/cbm` (`codebase-memory-mcp`, aliases: `cbm`, commands: `cbm index [modulo]`, `cbm arch [modulo]`, `cbm list`, `cbm ui`)
