---
name: security-and-separation
description: >
  Enforce Zero Trust, DTOs, and redundant validation.
  Trigger: ALWAYS ACTIVE - Load this skill for every project. Non-negotiable security standard.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.1"
---

# Skill: Security & Separation Marshall (Defense in Depth)

## Purpose
Enforce strict Separation of Concerns (SoC) and Redundant Validation across all system layers. This skill ensures that security is baked into the architecture, not added as an afterthought.

## Core Mandates (The Zero Trust Protocol)
1. **Never Trust the Client:** Even if the Frontend validates a field, the Backend MUST re-validate it with the same or stricter rules.
2. **DTO Isolation:** Never pass Database Entities directly to the Frontend or vice-versa. Use Data Transfer Objects (DTOs) and Mappers to ensure no sensitive fields (like IDs, internal timestamps, or secrets) leak accidentally.
3. **Layer Independence:**
    - **Frontend:** Responsible for UX, Accessibility, and *Immediate Feedback*.
    - **Backend:** Responsible for *Integrity*, *Business Logic*, and *Authorization*.
    - **Database:** Responsible for *Persistence* and *Data Constraints* (Unique, Foreign Keys).
4. **Sanitize at Boundaries:** Every input from the outside world (API, User, External SDK) must be sanitized and typed immediately upon entry.

## Rules of Engagement for the Agent
1. **Redundancy Check:** When writing a Frontend validation, the agent MUST immediately suggest or implement the corresponding Backend validation.
2. **Leak Prevention:** If an agent tries to "short-circuit" a layer (e.g., calling the DB directly from a Controller or sending an ORM Entity to a React Component), the agent MUST STOP and propose a DTO/Mapper solution.
3. **Principle of Least Privilege:** When designing APIs or Database queries, only request and return the MINIMUM data required for the task. No `SELECT *` or sending `User` objects with passwords/hashes.

## Implementation Standards
- **Validation:** Use libraries like Zod (TS), Respect/Validation (PHP), or Validator (Go) to create schema-based validation at the entry points.
- **Security Headers:** Always ensure secure headers and CORS policies are part of the proposal for any web service.
- **Error Messages:** Never leak stack traces or internal logic in error responses. Use generic error codes for the client and log the details internally.

## Interaction Tone
- Serious, security-conscious, and detail-oriented.
- If a security risk is detected (e.g., SQL injection potential, XSS, or lack of CSRF), warn the user IMMEDIATELY.
