# Documentation

Contributor-facing documentation for the Hackspace Manchester membership system, written during a
full codebase review in June 2026. Each document describes the current state of the code, what
works well, and where it's heading — so contributions can push in the same direction.

| Document | Covers |
| --- | --- |
| [Project health](project-health.md) | Repo layout, dependency/framework currency, health scorecard, prioritised improvements |
| [Domain subsystems](domain-subsystems.md) | Each domain concept — accounts, inductions, equipment & training, physical access, storage, disciplinary, roles, Discourse, stats — what it does and how it fits together |
| [Billing](billing.md) | The membership billing subsystem: data model, scheduled engine, GoCardless webhooks, the full member lifecycle, state machines |
| [Operations](operations.md) | What the system does automatically when deployed: scheduled jobs, observability, queues, external integration points |
| [Architecture](architecture.md) | The three generations of patterns in the codebase, the target architecture, and the modernisation plan |
| [Frontend](frontend.md) | Blade/jQuery legacy vs Inertia/React/TypeScript, build tooling, and the page-by-page migration plan |
| [Testing](testing.md) | Test inventory, approaches, quality conventions, coverage gaps, and where new tests help most |
| [Glossary](glossary.md) | Domain terms — membership statuses, training concepts, billing vocabulary, access control, project shorthand |

## Conventions

- File references are given as `path:line` relative to the repo root and were accurate at time of
  writing; line numbers drift, so treat them as starting points.
- "Legacy" means a pattern we are migrating away from; it does not necessarily mean broken.

## Keeping these up to date

This codebase is in active migration (Laravel 7 → modern Laravel, Blade → Inertia, legacy patterns
→ current ones). When you complete a migration step described here, update the relevant doc in the
same PR. Each doc's "plan" section is a living backlog.
