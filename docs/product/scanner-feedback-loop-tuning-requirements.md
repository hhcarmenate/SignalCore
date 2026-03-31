# Scanner Feedback Loop for Tuning Requirements

## Status
Defines how analytics results should feed back into strategy refinement and scanner tuning decisions.

## Purpose
The scanner feedback loop answers the question:

**Once analytics tells us what is working or failing, how do we turn that into controlled scanner improvements?**

Without a feedback loop, analytics becomes descriptive only.
With one, SignalCore can improve deliberately instead of by random tweaking.

## Product role
This layer sits above:
- outcome tracking
- performance metrics
- evaluation workflow
- strategy comparison reporting

It is the bridge between analytics insight and scanner change management.

## Source of truth
The feedback loop should rely on:
- approved signal outcomes
- normalized performance metrics
- strategy comparison reports
- explicit tuning decisions and review cadence

It should not rely on:
- impulsive one-day reactions
- anecdotal chart picks
- undocumented parameter changes

## Core goals
1. detect weak or degrading strategies
2. identify candidate tuning inputs
3. define when tuning reviews should happen
4. define how change validation should work
5. preserve controlled continuous improvement

## Weak strategy detection
The feedback loop must define how a strategy becomes a tuning candidate.

### Weakness signals should include combinations such as:
- sustained low win rate
- poor average `R`
- unusually high stop hit rate
- weak performance concentrated in specific symbols or timeframes
- degrading results versus the strategy's own recent baseline

### Important rule
One bad short window should not automatically trigger tuning.
Weakness detection should consider:
- time window
- sample size
- stability of underperformance

## Threshold tuning inputs
The feedback loop should define which analytics inputs are allowed to inform scanner tuning.

Examples:
- confidence threshold effectiveness
- ranking score threshold quality
- signal frequency vs quality tradeoff
- symbol-specific failure concentration
- timeframe-specific degradation
- directional asymmetry in results

### Boundary rule
The feedback loop should recommend tuning inputs, not silently mutate production thresholds on its own in the MVP.

## Review cadence
The system needs an explicit cadence for tuning review.

Recommended review windows:
- weekly review for active monitoring
- monthly review for more stable strategy decisions

Why:
- daily changes are too noisy
- extremely long delays reduce learning speed

### Required rule
Tuning review should happen on a planned cadence, not only when somebody feels annoyed by recent losses.

## Continuous improvement workflow
Recommended high-level workflow:

1. analytics data accumulates
2. weak/strong strategy candidates are identified
3. review summary is prepared
4. a tuning hypothesis is proposed
5. the proposed change is documented
6. the change is validated in a controlled way
7. results are re-measured after deployment or simulation window

This workflow should remain explicit and auditable.

## Tuning decision categories
The workflow should support at least these decision outputs:
- `no_change`
- `observe_longer`
- `tune_thresholds`
- `narrow_symbol_scope`
- `narrow_timeframe_scope`
- `pause_strategy`
- `retire_strategy`
- `promote_strategy`

These categories help avoid vague conclusions like “maybe improve this later.”

## Change validation expectations
Any proposed tuning change should define how it will be judged.

Minimum expectations:
- what changed
- why it changed
- which metrics motivated the change
- which time window will be used to evaluate the change
- what success/failure looks like

### Required rule
No tuning change should be considered successful just because it “felt better.”
It needs explicit validation criteria.

## Validation windows
The feedback loop should support defining a validation period after a tuning change.

Examples:
- minimum resolved signal count
- minimum trailing days/weeks
- comparison against pre-change baseline

The exact thresholds can come later, but the workflow must require them.

## Auditability requirements
The feedback loop should preserve decision traceability.

A tuning recommendation or change log should be able to answer:
- what underperformance was detected?
- who or what proposed the tuning action?
- what was changed?
- when was it changed?
- what evidence justified it?
- how will success be measured?

## Auto vs manual tuning boundary

### MVP recommendation
Tuning decisions should remain human-approved.

Analytics can:
- flag weak strategies
- flag strong strategies
- suggest review candidates
- surface likely tuning inputs

But the system should not automatically change scanner logic or thresholds in production in the MVP.

### Future-compatible path
Later, the system may support recommendation scoring or simulation-driven proposals, but not self-editing production behavior without explicit approval.

## Relationship to strategy comparison
Strategy comparison tells us which strategies are relatively stronger or weaker.
The feedback loop defines how those findings turn into a controlled decision process.

## Relationship to implementation
This task defines the tuning workflow and decision model.
It does not yet implement automation, simulation, or parameter-editing systems.

## UX / reporting expectations
The product should later be able to show:
- strategies needing review
- tuning candidate reasons
- recent tuning decisions
- validation status of recent changes

This definition task does not require the UI yet, but the workflow should support it cleanly.

## Acceptance criteria
This task is complete when:
- weak strategy detection rules are defined
- threshold tuning inputs are defined
- review cadence is defined
- change validation expectations are defined
- continuous improvement workflow is defined
- manual vs automatic tuning boundary is defined
- auditability expectations are explicit

## Summary
The scanner feedback loop should convert analytics into disciplined improvement instead of random strategy tinkering.

The first version should prioritize:
1. explicit weakness detection
2. explicit review cadence
3. explicit tuning decision categories
4. explicit validation expectations
5. human-approved change control
