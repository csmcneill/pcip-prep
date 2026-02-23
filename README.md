# PCI Professional Preparation Plugin (P4)

A WordPress plugin for PCIP (Payment Card Industry Professional) certification prep. Provides flashcards, domain-specific quizzes, a full 75-question exam simulator, and a performance dashboard — all driven by shortcodes.

## Features

- **Flashcards** — Flip-card study interface filtered by PCIP domain and PCI DSS requirement. Keyboard navigation and shuffle support.
- **Domain quizzes** — Multiple-choice quizzes scoped to individual PCIP domains or requirements. Immediate answer feedback with explanations.
- **Practice exam** — 75-question timed exam simulating PCIP conditions (90-minute timer, 75% passing threshold). Includes question flagging, a navigation grid, answer review, and per-domain/per-requirement score breakdowns.
- **Performance dashboard** — Tracks quiz and exam history, domain-level accuracy, and requirement-level performance for Domain 3 (PCI DSS Requirements).
- **Issue reporter** — Users can flag questions for review directly from the quiz interface. Reports are tracked as a custom post type with open/resolved status filtering.
- **CSV import and export** — Bulk manage question content via CSV files. Supports both multiple-choice and flashcard formats with validation and upsert logic.

## Requirements

- WordPress 6.0+
- PHP 7.4+

## Installation

1. Upload the `pcip-prep` directory to `wp-content/plugins/`.
2. Activate the plugin through the WordPress admin.
3. The plugin automatically creates its database tables and default taxonomy terms on activation.

## Setup

Create four pages and add the corresponding shortcode to each:

| Page | Shortcode |
|------|-----------|
| Performance dashboard | `[pcip_dashboard]` |
| Flashcards | `[pcip_flashcards]` |
| Quizzes | `[pcip_prep]` |
| Practice exam | `[pcip_exam]` |

Page slugs and hierarchy don't matter — the plugin is entirely shortcode-driven.

## Importing questions

The plugin ships with starter CSV files in the `data/` directory:

- `data/mc-questions.csv` — ~140 multiple-choice questions across all 5 PCIP domains
- `data/flashcards.csv` — ~80 flashcards covering key definitions, thresholds, and concepts

To import, go to **PCIP Prep > CSV Import/Export** in the WordPress admin, select the question type, and upload the file. The importer uses upsert logic — matching questions (by text and type) are updated rather than duplicated, so re-importing is safe.

### CSV schemas

**Multiple choice:**

```
question_text,option_a,option_b,option_c,option_d,correct_answer,explanation,domain,requirement,difficulty,pcip_reference
```

- `correct_answer`: a, b, c, or d
- `domain`: 1–5
- `requirement`: 1–12 (required for Domain 3, optional otherwise)
- `difficulty`: easy, medium, or hard

**Flashcards:**

```
question_text,answer,domain,requirement,pcip_reference
```

## PCIP domains

| Domain | Topic | Exam weight |
|--------|-------|-------------|
| 1 | Payment Card Industry Overview | ~15% |
| 2 | PCI DSS Overview and Applicability | ~20% |
| 3 | PCI DSS Requirements | ~35% |
| 4 | Assessment and Validation | ~15% |
| 5 | Compliance Programs and Operations | ~15% |

Domain 3 is further broken down into Requirements 1–12, each mapped to a specific PCI DSS requirement.

## REST API

All endpoints are under the `pcip-prep/v1` namespace and require an authenticated user.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/questions` | Fetch questions by type, domain, or requirement |
| POST | `/quiz/start` | Start a domain quiz |
| POST | `/quiz/answer` | Submit an answer (immediate feedback) |
| POST | `/quiz/submit` | Complete and record a quiz session |
| POST | `/exam/start` | Start a 75-question practice exam |
| POST | `/exam/autosave` | Save exam progress (answers and flags) |
| POST | `/exam/submit` | Complete and score the exam |
| GET | `/dashboard` | Retrieve performance stats and history |
| POST | `/report-issue` | Report a question issue |

## Authentication

All shortcodes display a login prompt for unauthenticated users. The login button redirects to WordPress.com SSO via `wp_login_url()`. This plugin does not enforce site-wide access control — pair it with an access restriction plugin or WooCommerce Memberships if you need to gate the entire site.

## License

GPL-2.0-or-later
