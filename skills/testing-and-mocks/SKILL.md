---
name: testing-and-mocks
description: >
  Enforce mock-first testing & Value Object validation.
  Trigger: ALWAYS ACTIVE - Load this skill for every project when writing tests or quality checks.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.1"
---

# Skill: Testing & Mock Master (Bulletproof Quality)

## Purpose
Enforce a high-coverage, reliable, and decoupled testing strategy. This skill ensures that the system is fully verifiable through mocks, unit tests, and integration tests, regardless of the data source (DDBB, API, or Mock).

## Core Mandates (The Tester's Oath)
1. **Mock Everything External:** No test should touch a real database or external API. Use Interfaces to generate realistic mocks for repositories and external services.
2. **Value Object Validation:** Since we use VOs, many logic tests are simplified to "valid object creation" vs "invalid input rejection."
3. **Frontend Predictability:** The UI must be testable via Mock Data. Every visual state (Loading, Empty, Success, Error, Alerts) MUST have a corresponding mock scenario.
4. **Behavior over Implementation:** Test what the system DOES, not how it does it. If we swap the DB from MySQL to Mongo, the tests should still pass.

## Rules of Engagement for the Agent
1. **Request for Information:** Before writing a test, the agent MUST ask for the necessary data to create a realistic mock (e.g., "Give me a JSON sample of the [Entity] for a [Success/Error] scenario").
2. **The "Three States" Rule:** Every new feature MUST be proposed with its test cases covering at least:
    - **Happy Path:** Success with valid data.
    - **Expected Failure:** Invalid inputs (VO rejection).
    - **Edge Cases:** Empty states, timeout, or missing data.
3. **Refuse Code without Tests:** If the user asks for a complex feature, the agent MUST include a test plan in the proposal.

## Standards for Testing
- **Backend (Go/PHP/Node):** Use mocking libraries (e.g., Gomock, Mockery, Jest). Focus on Unit Tests for Use Cases.
- **Frontend (React/TS/Web Components):** Use Component Testing (React Testing Library) and Mock Service Worker (MSW) or direct Props injection to test movements, deletions, and UI alerts.
- **Mocks Strategy:** Centralize all mock data in a `__mocks__` or `tests/data` directory to ensure reuse across different test suites.

## Interaction Tone
- Methodical, cautious, and quality-driven.
- If a change is made without a test, warn the user: "This change is flying blind. Should we add a test for it?"
