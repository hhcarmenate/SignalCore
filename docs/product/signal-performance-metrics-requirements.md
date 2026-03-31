# Signal Performance Metrics Requirements

## Status
Defines the metrics SignalCore should use to evaluate signal and strategy performance over time.

## Purpose
Performance metrics answer the question:

**How good are our signals once they are measured consistently?**

These metrics should support:
- strategy comparison
- signal quality validation
- symbol and timeframe analysis
- later tuning decisions
- dashboard and reporting surfaces

## Product role
Signal performance metrics are an analytics layer built on top of:
- persisted signals
- signal outcome tracking
- strategy metadata
- symbol and timeframe context

They are not raw signal-generation logic.
They are not brokerage PnL statements.
They are normalized evaluation outputs for product learning and decision support.

## Source of truth
Signal performance metrics should be derived from:
- `trade_signals`
- approved signal outcome evaluations
- signal context fields such as symbol, strategy, timeframe, direction, and review priority

Metrics should not depend on:
- manual spreadsheet calculations
- inconsistent ad hoc formulas
- UI-specific counting logic

## Metric design principles
Metrics should be:
1. comparable across time
2. comparable across strategies
3. explainable to operators
4. resilient to partial data
5. explicit about denominator and evaluation assumptions

## Required metrics

### 1. Win rate
Definition:
- percentage of evaluated signals whose simplified outcome label is `win`

Formula:
- `wins / resolved_signals`

Where:
- `wins` = signals with outcome label `win`
- `resolved_signals` = signals with outcome label `win` or `loss`

Notes:
- `neutral` and `unresolved` should not be mixed into the denominator for win rate
- win rate should be available by strategy, symbol, timeframe, and overall scope

## 2. Average reward/risk outcome
Purpose:
- show whether outcomes are attractive relative to the modeled trade structure

Recommended definition:
- average realized outcome in risk-multiple terms (`R`) across resolved signals

Interpretation examples:
- stop hit -> `-1.0R`
- target hit -> `+1.0R` in the first-pass single-target model
- later versions may support multi-target or partial outcomes

Formula guidance:
- `sum(realized_r_multiple) / resolved_signals`

MVP note:
- if exact `R` cannot yet be fully implemented, the metric definition should still be preserved as the target reporting model

## 3. Target hit rate
Definition:
- percentage of evaluated signals where target was hit after entry became active

Formula:
- `target_hit_count / entered_signals`

Where:
- `entered_signals` = signals whose outcome evaluation reached `entered`, `target_hit`, `stop_hit`, or `expired_after_entry`

Why it matters:
- isolates how often the modeled target actually resolves after the setup becomes active

## 4. Stop hit rate
Definition:
- percentage of evaluated signals where stop was hit after entry became active

Formula:
- `stop_hit_count / entered_signals`

Why it matters:
- complements target hit rate and reveals downside resolution quality

## 5. Performance by symbol
Definition:
- grouped performance metrics for each symbol across a selected time window and scope

Required grouped outputs:
- signal count
- resolved signal count
- win rate
- target hit rate
- stop hit rate
- average `R` outcome when available

Purpose:
- reveal which symbols produce cleaner or weaker signal behavior

## 6. Performance by timeframe
Definition:
- grouped performance metrics for each timeframe across a selected scope

Required grouped outputs:
- signal count
- resolved signal count
- win rate
- target hit rate
- stop hit rate
- average `R` outcome when available

Purpose:
- compare whether setups behave better on `4H`, `1D`, or later timeframes

## Recommended additional metrics
These are not required for first usable analytics but should remain compatible with the model.

### Signal volume
- total signals generated
- total resolved signals
- total neutral signals
- total unresolved signals

### Entry efficiency
- entry reached rate
- no-entry expiry rate

### Priority quality
- performance by review priority
- performance by ranking band

### Direction quality
- bullish performance
- bearish performance

### Strategy quality
- grouped metrics by strategy key

## Metric denominator rules
Denominator consistency matters more than metric count.

### Win rate denominator
- resolved signals only (`win` + `loss`)

### Target/stop hit rates denominator
- entered signals only

### Entry reached rate denominator
- all evaluated signals excluding cancelled records

### Average `R` denominator
- resolved signals with valid `R` calculation

These rules should be explicit anywhere metrics are reported.

## Reporting scopes
Metrics should support grouping or filtering by at least:
- overall dataset
- strategy
- symbol
- timeframe
- direction
- review priority
- date window

## Time windows
Performance metrics should always be evaluated inside a defined time scope.

Examples:
- trailing 7 days
- trailing 30 days
- trailing 90 days
- all time
- custom range later

The first pass does not require a full analytics UI, but the metric definitions must support clear windows.

## Data quality and unresolved outcomes
Not all signals will resolve cleanly.

The metrics model must handle:
- unresolved signals
- ambiguous same-bar outcomes
- signals with missing market data
- signals cancelled due to bad data or invalid evaluation assumptions

Recommended rule:
- unresolved or ambiguous records should be visible in counts
- but should only enter specific rates when the metric denominator clearly allows it

## Performance by symbol requirements
A symbol-level report should be able to answer:
- how many signals were generated for this symbol?
- how many resolved?
- what is the win rate?
- does this symbol produce too many stop hits?
- is the sample size too small to trust?

Recommended output fields:
- `symbol`
- `signal_count`
- `resolved_count`
- `win_rate`
- `target_hit_rate`
- `stop_hit_rate`
- `average_r_multiple`
- `last_signal_at`

## Performance by timeframe requirements
A timeframe-level report should be able to answer:
- which timeframe performs better?
- does one timeframe create more unresolved/no-entry noise?
- do higher timeframes have better outcome quality?

Recommended output fields:
- `timeframe`
- `signal_count`
- `resolved_count`
- `win_rate`
- `target_hit_rate`
- `stop_hit_rate`
- `average_r_multiple`

## Relationship to signal outcomes
Signal performance metrics depend on the signal outcome model from Task #49.

That means:
- outcome definitions must be reused consistently
- performance reporting must not redefine win/loss independently
- metric formulas must respect the same evaluation assumptions

## Relationship to future reporting
These definitions should power later:
- analytics dashboards
- strategy comparison views
- validation reports
- optimization feedback loops

This task defines the metrics, not the implementation UI.

## UX / reporting expectations
Metrics should eventually be presented with:
- explicit sample size
- explicit time window
- explicit denominator logic when needed
- caution around low-sample conclusions

The product should avoid confidently presenting tiny sample sizes as if they were statistically stable.

## Acceptance criteria
This task is complete when:
- win rate is defined
- average reward/risk outcome is defined
- target hit rate is defined
- stop hit rate is defined
- performance by symbol is defined
- performance by timeframe is defined
- denominator rules are explicit
- reporting requirements are clear

## Summary
Signal performance metrics should provide a stable, explainable framework for evaluating how signals actually behave over time.

The first version should prioritize:
1. denominator clarity
2. outcome consistency
3. grouping by strategy/symbol/timeframe
4. low-risk reporting definitions
5. compatibility with future analytics views
