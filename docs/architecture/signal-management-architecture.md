# Signal Management Architecture

## Status
Defines the Signal Management layer for SignalCore after the Scanner Engine produces normalized signal payloads.

## Purpose
Signal Management is the layer that takes scanner-produced signal candidates and turns them into durable, reviewable, traceable product objects.

It is responsible for:
- persisting normalized signals
- tracking lifecycle state
- supporting human review
- handling action/status updates
- enabling notification eligibility
- preserving audit history
- powering filtering, prioritization, and review queue ordering

It is **not** responsible for generating raw signals. That belongs to the Scanner Engine.

## High-level architecture

```text
Scanner Engine
  -> normalized scanner payload
  -> persistence integration contract
  -> trade_signals record
  -> lifecycle / review / notification / audit / filtering layers
  -> dashboard + future APIs
```

## Responsibility boundaries

### Scanner Engine owns
- strategy execution
- candle access
- indicators
- market context
- scoring/ranking
- confirmation rules
- normalized output payloads

### Signal Management owns
- persistence into `trade_signals`
- status model and transitions
- review queue and review notes
- action flow rules
- notification eligibility
- audit history
- filtering and prioritization for UI exposure

### Laravel / API layer owns
- migrations and schema order
- Eloquent models
- service classes for signal state and workflows
- eventual controllers / endpoints / policies

## Core data model

### Primary entity: `trade_signals`
The central product object is `trade_signals`.

It stores:
- source context
  - `watchlist_id`
  - `symbol_id`
  - `scanner_strategy_id`
  - `strategy_key`
  - `source_run_reference`
  - `source_signal_reference`
- signal identity
  - `timeframe`
  - `direction`
  - `signal_category`
  - `execution_hint`
- quality and ranking
  - `score`
  - `confidence`
  - `ranking_score`
  - `ranking_position`
  - `review_priority`
  - `review_score`
- trade levels
  - `entry_price`
  - `stop_loss`
  - `target_price`
- structured signal payload
  - `thesis`
  - `score_breakdown`
  - `indicator_snapshot`
  - `market_context`
  - `metadata`
- lifecycle / workflow state
  - `status`
  - `status_reason`
  - `reviewed_at`
  - `invalidated_at`
  - `actioned_at`
  - `queued_for_review_at`
  - `review_summary`
  - `review_notes`
  - `last_action`
  - `last_action_at`
  - `last_action_note`
- notification state
  - `notification_priority`
  - `should_notify`
  - `notified_at`
- deduplication state
  - `fingerprint`
  - `setup_key`
  - `bar_time`
  - `is_duplicate`
  - `replaces_trade_signal_id`

### Audit entity: `trade_signal_audits`
`trade_signal_audits` stores traceability events for signal changes.

It captures:
- `trade_signal_id`
- `event_type`
- `status_before`
- `status_after`
- `action_type`
- `reason`
- `notes`
- `metadata`
- `occurred_at`

## Data flow

### 1. Scanner output
The Scanner Engine emits normalized signal payloads using its contracts.

### 2. Persistence mapping
`TradeSignalPersistenceContract` transforms scanner payloads into Laravel-side persistence attributes.

Responsibilities:
- validate required fields
- resolve `symbol_id`
- resolve `scanner_strategy_id` when available
- map levels / context / scoring payloads
- preserve source run references

### 3. Initial persistence
The mapped payload is persisted into `trade_signals` with default status:
- `new`

### 4. Post-persistence enrichment
Once stored, the signal can flow through supporting layers:
- deduplication rules
- filtering/prioritization rules
- notification rules
- review workflow
- lifecycle transitions
- audit logging

## Lifecycle model

### Current statuses
- `new`
- `pending_review`
- `accepted`
- `rejected`
- `expired`
- `actioned`
- `ignored`

### Transition rules
- `new` -> `pending_review | accepted | rejected | expired | ignored`
- `pending_review` -> `accepted | rejected | expired | ignored`
- `accepted` -> `actioned | expired`
- terminal: `rejected | expired | actioned | ignored`

