# Scanner Service

Python service for SignalCore scanner execution and market analysis.

## Current scope

This service now includes a formal MVP scanner strategy set.

## MVP strategy set

The current MVP strategy set is:
1. `trend_continuation`
2. `breakout_confirmation`
3. `mean_reversion_to_trend`

### Priority order
- Trend Continuation ? priority `1`
- Breakout Confirmation ? priority `2`
- Mean Reversion to Trend ? priority `3`

### Directional mapping
Each MVP strategy supports both directional paths:
- `bullish` ? `call`
- `bearish` ? `put`

This keeps the strategy layer aligned with SignalCore's options-oriented execution hints while still using equity candles and scanner logic as the source of truth.

### Inclusion notes
- these three strategies are included in MVP
- each is enabled by default in the code registry
- each is intended to work with the shared candle, indicator, context, and execution layers

### Exclusion notes
The following categories are explicitly excluded from MVP for now:
- counter-trend reversal
- range rotation
- volatility expansion scalp

Reason: they would increase complexity and weaken consistency before the core directional scanner flow is proven.
