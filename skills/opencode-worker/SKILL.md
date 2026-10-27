---
name: opencode-worker
description: "Trigger: opencode, worker, writefile, readfile, pyexec, local-worker, delegar en opencode, ejecutar tarea con opencode, opencode run. Delegate bounded tasks to OpenCode CLI worker or fast local helper binaries (worker, writefile, readfile, pyexec)."
license: Apache-2.0
metadata:
  author: pabloponce
  version: "2.0"
---

## Activation Contract

Use this skill when:
- Bulk mechanical modifications across multiple files (e.g. mass PHPDoc additions, adding `declare(strict_types=1);`, or applying repetitive boilerplate across multiple files).
- Autonomous multi-file refactorings where the exact changes are not yet fully mapped or verified.

Do NOT use this skill when:
- **Single-file surgical refactoring or targeted logic fixes (ANTI-PATTERN)**: If the orchestrator already knows the implementation or exact lines to modify, writing a contract file and invoking a worker model causes double-work, token waste, and shell/parsing failure points. Apply the change directly using native tools (`replace_file_content`, `write_to_file`) or `writefile`, and verify with `cge-lint`.
- Exploration or codebase mapping: Use `codebase-memory-mcp` (CBM) and `readfile` instead of delegating to worker.
- The task requires system-wide architectural decisions or cross-repo planning.

## Hard Rules (Zero-Trust Policy)

1. **MANDATORY GIT REPOSITORY (ABSOLUTE BLOCKER)**: The workspace MUST be an initialized Git repository (`git rev-parse --is-inside-work-tree`). If Git is NOT initialized, the orchestrator MUST STOP IMMEDIATELY. NEVER proceed. NEVER execute OpenCode. NEVER auto-initialize Git (`git init`). Challenge the user to initialize Git themselves and wait.
2. **Explicit Whitelist Only**: Every task MUST declare an exact list of target files. Any file modified outside this whitelist triggers an IMMEDIATE automated rollback (`git checkout -- .`).
3. **Deterministic Checkpoint**: Before dispatching OpenCode, verify clean git state (`git status --porcelain`). If dirty, stash or reject.
4. **Execution Sandboxing & Network Access (CRITICAL)**: Ollama runs as a local TCP service on `http://127.0.0.1:11434`. In Antigravity (and sandboxed agent runners) where the sandbox blocks network by default, the orchestrator MUST dispatch `run_command` with `BypassSandbox: true` so the process can reach localhost:11434 (otherwise it fails immediately with `[Errno 111] Connection refused`). Execute non-interactively with model priority: local primary `ollama/llama3.1:8b` for OpenCode tool-calling, or direct `worker` script with `qwen2.5-coder:7b` (fallback: `ollama/qwen2.5-coder:14b` for complex logic, or cloud `opencode/big-pickle` / `google/gemini-3.1-flash-lite` if Ollama is unreachable): `opencode run --agent worker -m ollama/llama3.1:8b --auto "<contract>"`.
5. **Mandatory Verification Gate**: A verification command (unit tests, build, or linter like `cge-lint`) is REQUIRED. If the verification command exits with non-zero, the worker's changes are REJECTED and rolled back.
6. **No Double Work (Direct Edit Priority)**: If the orchestrator can articulate the code change, it MUST apply the diff directly with native tools or `writefile`. Reserve `worker` strictly for bulk mechanical changes across multiple files. The worker NEVER commits to git; only the orchestrator or user commits after verification.
7. **English Instructions Only (MANDATORY)**: All prompts, instructions, target descriptions, and negative constraints sent to the worker MUST be written in English. Coding LLMs perform with higher precision, lower token usage, and zero translation friction when instructed in English.

## Decision Gates

| Condition Observed | Action |
|---|---|
| Not a Git repository (`git rev-parse` fails) | **ABSOLUTE STOP**. Do NOT run OpenCode. Do NOT auto-init Git. Demand user initialize repo and STOP. |
| Dirty working tree (`git status` shows uncommitted changes) | STOP. Ask user to stash or commit existing work before worker dispatch. |
| Worker touched files outside declared targets | Execute `git checkout -- .` immediately; report out-of-scope breach |
| Verification tests FAIL after execution | Execute `git checkout -- .` immediately; do not attempt manual patch loop |
| Worker process crashes, times out, or errors | Log error, ensure workspace is clean, pause delegation |
| Target files modified AND verification tests PASS | Present clean diff to user for approval / commit |

