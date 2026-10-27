---
name: database-migrations
description: >
  Enforce idempotent & versioned SQL migrations.
  Trigger: ALWAYS ACTIVE - Load this skill when any database schema change is needed.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.1"
---

# Skill: Database Migration Sentinel (Robust SQL & Idempotency)

## Purpose
Enforce a safe, versioned, and idempotent approach to database schema and data changes. This skill ensures that every SQL script is production-ready, reversible, and won't crash even if executed multiple times.

## Core Mandates (The DBA's Oath)
1. **Idempotency Required:** Every script MUST be safe to run twice. Use `IF NOT EXISTS` for CREATE, `IF EXISTS` for DROP/ALTER, and check for existing constraints/indexes before adding them.
2. **Atomic Changes (Transactions):** Wrap migrations in transactions (where supported) to ensure that if a script fails halfway, the database rolls back to its original state.
3. **Double Verification:** Before an `ALTER TABLE`, the script must check if the column already exists with the correct type.
4. **Zero Data Loss:** NEVER propose a destructive change (DROP, DELETE) without explicitly asking for a data preservation plan (temporary tables, backups).

## Rules of Engagement for the Agent
1. **The "Up/Down" Rule:** Every migration proposal MUST include two parts:
    - **UP:** The script that applies the change.
    - **DOWN:** The script that undoes the change (Rollback).
2. **Schema Audit:** Before proposing a change, the agent MUST inspect the current schema (or ask for it) to ensure the script matches the existing engine (MySQL, PostgreSQL, etc.).
3. **Production Safety:** If a script takes more than a few seconds (e.g., adding an index to a 10M-row table), the agent MUST warn about potential downtime or locking and suggest a non-blocking alternative (like `CREATE INDEX CONCURRENTLY` in Postgres).

## Implementation Standards
- **Naming Convention:** Use `YYYYMMDD_HHMMSS_description.sql` for all scripts.
- **SQL Best Practices:** Prefer explicit column lists over `*`. Use proper indexing strategies. Ensure foreign keys and constraints are correctly named.
- **Environment Parity:** The agent must ensure that the script works equally in Dev, Staging, and Production by avoiding environment-specific hardcoding.

## Interaction Tone
- Precise, cautious, and focused on data integrity.
- If a proposed script is "blind" (no checks), the agent MUST refuse to generate it until idempotency is added.
