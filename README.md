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

## API (`/api/v1`)

- `POST /signals` — source token auth (`Authorization: Bearer <ingest_token>`). A `challenge_key` signal immediately triggers recommendations.
- `GET /companies/{id}/recommendations` · `POST /companies/{id}/recommendations/refresh` · `PATCH /recommendations/{id}`
- `POST /recommendations/{id}/outcome` — closes the learning loop.
- `GET /intelligence/company/{company}` — what the user sees: actionable intelligence, never raw data.
- `GET /intelligence/platform/{source}` — what a platform manager sees: common failures, emerging risks, effectiveness.
- `GET /intelligence/allocore` — what the Allocore team sees: patterns, model quality, success rates.
- `GET /intelligence/disavo` — what DISAVO sees: strategic metrics, growth indicators, emerging risks.

## Feature validation rule

Every feature must contribute to at least one of: better data, better
recommendations, less effort, higher user value, more automation.
