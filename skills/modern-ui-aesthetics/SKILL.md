---
name: modern-ui-aesthetics
description: >
  Enforce Material Design & SVG optimization standards.
  Trigger: ALWAYS ACTIVE - Load this skill for every project when designing UI or styling.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.1"
---

# Skill: Modern UI Aesthete (Material Design & SVG Ops)

## Purpose
Enforce a modern, minimalist aesthetic based on Material Design principles, while maintaining a high-performance and clean codebase for visual assets (SVGs).

## Core Mandates (The Aesthetic Code)
1. **Material Minimalism:**
    - Use elevation (box-shadows) to create hierarchy.
    - Rounded corners (border-radius) for a modern, friendly feel.
    - Generous white space (padding/margin) to avoid "cluttered" interfaces.
2. **SVG Zero-Inlining Policy:** 
    - NEVER paste raw `<svg>` code directly into main HTML templates or page-level components.
    - **Option A (Sprite):** Use a centralized `assets/sprite.svg` and reference icons using the `<svg><use href="#icon-id"/></svg>` pattern.
    - **Option B (SVG Component):** Encapsulate complex SVGs into their own dedicated UI components (Atoms) to keep the parent code clean.
3. **Interactive Buttons:** Every button MUST have clear visual feedback for `hover`, `active`, and `focus` states, using transitions for a smooth Material feel.

## Rules of Engagement for the Agent
1. **Visual Proposal:** When asked to build a UI, the agent MUST propose a color palette and elevation strategy before writing CSS.
2. **Asset Inspection:** Before adding an icon, the agent MUST check if it should be added to the `sprite.svg` or created as a standalone component.
3. **Clean HTML:** If the agent sees a large SVG block in a component, it MUST suggest refactoring it into a cleaner pattern (Sprite or Sub-component).

## Standards for Implementation
- **Material Design:** Follow Google's Material Design 3 guidelines but adapt for a "minimalist" look (less colorful, more focus on structure).
- **SVG Optimization:** Run SVGs through a minifier (like SVGO) before adding them to the sprite or component.
- **Accessibility:** Ensure all icon-based buttons have `aria-label` or `title` for screen readers.

## Interaction Tone
- Design-savvy, focused on polish and pixel-perfection.
- If a UI proposal looks "dated" or "cluttered," the agent MUST suggest a cleaner, more modern alternative.
