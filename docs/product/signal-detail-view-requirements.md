# Signal Detail View Requirements

## Status
Defines the requirements for the main Signal Detail View used to inspect a single persisted trade setup.

## Purpose
The Signal Detail View is the inspection surface for one signal.

It should answer these questions clearly:
- Why was this signal generated?
- How strong is it?
- What are the entry, stop, and target levels?
- What lifecycle state is it in now?
- What review/action history exists around it?
- What scanner context produced it?

## Role in the product
If the Signals List View is the triage surface, the Signal Detail View is the investigation surface.

It should support:
- deeper signal validation
- review decisions
- lifecycle inspection
- action traceability
- strategy/scanner understanding

## Source of truth
The detail view must be driven by persisted `trade_signals` data and related signal-management context.

It should not depend on temporary scanner-only memory.

## Core layout
Recommended high-level layout:

- page header / breadcrumb area
- primary summary card
- detail sections or tabs
- action area
- activity / audit context

The page should support dense but readable information design.

## Required sections

### 1. Signal summary section
Must display the core signal identity at a glance:
- symbol
- strategy label/key
- direction
- execution hint
- timeframe
- current status
- review priority
- score
- confidence
- ranking score / ranking position when available
- generated time

This is the ?what is this?? section.

### 2. Thesis section
Must show the human-readable rationale.

Requirements:
- thesis text should be prominent and readable
- not buried below metadata
- allow enough width/spacing for multi-line reasoning

This is the ?why does this setup exist?? section.

### 3. Levels section
Must display the actionable trade structure:
- entry
- stop loss
- target

Requirements:
- visually grouped together
- easy to compare in one scan
- handle missing values gracefully

This is the ?how would I act on it?? section.

### 4. Signal quality section
Must display the quality metrics clearly:
- score
- confidence
- ranking score
- ranking position
- score breakdown

Requirements:
- score breakdown should be structured, not dumped raw
- it should be easy to understand what drove the score

This is the ?how strong is it?? section.

### 5. Market / technical context section
Must show the structured signal context:
- indicator snapshot
- market context
- direction context
- confirmation context when available later

Requirements:
- present JSON-like technical data in readable UI groups
- avoid raw blob dumps unless behind an expandable advanced section

This is the ?what technical evidence supports it?? section.

### 6. Lifecycle / status section
Must show the current lifecycle and important timestamps:
- status
- status reason
- reviewed at
- invalidated at
- actioned at
- queued for review at
- expires at

This is the ?where is this signal in the workflow?? section.

### 7. Review section
Must show review-specific information when available:
- review summary
- review notes
- review decision context

Requirements:
- notes should be readable in chronological order
- summary should be distinct from raw notes

This is the ?what did the reviewer conclude?? section.

### 8. Audit / activity section
Must show signal activity over time.

Minimum expectation:
- latest-first event history
- event type
- action type
- status before / after
- reason
- occurred at

This is the ?what happened to this signal over time?? section.

### 9. Source / scanner context section
Must show upstream scanner linkage when available:
- source run reference
- source signal reference
- strategy key
- watchlist context if available

This is the ?where did it come from?? section.

## Detail page actions
The detail page should support visible action affordances for the workflow.

Current likely actions:
- queue for review
- accept
- reject
- ignore
- mark actioned
- expire (if exposed)

Requirements:
- actions must respect lifecycle rules
- disabled/unavailable actions should be understandable
- destructive or terminal actions should be visually distinct

## Information hierarchy
Recommended hierarchy from top to bottom:
1. summary + current status
2. thesis
3. entry/stop/target
4. score + confidence + breakdown
5. market/technical context
6. review + lifecycle details
7. audit trail
8. raw metadata / advanced context

This keeps the page decision-first, not schema-first.

## Visual behavior
Use compact but strong status language:
- direction badge
- status badge
- priority badge
- execution hint badge

Use cards/sections with clear headings.
Do not dump all fields into one giant generic detail card.

## States

### Loading state
- skeleton layout that resembles the real page
- preserve page structure while loading

### Missing signal / not found state
- clear not-found message
- route back to signals list

### Error state
- clear fetch/load error
- retry action
- avoid blank screen behavior

## Relationship to current frontend
Right now the frontend only exposes:
- `SignalsView.vue`
- `SignalsTable.vue`

There is no real persisted-signal detail page yet.

So this requirement set should guide the transition from:
- simple summary table

to:
- row click / navigation
- detail route
- investigation surface

## Frontend data contract expectations
The detail page should expect these core fields:
- `id`
- `symbol`
- `strategy_key`
- `direction`
- `execution_hint`
- `timeframe`
- `status`
- `status_reason`
- `score`
- `confidence`
- `ranking_score`
- `ranking_position`
- `review_priority`
- `thesis`
- `entry_price`
- `stop_loss`
- `target_price`
- `score_breakdown`
- `indicator_snapshot`
- `market_context`
- `review_summary`
- `review_notes`
- `signal_generated_at`
- `expires_at`
- `reviewed_at`
- `actioned_at`
- `invalidated_at`
- `source_run_reference`
- `source_signal_reference`

Optional / expandable:
- notification fields
- deduplication fields
- replacement relationships
- raw metadata

## UX priorities
Prioritize in this order:
1. explainability
2. actionability
3. lifecycle clarity
4. auditability
5. visual polish

The page should help a human decide what to do with the signal, not just admire the schema.

## Acceptance criteria
The Signal Detail View requirements are complete when:
- required detail sections are defined
- action expectations are defined
- lifecycle visibility is defined
- review and audit visibility are defined
- source/scanner linkage is defined
- page states are defined
- frontend data expectations are explicit

## Summary
The Signal Detail View should become the main investigation and decision page for a persisted signal.

It must make it easy to understand:
- what the signal is
- why it exists
- how strong it is
- what to do with it
- and what has already happened to it
