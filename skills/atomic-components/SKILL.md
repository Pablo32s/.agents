---
name: atomic-components
description: >
  Enforce Web Components & Atomic Design for reusable UI.
  Trigger: ALWAYS ACTIVE - Load this skill for every project when building UI components.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.1"
---

# Skill: Atomic Component Architect (Web Components First)

## Purpose
Enforce a clean, scalable, and framework-agnostic component architecture. This skill ensures that UI components are truly reusable and decoupled from business logic, following the "Atomic Design" principles without over-complicating the system.

## Core Mandates (The Component Law)
1. **Web Components First:** Prefer standard Web Components (`customElements`) or framework-agnostic styles for maximum portability.
2. **The "Stateless" Rule:** Pure UI components (Atoms/Molecules) MUST be stateless. They receive data via `props` and notify changes via `events`. Never allow a UI component to fetch its own data.
3. **High Cohesion, Low Coupling:** A component should do ONE thing visually. If a component starts having "if/else" logic to check where it's being used, it MUST be broken down or generalized.
4. **Composition over Flags:** Instead of adding 10 boolean flags (e.g., `isRed`, `hasBorder`, `showIcon`), use "Slots" or "Composition" to allow the parent to define the internal content.

## Rules of Engagement for the Agent
1. **Reuse vs. Creation:** Before proposing a new component, the agent MUST check if an existing one can be generalized without breaking its Single Responsibility.
2. **Contract Definition:** Every component proposal must start with its "Contract":
    - **Inputs (@props):** What data does it need? (Types required).
    - **Outputs (@events):** What actions does it notify?
    - **Slots:** Where can the parent inject custom UI?
3. **Refuse Business Leakage:** If the agent sees business logic (API calls, complex state management) inside a base UI component, it MUST suggest moving it to a "Container" or "Page" level.

## Standards for Implementation
- **Naming:** Use standard `kebab-case` for custom elements (e.g., `gc-button`, `gc-input-group`).
- **Styling:** Encapsulate styles within the component (Shadow DOM or scoped CSS) to prevent global leakage.
- **Documentation:** Every component MUST include a brief usage example to show how it's reused.

## Interaction Tone
- Design-oriented, focused on visual consistency and technical elegance.
- If a component is becoming too "smart" (knowing too much about the app), warn the user to simplify it.
