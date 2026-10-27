---
name: context-guardian
description: >
  Prevent context drift & ensure session handovers.
  Trigger: ALWAYS ACTIVE - Load this skill for every session. Maintain CONTEXT.md as source of truth.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.1"
---

# Skill: Context Guardian

## Purpose
Prevent context drift and ensure seamless session handovers across different agents and environments (CLI, IDE, different PCs). This skill maintains a living `CONTEXT.md` file in the project root as the "Source of Truth" for the current state of work.

## Core Mandates
1. **Always Read First:** At the start of ANY interaction, the agent MUST check for a `CONTEXT.md` file in the root. If it exists, read it before making any assumptions or proposals.
2. **Update on Milestone:** Every time a task is completed, a significant decision is made, or the session is about to end, the agent MUST update `CONTEXT.md`.
3. **Reasoning over Result:** Don't just list what was done; explain WHY it was done that way to prevent future agents from undoing architectural choices.

## Execution & Navigation Safety (Anti-Drift)
1. **No Implicit Navigation:** Sub-agents must never use the browser for local code analysis.
2. **Login Configuration:** If a task requires browser access (Docs/Verification), the agent MUST request login/auth configuration from the user BEFORE proceeding.
3. **Orchestrator North:** Sub-agents must stick to the local workspace unless the Orchestrator explicitly authorizes web access for a specific sub-task.

## CONTEXT.md Structure
The file maintained by this skill must follow this structure:

### 1. Current Mission
One sentence describing the absolute priority right now.

### 2. Strategic Context & Architecture
- **Rules of Engagement:** Specific ways we are working (e.g., "Using Clean Architecture", "Preferring Signals over Redux").
- **Design Decisions:** Summary of recent "Whys".

### 3. State of Play
- ✅ **Accomplished:** Recent wins and their impact.
- ⏳ **In Progress:** What is currently half-baked.
- 🛑 **Blockers:** What's stopping us.

### 4. The Next Move (Hand-off)
The very next step the next agent should take. Be specific.

## Usage Instructions for the Agent
- If `CONTEXT.md` is missing, offer to initialize it by summarizing the current known state.
- Use a tone that is direct and professional, focused on technical continuity.
- NEVER let a session end without a clear "Next Move" recorded.
