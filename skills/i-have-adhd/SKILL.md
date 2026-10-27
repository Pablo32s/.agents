---
name: i-have-adhd
description: 'Shape output for low cognitive load and ADHD: lead with the next action, number multi-step work, restate state across turns, suppress tangents, give specific time estimates, bounded 2-minute next steps.'
license: MIT
metadata:
  tags: "ADHD, Output Style, Productivity, Formatting"
  category: "productivity"
---

# i-have-adhd

Rules for low cognitive load and actionable responses.

## Core Directives

1. **Lead with the action**: The first line must be something the user can execute or open (terminal command, file path, code snippet). Context and explanations come after, if needed.
2. **Number multi-step tasks**: Keep steps atomic, sequential, and bounded. No step contains "and then" multiple times.
3. **Restate state every turn**: In multi-step work, begin with progress tracking: `Step X of Y: [done]. Next: [action]`.
4. **End with ONE concrete next action**: Close with exactly one small action taking < 2 minutes (e.g., `Run npm test and paste the first error`). Never use vague closers like "Hope this helps".
5. **Suppress tangents**: Finish the current problem completely before raising related or secondary issues.
