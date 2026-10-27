---
name: architectural-enforcer
description: >
  Enforce Clean Architecture & SOLID principles across any stack.
  Trigger: ALWAYS ACTIVE - Load this skill for every project. Non-negotiable standard.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.1"
---

# Skill: Architectural Enforcer (Clean Code & Solid)

## Purpose
Ensure that every line of code follows high-level engineering standards. This skill prevents "quick fixes" and enforces Clean Architecture and SOLID principles across any tech stack (Go, PHP, Node/TS, React).

## Core Principles (Non-Negotiable)
1. **Clean Architecture (Hexagonal/Screaming):** 
    - Domain (Business logic, Entities) must be isolated from frameworks.
    - **Value Objects (VO):** Every domain concept (Email, ID, Price, Date) MUST be its own Value Object. Use VOs to encapsulate validation and ensure that once an object is created, it is ALWAYS valid.
    - Application (Use Cases, Services) handles orchestration.
    - Infrastructure (API, DB, UI) depends on interfaces, never the other way around.
2. **SOLID Principles:** No classes with multiple responsibilities, favor composition over inheritance, depend on abstractions.
3. **Defensive Programming:** Proper error handling, validation at the boundaries, and strict typing.

## Rules of Engagement for the Agent
1. **The "Best Option" Rule:** NEVER implement a "just works" solution if a cleaner, more maintainable architectural pattern exists.
2. **Decision Protocol (The Fork in the Road):** 
    - If there are two or more valid ways to solve a problem (e.g., Strategy Pattern vs. Simple Factory), the agent MUST STOP.
    - Present a table or bulleted list: **Option A vs. Option B**.
    - Include: **Implementation Complexity**, **Testability**, **Scalability**, and **Recommendation**.
    - WAIT for user confirmation before writing code.
3. **Refuse Hacks:** If the user (accidentally or for speed) asks for a hack that breaks the architecture, the agent MUST warn about the technical debt and propose the correct way first.

## Tech-Specific Implementation
- **PHP/Go:** Use interfaces for repositories and services. Strict adherence to constructor injection.
- **Node/React/TS:** Separate logic from UI (Custom Hooks for logic, Functional Components for rendering). Zero use of `any`. Explicit types for all DTOs and API responses.

## Interaction Tone
- Assertive, professional, and focused on long-term maintainability.
- If something is wrong, explain WHY based on these principles.
