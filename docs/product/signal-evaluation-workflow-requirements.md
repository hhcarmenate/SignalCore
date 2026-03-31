# Signal Evaluation Workflow Requirements

## Status
Defines the workflow used to evaluate generated signals after sufficient market data has unfolded.

## Purpose
Signal evaluation workflow answers the question:

**When and how does SignalCore decide a signal has enough post-signal data to be evaluated?**

This workflow is needed so outcome tracking and performance reporting happen consistently instead of opportunistically.

## Product role
The evaluation workflow sits between:
- persisted signal generation
- post-signal market data availability
- outcome evaluation
- performance analytics

It governs process and timing.
It does not redefine signal generation or strategy logic.

## Source of truth
The workflow should be driven by:
- persisted signal metadata
- signal outcome requirements
- market data availability
- explicit evaluation timing rules

## Workflow goals
1. evaluate signals only when enough data exists
2. avoid evaluating too early
3. support automated evaluation by default
4. define where manual intervention is allowed
5. make re-evaluation rules explicit

## High-level workflow
Recommended sequence:

1. signal is persisted
2. signal enters evaluation waiting state
3. market data continues to arrive
4. evaluator checks whether the signal is ready for outcome evaluation
5. signal is evaluated automatically when prerequisites are met
6. outcome record is persisted
7. performance metrics can consume the result

## Evaluation timing rules

### 1. Do not evaluate immediately at signal creation
Reason:
- post-signal outcome cannot exist at creation time
- immediate evaluation would create false unresolved noise

### 2. Evaluate only after sufficient post-signal market data is available
Minimum requirement:
- enough price data exists after `signal_generated_at` to determine whether entry, stop, target, or expiry conditions occurred

### 3. Expiration-aware timing
If the signal has `expires_at`:
- evaluation should wait until either
  - a decisive outcome is detectable, or
  - expiration is reached and the remaining result can be classified

### 4. Configurable fallback horizon
If the signal has no explicit expiration:
- the workflow should support a configurable evaluation horizon by timeframe and/or strategy

Examples later:
- `4H` signals might use a shorter evaluation horizon
- `1D` signals might use a longer evaluation horizon

This task defines the workflow requirement, not the exact implementation constants.

## Auto vs manual evaluation boundaries

### Default recommendation
Use **automatic evaluation** as the primary path.

Automatic evaluation should handle:
- entry reached checks
- target/stop checks
- expiry / no-entry classification
- ambiguity flags when data cannot prove sequencing

### Manual evaluation should be exceptional
Manual review should be allowed only for cases such as:
- missing or corrupted market data
- policy changes after the original evaluation
- explicit operator audit or correction flow later

### Boundary rule
The product should not require humans to hand-evaluate normal signal outcomes.
That would make analytics fragile and non-scalable.

## Evaluation data dependencies
The workflow requires access to:
- persisted signal record
- signal trade structure (`entry_price`, `stop_loss`, `target_price`)
- signal timestamps (`signal_generated_at`, `expires_at` when present)
- normalized candle or price data after signal generation
- direction context (`bullish` / `bearish`)

Optional later:
- intrabar data improvements
- strategy-specific evaluation assumptions

## Evaluation readiness rules
A signal should be considered ready for evaluation when at least one of these is true:
- a decisive post-entry outcome is detectable
- the signal expired and its final evaluation can be assigned
- the configured evaluation horizon has completed

A signal should remain pending if:
- not enough post-signal data exists yet
- expected market data ingestion is incomplete
- evaluation dependencies are missing

## Evaluation process design
Recommended domain process:
- a background evaluator scans for pending evaluable signals
- it applies readiness rules
- it persists an outcome evaluation when the signal becomes ready
- it avoids reprocessing fully evaluated records unless re-evaluation is explicitly triggered

Suggested owner later:
- `TradeSignalEvaluationWorkflow`

Responsibilities:
- determine readiness
- dispatch or run evaluation
- persist state changes and timestamps
- mark records for retry or manual review when dependencies fail

## Re-evaluation rules
Re-evaluation must be explicit, not accidental.

### Re-evaluation should be allowed when:
- market data was missing or later corrected
- outcome evaluation assumptions changed
- ambiguity policy changed
- a bug fix requires recomputation

### Re-evaluation should not happen silently when:
- a record is already resolved and nothing material changed
- a dashboard page simply reloads
- metric consumers query results

### Required workflow rule
Any re-evaluation path should preserve auditability:
- previous evaluation state should remain traceable later
- recalculation should have a reason

## Failure and retry behavior
The evaluation workflow should support operational failure handling.

Examples:
- required candle range unavailable
- provider gap in post-signal data
- malformed signal structure
- strategy data dependencies missing

Recommended behavior:
- mark evaluation as pending or blocked
- record failure reason
- allow retry once dependencies recover

Do not silently drop failed evaluations.

## Batch vs event-driven processing
The workflow should support either of these implementation styles later:
- scheduled batch evaluation job
- event-driven evaluation after relevant market data ingestion

For the current definition task, the important rule is:
- evaluation must be deterministic and repeatable regardless of trigger style

## Output expectations
The evaluation workflow should produce or update:
- evaluation state
- evaluation timestamps
- outcome record
- ambiguity or failure metadata when needed

## Relationship to outcome tracking
The evaluation workflow is the operational process.
The outcome model defines the result structure.

In simple terms:
- outcome tracking defines **what** gets stored
- evaluation workflow defines **when and how** it gets decided

## Relationship to performance metrics
Performance metrics should consume evaluated outcomes only.
They should not attempt to infer evaluation timing themselves.

This separation prevents analytics layers from inventing their own readiness rules.

## UX / operator expectations
The UI should later be able to distinguish between:
- not yet evaluated
- evaluated successfully
- evaluation blocked
- evaluation ambiguous
- re-evaluated after data correction

This task does not require that UI yet, but the workflow should support it.

## Acceptance criteria
This task is complete when:
- evaluation timing rules are defined
- evaluation process design is defined
- auto vs manual boundaries are defined
- evaluation data dependencies are defined
- re-evaluation rules are defined
- failure/retry behavior is defined
- relationship to outcomes and metrics is explicit

## Summary
Signal evaluation workflow should define when and how SignalCore turns post-signal market data into stable outcome records.

The first version should prioritize:
1. timing clarity
2. automatic evaluation by default
3. explicit readiness rules
4. explicit re-evaluation rules
5. operational reliability over cleverness
