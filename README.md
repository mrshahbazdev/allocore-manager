# Allocore Manager

The central decision-intelligence layer of the DISAVO ecosystem.

Not a software application — a self-improving system that learns from every
company, user, action and result across all connected platforms, and delivers
the best possible next action to each stakeholder according to their role.

## Core loop

```
Signals in → Patterns → Recommendations → Implementation → Outcomes → Learning → Better recommendations
```

## Domain model

| Model | Role |
|---|---|
| `Source` | A data provider platform (allocore.de, compliancetermine.de, CRMs, ERPs). Authenticates with an `ingest_token`. |
| `Company` | An ecosystem company. Similarity is by situation/maturity/challenges, not industry alone (`CompanySimilarity`). |
| `Signal` | A raw event pushed by a source (risk detected, action implemented, failure observed…). |
| `ActionMeasure` | Catalog of actions the system can recommend. |
| `Pattern` | Learned relation: challenge × action × cohort → attempts/successes/failures + failure reasons. |
| `Recommendation` | A "best next action" issued to a company, with confidence + human-readable rationale. |
| `Outcome` | Measured result of an implemented recommendation — feeds back into `Pattern`s via `LearningLoop`. |

## Services

- `SignalIngestor` — stores signals, upserts company context.
- `RecommendationEngine` — picks best-measured actions from cohort patterns when a challenge signal arrives.
- `LearningLoop` — records outcomes and updates global + cohort pattern statistics (including why things failed).
- `CompanySimilarity` — cohort keys + similarity scoring (situation 60 / maturity 25 / industry 15).

## Web app (Blade routes)

- `/` — ecosystem dashboard: stats, sources, learned patterns, recent signals.
- `/signals/new` + `POST /signals` — record a signal; a `challenge_key` immediately triggers recommendations.
- `/companies` + `/companies/{id}` — company view: recommended next actions with confidence + rationale, similar companies, signals. Accept/dismiss and record outcome from the page.
- `POST /recommendations/{id}/outcome` — closes the learning loop.
- `/intelligence/platform/{source}` — what a platform manager sees: common challenges, recommendation effectiveness.
- `/intelligence/allocore` — what the Allocore team sees: patterns, model quality, success rates.
- `/intelligence/disavo` — what DISAVO sees: strategic metrics, emerging risks.
- `/sources` — register/manage data providers (ingest tokens shown for future connectors).
- `/measures` — action catalog admins maintain.
- `/clusters` — companies grouped by similarity cohort (situation + maturity).
- `/trends` — `TrendDetector` compares challenge volume recent window vs prior window (7–90d), flags rising trends.
- `/processes` — automation tracker: every process moves manual → assisted → semi_automated → automated, with a recorded history (`ProcessAssessment`).

Seed demo data with `php artisan migrate:fresh --seed` (sources, catalog, companies, historic signals, learned patterns, tracked processes).

## Feature validation rule

Every feature must contribute to at least one of: better data, better
recommendations, less effort, higher user value, more automation.
