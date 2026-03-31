# Strategy Comparison and Reporting Requirements

## Status
Defines how SignalCore strategies should be compared and reported to identify what works and what needs tuning.

## Purpose
Strategy comparison reporting answers the question:

**Which strategies are actually performing better, and under what conditions?**

This layer should help SignalCore move from raw signal generation to evidence-based strategy judgment.

## Product role
Strategy comparison sits above:
- outcome tracking
- performance metrics
- evaluation workflow

It is a reporting and decision-support layer.
It is not the scanner itself.
It is not an optimization engine yet.

## Source of truth
Strategy comparison reporting should use:
- persisted signals
- approved outcome evaluations
- normalized performance metrics
- signal context dimensions such as symbol, timeframe, direction, and market context when available

It should not use:
- subjective impressions
- one-off anecdotes
- isolated screenshots without normalized metric context

## Core goals
1. compare strategies fairly
2. surface strong and weak strategies clearly
3. avoid misleading rankings from tiny samples
4. support drill-down by symbol, timeframe, and regime
5. produce summaries that can guide tuning work later

## Required comparison dimensions

### 1. Strategy-level comparison
Minimum comparison entity:
- `strategy_key`

Each strategy comparison should include at least:
- signal volume
- resolved signal count
- win rate
- target hit rate
- stop hit rate
- average `R` outcome when available
- unresolved/neutral share when useful

## 2. Symbol breakdowns
The comparison layer should support evaluating strategy behavior by symbol.

Questions this should answer:
- does a strategy work broadly or only on a few symbols?
- which symbols are strongest for a given strategy?
- are poor results concentrated in a specific subset of symbols?

## 3. Timeframe breakdowns
The comparison layer should support evaluating strategy behavior by timeframe.

Questions this should answer:
- does a strategy behave better on `4H` or `1D`?
- is one timeframe creating more unresolved or low-quality signals?

## 4. Direction breakdowns
The comparison layer should support bullish vs bearish comparisons.

Questions this should answer:
- is a strategy materially better on one directional side?
- should future tuning be side-specific?

## 5. Regime-based comparisons
Regime comparison is required conceptually even if the first implementation is limited.

Examples later:
- trend regime
- volatility regime
- market breadth regime
- risk-on / risk-off context

The requirement here is not to fully implement regime analytics yet, but to preserve the reporting model so regime comparisons can be added without redesigning the whole system.

## Required strategy ranking outputs
The reporting layer should support ranking strategies using a normalized comparison output.

Minimum ranking output should include:
- `strategy_key`
- `signal_count`
- `resolved_count`
- `win_rate`
- `target_hit_rate`
- `stop_hit_rate`
- `average_r_multiple`
- optional confidence or sample-quality note

## Ranking rules
Strategy ranking should not be based on one metric alone.

### Recommended rule
Use a ranked summary that balances:
- sample size sufficiency
- win rate
- average `R`
- target/stop behavior

### Important constraint
Do not rank a strategy highly on tiny sample size without surfacing that risk.

Recommended output should always allow the user to see:
- the metric values
- the sample size behind them
- whether the sample is likely too small for confidence

## Report summary requirements
The reporting layer should support summaries at multiple levels.

### Overall strategy summary
Questions answered:
- which strategies are strongest overall?
- which are weak enough to review or deprioritize?

### Strategy drill-down summary
Questions answered:
- where is this strategy strong?
- where is it weak?
- is weakness driven by symbol, timeframe, or direction?

### Comparative leaderboard summary
Questions answered:
- which strategies are leading?
- which are lagging?
- which are promising but under-sampled?

## Required report fields
A first-pass reporting output should support at least:
- `strategy_key`
- `signal_count`
- `resolved_count`
- `win_rate`
- `target_hit_rate`
- `stop_hit_rate`
- `average_r_multiple`
- `bullish_signal_count`
- `bearish_signal_count`
- `top_symbols` or grouped symbol breakdown
- `timeframe_breakdown`
- `sample_size_note`

## Report sorting requirements
Reports should support sorting by at least:
- signal count
- resolved count
- win rate
- average `R`
- target hit rate
- stop hit rate

The first-pass UX may expose only a subset, but the reporting model should support these outputs.

## Sample size and confidence guidance
Sample size must be visible in the comparison model.

Recommended reporting behavior:
- low-sample strategies should be flagged as provisional
- strong outcomes with weak sample size should not be treated as stable proof
- large-sample underperformers should be easy to identify

This is critical to avoid fake confidence in leaderboard-style reporting.

## Symbol and timeframe breakdown requirements
A strategy report should be able to break performance down by:
- symbol
- timeframe
- direction

Later, it should also be able to add:
- regime
- watchlist
- review-priority bucket

## Relationship to performance metrics
Strategy comparison must reuse the metric definitions from Task #50.
It should not redefine win rate or average `R` independently.

In simple terms:
- performance metrics define the ingredients
- strategy comparison defines how those ingredients are grouped and reported across strategies

## Relationship to future tuning
This reporting layer should support later decisions such as:
- retire or pause weak strategies
- increase focus on stronger strategies
- tune by symbol/timeframe/regime
- identify under-sampled strategies needing more observation

This task does not yet implement those decisions automatically.

## UX/reporting expectations
Reports should be:
- comparable at a glance
- drill-down friendly
- explicit about sample size
- explicit about time window
- careful about overclaiming certainty

The product should avoid turning analytics into vanity leaderboards.

## Acceptance criteria
This task is complete when:
- comparison dimensions are defined
- strategy ranking outputs are defined
- report summaries are defined
- symbol and timeframe breakdown requirements are defined
- regime-based comparison expectations are defined
- sample-size guidance is explicit
- relationship to metrics and future tuning is clear

## Summary
Strategy comparison reporting should help SignalCore judge strategies with evidence instead of intuition.

The first version should prioritize:
1. fair comparison dimensions
2. sample-size visibility
3. clear ranking outputs
4. useful drill-downs
5. compatibility with future tuning workflows
