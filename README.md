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

- `/` — ecosystem dashboard: stats, sources, learned patterns, recent signals, plus a "needs attention" queue (pending decisions + missing outcomes).
- `/signals/new` + `POST /signals` — record a signal; a `challenge_key` immediately triggers recommendations.
- `/companies` + `/companies/{id}` — company view: best-next-action card, recommended actions with confidence + full rationale evidence (cohort, attempts, success rate, evidence age), similar companies, co-occurring challenges. Accept/dismiss and record outcome from the page.
- `/recommendations/{id}/outcome` — closes the learning loop; failure requires a reason.
- `/intelligence/platform/{source}` — platform manager view: common challenges, recommendation effectiveness, per-company health.
- `/intelligence/allocore` — Allocore team view: patterns, coverage gaps, confidence calibration, success rates.
- `/intelligence/disavo` — DISAVO view: strategic metrics, growth, emerging risks.
- `/sources` — register/manage data providers, with per-feed health badges (healthy/quiet/stale).
- `/measures` — action catalog with per-measure adoption funnel (`/measures/{id}` shows effectiveness by cohort).
- `/challenges/{key}` — everything about one challenge: volume, affected companies, measures by cohort, recent recommendations.
- `/clusters` — companies grouped by similarity cohort (situation + maturity).
- `/trends` — `TrendDetector` compares challenge volume recent window vs prior window (7–90d), flags rising trends.
- `/digest` — last-24h briefing: signals, new recs, outcomes, pending decisions.
- `/processes` — automation tracker: every process moves manual → assisted → semi_automated → automated, with a recorded history (`ProcessAssessment`) and per-challenge maturity suggestions.
- `/users` — per-user signal/activity intelligence.

## Loop-closing signals

Platforms can close the loop without any UI clicks by emitting lifecycle
signals — `SignalIngestor::applyLifecycleSignal` handles them:

- `action.implemented` (+ `measure_key`, `recommendation_id`) → recommendation marked implemented.
- `outcome.measured` (+ `measure_key`/`recommendation_id`, `result` success|partial|failure, `failure_reason`) → outcome recorded, patterns updated.

## Data in / out

- `/sources/{id}/import` — bulk-import signals (JSONL, one object per line). Connector path until platform-specific pullers exist.
- `/sources/{id}/export` — JSONL export of a source's signals (round-trip compatible with import).
- `php artisan allocore:refresh` — re-evaluates open challenges for every company, expires stale pending recommendations; scheduled daily (`routes/console.php`).

Seed demo data with `php artisan migrate:fresh --seed` (sources, catalog, companies, historic signals, learned patterns, tracked processes, generated recommendations and outcomes).

## Feature validation rule

Every feature must contribute to at least one of: better data, better
recommendations, less effort, higher user value, more automation.
