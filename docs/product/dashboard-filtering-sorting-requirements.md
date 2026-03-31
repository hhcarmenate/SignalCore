# Dashboard Filtering and Sorting Requirements

## Status
Defines the filtering and sorting behavior required to make signal review efficient in the dashboard.

## Purpose
Filtering and sorting are core review tools, not optional table extras.

The dashboard must let a user quickly narrow signals down to:
- the strategies they care about
- the timeframes they trust
- the directions they want to inspect
- the statuses they need to act on
- the strongest opportunities first

## Product role
This layer sits on top of the Signals List View and future scanner-monitor views.

Its job is to reduce noise and make signal triage fast.

## Design goals
1. Make common review flows possible in one or two interactions.
2. Keep default sorting aligned with review priority.
3. Preserve filter visibility so users understand the current dataset.
4. Avoid overwhelming the user with giant advanced-query UI on first load.
5. Support growth without redesigning filtering from scratch later.

## Required filters

### 1. Symbol filter
Required behavior:
- exact symbol selection
- lightweight symbol search
- multi-select is preferred

Primary use cases:
- inspect one ticker
- compare recent signals for a small ticker set

### 2. Timeframe filter
Required values should include at minimum:
- `4h`
- `1d`

Requirements:
- support single or multi-select
- visible in the quick filter layer

### 3. Strategy filter
Required values should include at minimum:
- `trend_continuation`
- `breakout_confirmation`
- `mean_reversion_to_trend`

Requirements:
- support multi-select
- use friendly labels where possible

### 4. Direction filter
Required values:
- bullish
- bearish

Requirements:
- must be available as a quick filter
- should be visually lightweight to toggle

### 5. Status filter
Required values:
- new
- pending_review
- accepted
- rejected
- expired
- actioned
- ignored

Requirements:
- support multi-select
- status is one of the highest-value filters for operators

### 6. Review priority filter
Required values:
- high
- medium
- low

This should be exposed because the review queue depends heavily on it.

## Recommended filters (phase 1.5 or phase 2)
- execution hint
- watchlist
- notification priority
- score range
- confidence range
- generated date range
- source run reference

These are useful, but not required for first-pass MVP UX.

## Quick filter behavior
Quick filters should be:
- visible without opening a modal
- easy to toggle on/off
- clearly reflected in the current dataset state
- easy to reset with a single action

Quick filters should cover the highest-frequency review behaviors:
- status
- strategy
- timeframe
- direction
- review priority

## Advanced filtering behavior
Advanced filters can be added later for:
- score thresholds
- confidence thresholds
- date ranges
- watchlist-specific queries
- scanner-run-specific queries

Do not block the MVP on an advanced filter builder.

## Search behavior
The dashboard should support search across at least:
- symbol
- strategy label/key

Optional later:
- thesis
- source references

Search should combine cleanly with active filters.

## Required sorting options

### 1. Review priority
Needed for operational queue ordering.

### 2. Review score
Needed to surface the best signals first.

### 3. Score
Needed when ranking_score is absent or secondary.

### 4. Confidence
Needed for quality comparison.

### 5. Generated at
Needed for recency review.

### 6. Symbol
Needed for scanability and user preference.

### 7. Status
Useful for grouped workflow review.

## Default sorting
Recommended default sort:
1. `review_priority` (`high` -> `medium` -> `low`)
2. `review_score` descending
3. `signal_generated_at` descending

Reason:
- this best matches the actual review workflow
- it avoids a purely chronological feed overwhelming stronger signals

## Sorting behavior rules
- only one primary sort should be visible at a time in the MVP UI
- default sort should be obvious to the user
- sorting should persist while the user remains in the signals workspace if practical
- column-based sorting is acceptable for the list view

## UX behavior

### Active filter visibility
The UI must make it obvious which filters are currently active.
Examples:
- chips
- highlighted control states
- visible filter summary

### Reset behavior
A visible ?clear all filters? action is required.

### Empty filtered results
If filters produce zero rows, the UI must say:
- no signals match the current filters
- offer a one-click reset path

## Relationship to current implementation
The current frontend table is still lightweight and placeholder-oriented.

These requirements should guide the transition toward:
- real persisted-signal filters
- real review-centric sorting
- a more operational dashboard behavior

## Frontend data expectations
To support filtering and sorting well, the frontend should have access to at least:
- `symbol`
- `strategy_key`
- `direction`
- `timeframe`
- `status`
- `review_priority`
- `review_score`
- `score`
- `confidence`
- `signal_generated_at`

## UX priorities
Prioritize in this order:
1. fast triage
2. low-friction filter toggling
3. strong default ordering
4. visible active state
5. advanced flexibility later

## Acceptance criteria
This task is complete when:
- required filters are defined
- recommended filters are distinguished from required ones
- sorting options are defined
- default sorting is defined
- active-state / reset / filtered-empty behavior is defined
- frontend data expectations are explicit

## Summary
The dashboard filtering and sorting layer should make signal review fast, opinionated, and low-friction.

The MVP should optimize for the real review workflow first, not generic table configuration. 