## Execution Steps

1. **Pre-flight Git Gate (MANDATORY)**: Run `git rev-parse --is-inside-work-tree`. If this fails or returns non-zero, **STOP IMMEDIATELY**. Output a firm reminder that Git is required, refuse to run the worker, do NOT run `git init`, and wait for the user. Only if Git is valid, run `git status --porcelain` to ensure a clean tree.
2. **Contract Construction (English Only)**: Build the prompt strictly in English containing:
   - Target files whitelist.
   - Exact behavior change.
   - Negative constraints ("Do NOT modify X", "Do NOT touch imports outside Y").
   - Verification command (e.g., `cge-lint <target_file>`, `npm test`, `pytest`, `go test`).
3. **Worker Dispatch (Default: `worker` CLI)**:
   - **PRIMARY DISPATCH (Bounded edits & new files — 95% of tasks)**: ALWAYS use the ultra-fast local binary `worker`. It directly calls Ollama with zero session overhead, executes in 1–3 seconds, and requires no shell pipes:
     ```bash
     # Recommended for multiline instructions or PHP code (avoids Bash $variable and quote mangling):
     # 1. Write the prompt/contract to scratch/contract.txt (via native write_to_file)
     # 2. Execute worker passing the file:
     worker <target_file> scratch/contract.txt [model]

     # Or inline if instructions are simple:
     worker <target_file> "<instructions in English>" [model]
     ```
     *Default model: `qwen2.5-coder:7b` (or `llama3.1:8b`).*
   - **FALLBACK ONLY (Autonomous multi-file refactoring)**: Use `opencode run` ONLY if the task strictly requires autonomous multi-file tool-calling across 3+ files:
     ```bash
     opencode run --agent worker -m ollama/llama3.1:8b --auto "<contract>"
     ```
4. **Blast Radius Audit**: Run `git status --porcelain`. Verify that every modified file is strictly inside the whitelist. If any unauthorized file was touched, immediately run `git checkout -- .` and abort.
5. **Empirical Gate**: Run the verification command directly. If it fails or worker errored, revert changes (`git checkout -- .`) and output `tail -n 25 ~/.local/state/opencode-worker/last_run.log`.
6. **Delivery**: If all gates pass, show the concise git diff summary to the user.

## Output Contract

Return to user:
- Target files modified.
- Blast radius audit status (PASSED / VIOLATION_ROLLED_BACK).
- Verification test outcome with actual command output (PASSED / FAILED).
- Git diff summary.

## Local Helper Utilities (~/.local/bin)

When full OpenCode execution is not necessary or for direct local operations, eight zero-overhead utilities located in `~/.local/bin/` (present in `$PATH` and aliased in `~/.bashrc`) can be leveraged directly:

1. **`worker` (`~/.local/bin/worker`)**:
   - Direct Python CLI that communicates with local Ollama (`http://127.0.0.1:11434/api/chat`).
   - Supports BOTH editing existing files via `{ "target": "...", "replacement": "..." }` and **automatically creating brand new or empty files** (asking Ollama for full initial content).
   - Syntax:
     ```bash
     worker <target_file> "<instructions in English>" [model]
     ```
   - Default model: `qwen2.5-coder:7b` (can specify `qwen2.5-coder:14b` or `llama3.1:8b`).
   - Use case: Ultra-fast single-file bounded code edits or new file generation without OpenCode session or compaction overhead.

2. **`writefile` (`~/.local/bin/writefile`)**:
   - Atomically writes content to `<target_file>` (automatically creating parent directories if needed).
   - Supports reading from standard input OR copying directly from a source file (avoiding shell pipes):
     ```bash
     writefile <target_file> <source_file>       # No pipes, preserves prefix matching
     cat << 'EOF' | writefile <target_file>      # Stdin mode
     ```
   - Use case: Deterministic file creation or full overwrite when exact file content is already composed by the orchestrator.

