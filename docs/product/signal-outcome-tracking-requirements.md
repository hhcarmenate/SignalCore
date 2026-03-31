# Signal Outcome Tracking Model

## Status
Defines how SignalCore tracks what happens after a signal is generated so outcomes can be measured consistently.

## Purpose
Signal outcome tracking answers the question:

**What actually happened after this signal existed as a tradeable setup?**

It should make it possible to evaluate signals using a shared model instead of ad hoc chart reading or subjective memory.

This layer is required for:
- signal performance analytics
- strategy comparison
- outcome-based validation
- later feedback loops for tuning scanners and ranking models

## Product role
Signal outcome tracking sits after Signal Management.

It is not responsible for:
- generating signals
- review workflow
- notification decisions
- execution logging for real brokerage activity

It is responsible for:
- measuring post-signal price behavior
- deciding whether entry, stop, or target conditions were reached
- assigning a normalized outcome classification
- recording timestamps for important outcome events
- preserving the assumptions used for evaluation

## Source of truth
Outcome tracking should be driven by:
- persisted `trade_signals`
- the signal's stored trade structure (`entry_price`, `stop_loss`, `target_price`)
- normalized post-signal market data
- explicit evaluation rules

It must not depend on:
- human memory
- UI-only interpretation
- implied execution that was never recorded

## Core principle
A signal outcome is an **evaluation result**, not proof of an executed trade.

This means:
- outcome tracking measures what the setup did after creation
- execution and PnL tracking can exist later as separate concepts
- a signal can have a modeled outcome even if no trade was taken

## Evaluation anchor
Each signal outcome evaluation begins from the persisted signal context:
- `signal_generated_at`
- `entry_price`
- `stop_loss`
- `target_price`
- `direction`
- `timeframe`
- `expires_at` when present

The evaluation window begins at `signal_generated_at`.

## Required tracked events
The model must support tracking these events explicitly.

### 1. Entry reached
Definition:
- the market reaches the signal entry price after signal generation

Required tracking:
- whether entry was reached
- first timestamp when entry was reached
- reference price or candle context used to confirm it later if needed

### 2. Stop hit
Definition:
- after entry is considered active, price reaches the stop level

Required tracking:
- whether stop was hit
- first timestamp when stop was hit

### 3. Target hit
Definition:
- after entry is considered active, price reaches the target level

Required tracking:
- whether target was hit
- first timestamp when target was hit

### 4. Expiration without outcome
Definition:
- the signal expires or evaluation ends before a decisive tracked outcome occurs

Required tracking:
- whether the signal expired before entry
- whether the signal expired after entry but before target/stop resolution

## Evaluation state model
A signal outcome should move through a compact evaluation model.

### Suggested states
- `pending`
  - signal exists but no outcome evaluation has completed yet
- `entry_not_reached`
  - evaluation window ended before entry was touched
- `entered`
  - entry was reached but final result is not yet resolved
- `target_hit`
  - target was reached after entry
- `stop_hit`
  - stop was reached after entry
- `expired_after_entry`
  - entry was reached but neither target nor stop resolved before evaluation ended
- `cancelled`
  - optional administrative state if evaluation is invalidated due to bad data or rule changes

These are evaluation states, not review workflow states.

## Outcome labels
In addition to the detailed evaluation state, the system should expose a simplified outcome label for reporting.

### Required simplified labels
- `win`
- `loss`
- `neutral`
- `unresolved`

### Mapping guidance
- `target_hit` -> `win`
- `stop_hit` -> `loss`
- `entry_not_reached` -> `neutral`
- `expired_after_entry` -> `neutral`
- `pending` -> `unresolved`
- `cancelled` -> `unresolved`

## Win/loss classification rules
The first pass should use rule-based outcome classification.

### Bullish signals
- entry reached when market trades at or above `entry_price`
- target hit when market trades at or above `target_price`
- stop hit when market trades at or below `stop_loss`

### Bearish signals
- entry reached when market trades at or below `entry_price`
- target hit when market trades at or below `target_price`
- stop hit when market trades at or above `stop_loss`

## Event ordering assumptions
Outcome classification depends on event ordering assumptions.

### Base assumption for MVP
Use **first-touch evaluation** after entry becomes active.

Meaning:
- once entry is reached, the system waits for the first decisive resolution event
- the first of `target_hit` or `stop_hit` determines the outcome