### Lifecycle owner
`TradeSignalLifecycleManager`

Responsibilities:
- enforce allowed transitions
- stamp transition timestamps
- write status reason

## Review workflow

### Review owner
`TradeSignalReviewWorkflow`

### Current responsibilities
- queue a signal for review
- record review summary
- append review notes
- accept a signal
- reject a signal

### Review queue behavior
Signals intended for review should typically move:
- `new` -> `pending_review`

Review metadata lives on the signal for now:
- `queued_for_review_at`
- `review_summary`
- `review_notes`

## Action and status update flow

### Action owner
`TradeSignalActionManager`

### Action types
- `queue_for_review`
- `accept`
- `reject`
- `ignore`
- `mark_actioned`
- `expire`
- `invalidate`

### Purpose
This layer expresses domain actions clearly instead of allowing random raw status updates everywhere.

It also preserves:
- `last_action`
- `last_action_at`
- `last_action_note`

## Notification rules

### Notification owner
`TradeSignalNotificationRules`

### Current behavior
Notifications are based on:
- status eligibility
- timeframe eligibility
- supported strategy set
- quality thresholds

### Current output fields
- `notification_priority`
- `should_notify`
- `notified_at`

This gives the dashboard or future delivery systems a clean way to determine if a signal should surface without mixing transport concerns into scanner code.

## Deduplication rules

### Dedup owner
`TradeSignalDeduplicator`

### Current behavior
The dedup layer uses:
- `fingerprint` for same-bar duplicate identity
- `setup_key` for broader same-setup matching
- `replaces_trade_signal_id` for superseding/replacement chains

### Purpose
Prevent duplicate signals from flooding review queues while still allowing newer setup iterations to reference older ones.

## Filtering and prioritization

### Filtering owner
`TradeSignalFilteringRules`

### Current responsibilities
- classify `review_priority`
- compute `review_score`
- filter by strategy, direction, timeframe
- order review queue by priority + score + recency

### Current review queue ordering
1. high priority
2. medium priority
3. low priority
4. higher review score first
5. newer signals first

## Audit history

### Audit owner
`TradeSignalAuditLogger`

### Purpose
Create a traceable history of changes without pushing that concern into every service class manually.

### Recommended event types
- `persisted`
- `status_changed`
- `queued_for_review`
- `review_note_added`
- `accepted`
- `rejected`
- `ignored`
- `expired`
- `actioned`
- `notification_marked`
- `deduplicated`
- `superseded`

## Design principles

### 1. Scanner output remains normalized
Do not let Laravel-side persistence rules leak back into scanner strategy code.

### 2. One central signal entity
Avoid splitting the core signal object across multiple competing tables too early.

### 3. Separate action semantics from raw statuses
Statuses describe state. Actions describe what happened.

### 4. Structured payloads for key technical context
Keep critical technical context in structured JSON fields rather than burying everything in free-form text.

### 5. Auditability by default
Any meaningful state change should be traceable.

## Current implementation map

Implemented in this epic:
- `TradeSignal`
- `ScannerStrategy`
- `TradeSignalLifecycleManager`
- `TradeSignalReviewWorkflow`
- `TradeSignalActionManager`
- `TradeSignalNotificationRules`
- `TradeSignalDeduplicator`
- `TradeSignalAuditLogger`
- `TradeSignalPersistenceContract`
- `TradeSignalFilteringRules`
- `TradeSignalAudit`

## Future follow-up suggestions
Not required for this MVP architecture task, but logically next later:
- API endpoints for signal review/actions
- auth-aware reviewer attribution
- audit event auto-logging inside lifecycle/action services
- dedupe-aware persistence path
- notification delivery pipeline
- dashboard query endpoints for review queues and filtered signal views

## Summary
Signal Management is now defined as the layer between scanner output and user-facing signal operations.

It provides:
- a durable signal model
- controlled lifecycle transitions
- review and action flows
- notification eligibility
- deduplication support
- audit history
- filtering and prioritization

That gives SignalCore a clear architecture where generated signals become manageable product objects instead of ephemeral scanner results.
