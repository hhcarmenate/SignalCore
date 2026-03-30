# Python Scanner Service Structure

## Status
Scanner service structure and core strategy set defined through Tasks #24-#31.

## MVP scanner strategy set

Task #31 formalizes the initial scanner strategies included in MVP.

### Included in MVP
1. `trend_continuation`
2. `breakout_confirmation`
3. `mean_reversion_to_trend`

### Priority order
- `trend_continuation` ? 1
- `breakout_confirmation` ? 2
- `mean_reversion_to_trend` ? 3

### Directional mapping
Each MVP strategy can produce:
- bullish setups ? `call` execution hint
- bearish setups ? `put` execution hint

This preserves the product rule that the scanner logic is equity/ETF based while the output can still guide options-style execution.

### Inclusion rationale
These strategies were chosen because together they cover the MVP directional workflow:
- continuation when trend is already established
- breakout when structure expansion confirms
- pullback entry when mean reversion returns into the broader trend

### Explicit MVP exclusions
The following categories are currently excluded:
- counter-trend reversal
- range rotation
- volatility expansion scalp

These are intentionally out of scope for MVP because they would:
- widen strategy behavior too early
- complicate context interpretation
- increase signal inconsistency before the core set is validated

### Design outcome
The strategy registry now carries useful MVP metadata such as:
- priority
- directional biases
- execution hints
- MVP inclusion flag
- notes

That metadata can later support:
- dashboard display
- default ordering
- runtime prioritization
- product documentation