### Same-bar ambiguity rule
If both stop and target appear reachable within the same evaluation bar and intrabar order is unknown:
- mark the outcome as `ambiguous_same_bar`
  or
- classify using a conservative assumption if the project chooses a single default rule

For MVP, the recommended rule is:
- store an explicit `evaluation_result = ambiguous_same_bar`
- map simplified label to `neutral` until a stricter policy is adopted

Reason:
- inventing intrabar order creates fake confidence
- analytics should preserve uncertainty where the data cannot prove sequence

## Evaluation assumptions
These assumptions must be explicit in the model.

### 1. Outcome tracking is model-based, not execution-based
The system evaluates setup behavior, not real fills.

### 2. Entry must happen before stop/target resolution
A target or stop should not count as the final outcome if entry was never considered active.

### 3. Evaluation starts only after signal generation time
Pre-signal price action is irrelevant.

### 4. Signal expiration bounds the evaluation window when present
If `expires_at` exists, outcome evaluation should not continue beyond that timestamp unless a later rule explicitly allows it.

### 5. If no explicit expiration exists, a fallback evaluation horizon is needed
This may later depend on timeframe or strategy.
For this definition task, the model should support a configurable evaluation horizon.

### 6. Ambiguity should be preserved, not hidden
If available market data cannot prove order of events, the model should retain ambiguity instead of pretending certainty.

## Required timestamps
The model must support at least these timestamps:
- `evaluation_started_at`
- `evaluation_completed_at`
- `entry_reached_at`
- `target_hit_at`
- `stop_hit_at`
- `expired_at`

Not all timestamps will be present for every signal.

## Required stored fields
The outcome layer should be able to store at least:
- `trade_signal_id`
- `evaluation_state`
- `outcome_label`
- `entry_reached`
- `entry_reached_at`
- `target_hit`
- `target_hit_at`
- `stop_hit`
- `stop_hit_at`
- `expired_without_entry`
- `expired_after_entry`
- `evaluation_started_at`
- `evaluation_completed_at`
- `evaluation_assumption_key`
- `ambiguity_reason`
- `notes`

Optional later:
- `max_favorable_excursion`
- `max_adverse_excursion`
- fixed lookahead price snapshots
- percent return proxies
- risk-multiple outcome

## Relationship to existing database notes
The current `docs/database/tables/signal_outcomes.md` document is directionally useful but incomplete for the analytics workflow.

It should eventually be aligned so that outcome tracking covers:
- entry/stop/target event tracking
- explicit timestamps
- evaluation assumptions
- ambiguity handling
- normalized evaluation states

The current snapshot-only fields like `price_after_1d` and `price_after_3d` may still be useful later, but they do not replace an actual outcome model.

## Relationship to signal lifecycle
Signal lifecycle and signal outcome are separate concepts.

Examples:
- a signal can be `ignored` by the user and still later evaluate to a modeled `win`
- a signal can be `accepted` and still evaluate to a modeled `loss`
- a signal can be `expired` in lifecycle terms and produce `entry_not_reached` in outcome terms

This separation is important for honest analytics.

## Recommended ownership
Suggested domain owner:
- `TradeSignalOutcomeEvaluator`

Responsibilities:
- read persisted signal context
- load required post-signal market data
- evaluate entry/stop/target sequence
- stamp timestamps
- assign evaluation state and simplified label
- preserve assumption metadata

## UX/reporting expectations
The outcome model should support later UI/reporting use cases such as:
- signal detail outcome section
- strategy win/loss aggregates
- validation dashboards
- comparison reports by timeframe / strategy / market context

This definition task does not require those interfaces yet.

## Acceptance criteria
This task is complete when:
- entry reached tracking is defined
- stop hit tracking is defined
- target hit tracking is defined
- win/loss/neutral/unresolved classification is defined
- outcome timestamps are defined
- evaluation assumptions are explicit
- ambiguity handling is defined
- relationship to signal lifecycle is clarified

## Summary
Signal outcome tracking should provide a normalized, explicit, and uncertainty-aware way to measure what happened after a signal was created.

The first version should prioritize:
1. event clarity
2. timestamp clarity
3. win/loss neutrality rules
4. explicit assumptions
5. preserving ambiguity instead of inventing certainty
