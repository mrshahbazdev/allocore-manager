# Allocore Manager

A self-improving decision-intelligence layer for the DISAVO ecosystem.

It receives **signals** from participating platforms, turns them into **patterns**,
issues **recommendations** per stakeholder, records **outcomes**, and learns which
actions actually work.

```
signals → patterns → recommendations → implementation → outcomes → better recommendations
```

## Stack

Laravel 12, SQLite by default (MySQL in prod), Blade UI — no JS framework.

## Data model

| Table | Purpose |
|---|---|
| `sources` | webhook tokens per data provider |
| `signals` | normalized events from any source (`type`, `company_key`, `payload`, `occurred_at`) |
| `patterns` | detected challenges + evidence + companies affected |
| `recommendations` | "best next action" per company/user with evidence text |
| `outcomes` | success / failed / dismissed per recommendation — the learning signal |

## Roles (`users.role`)

- `member` — sees "Your next actions" cards with evidence and outcome buttons
- `platform_manager` — product intelligence (top challenges, signal mix, recommendation stats)
- `allocore` — model quality (per-pattern effectiveness, company clusters)
- `disavo` — portfolio intelligence only (companies, success rate, critical open)

## Setup

```sh
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate:fresh --seed   # demo users + demo signals
php artisan serve
```

Demo logins (password `demo1234`): `demo@allocore.de` (member),
`manager@allocore.de`, `team@allocore.de`, `disavo@allocore.de`.

## Signal ingest

```sh
curl -X POST /api/v1/signals/{source_token} \
  -d '{"type":"invoice.overdue","company_key":"acme","occurred_at":"…", ...}'
# or batch: {"signals":[{...},{...}]}
```

## Engine

`php artisan decisions:run` — scans the last 30 days of signals, upserts
patterns, creates open recommendations (deduplicated). Scheduled hourly.
Evidence text shows cross-company effectiveness once outcomes accumulate:
"3 companies implemented a similar action; 66% succeeded."