3. **`readfile` (`~/.local/bin/readfile` / `cge-read` / `cge_read`)**:
   - Reads target file with optional line ranges or `-n` for line numbers.
   - Syntax:
     ```bash
     readfile <target_file> [start_line] [end_line]
     readfile -n <target_file> [start_line] [end_line]
     ```
   - Use case: Reading local repository files without triggering repetitive individual file permission prompts in Antigravity (user approves `readfile` once).

4. **`pyexec` (`~/.local/bin/pyexec` / alias `pyrun`)**:
   - Executes Python scratch scripts directly from file OR from standard input.
   - Syntax:
     ```bash
     pyexec <script.py> [args...]               # Direct file execution
     pyrun <script.py> [args...]                # Alias
     python3_code_string | pyexec               # Stdin mode
     ```
   - Use case: Ad-hoc local data processing, regex extraction, test validation, or running intermediate migration/refactoring scripts with a single permission approval.

5. **`cge-lint` (`~/.local/bin/cge-lint` / aliases: `phplint`, `cge_lint`)**:
   - Validates PHP 7.3 syntax without per-file permission prompts.
   - Syntax:
     ```bash
     cge-lint <target_file>
     ```
   - Use case: Mandatory immediate verification after every PHP file edit.

6. **`cge-git` (`~/.local/bin/cge-git` / alias `cge_git`)**:
   - Executes Git commands targeting the local module cleanly without dynamic `-C` flags.
   - Syntax:
     ```bash
     cge-git <modulo> <comando_git...>
     cge-git status
     cge-git diff
     ```
   - Use case: Auditing git state, diffs, and logs with single permission approval.

7. **`cge-sync` (`~/.local/bin/cge-sync` / aliases: `cge-pull`, `cge_pull`, `cge-push`, `cge_push`, `cge_sync`)**:
   - Performs safe rsync mirroring between `~/local_sage_desarrollo/<modulo>/` and Samba server `/mnt/servidor_sage_desarrollo/<modulo>/`.
   - Syntax:
     ```bash
     cge-sync pull <modulo>
     cge-sync push <modulo>
     ```
   - Use case: Bidirectional sync avoiding VPN I/O lag and isolating local AI artifacts.

8. **`cbm` (`~/.local/bin/cbm` / CLI for `codebase-memory-mcp`)**:
   - Queries and manages the SQLite/AST code graph for instant symbol and architectural lookups.
   - Syntax:
     ```bash
     cbm arch [modulo]
     cbm search "<query>"
     cbm outline <file>
     ```
   - Use case: Exploring codebase symbols and relationships without context window bloat.

## Scratch Scripts (.py) Protocol & Permission Optimization

When a refactoring or complex migration requires intermediate automation scripts (e.g. AST parsing, bulk replacements, data extraction):
1. **Creation**: Write the script in `scratch/` (e.g. `<conversation-artifact>/scratch/script.py` or `./scratch/script.py`) using the orchestrator's `write_to_file` tool or `writefile <target> <source>`.
2. **Execution**: ALWAYS execute the script via `pyexec <path/to/script.py>` (or `pyrun <path/to/script.py>`), NEVER via raw `python3 <random_path>`.
3. **Permission Benefit**: Antigravity prefix-matches the binary `pyexec`. The user grants permission once ("Always allow"), and all future scratch script executions run without interrupting the user.
4. **Cleanup**: Temporary scratch scripts stay isolated in `scratch/` and are never committed to the repository.

## References

- OpenCode CLI reference (`opencode run --help`)
- Local binaries: `~/.local/bin/{worker,writefile,readfile,pyexec,cge-lint,cge-git,cge-sync,cbm}` (or in `$PATH`)
- Aliases in `~/.bashrc`: `worker`, `writefile`, `readfile`, `cge_read`, `pyexec`, `pyrun`, `cge-lint`, `phplint`, `cge_lint`, `cge-git`, `cge_git`, `cge-sync`, `cge-pull`, `cge_pull`, `cge-push`, `cge_push`, `cbm`
- Engram memory integration (`engram mcp`)
