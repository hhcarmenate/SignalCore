# Python Scanner Service Structure

## Status
Scanner service structure and core scanner-engine building blocks defined through Tasks #24-#33.

## Multi-timeframe confirmation rules

Task #33 adds the reusable confirmation rules used to improve signal quality across multiple timeframes.

### Core pieces
- `MultiTimeframeConfirmationInput`
- `MultiTimeframeConfirmationResult`
- `TrendAlignmentRule`
- `ConflictFilter`
- `MultiTimeframeConfirmer`

### Current rule set
The confirmation layer currently evaluates:
- higher timeframe bias alignment
- trigger vs higher timeframe conflict
- minimum score threshold
- confirmation notes for diagnostics

### Higher timeframe bias rule
- bullish trigger wants bullish higher timeframe bias
- bearish trigger wants bearish higher timeframe bias
- neutral higher timeframe bias is treated as partial alignment
- opposite higher timeframe bias is treated as misalignment

### Conflict filtering rule
A setup is filtered when the trigger/higher timeframe context contradicts the intended signal direction.

### Minimum quality rule
A setup must meet a minimum trigger score threshold to pass confirmation.

### Design outcome
This gives the scanner a reusable confirmation layer before concrete strategy implementations become more complex.

That matters because otherwise every strategy would invent its own version of:
- what ?confirmed? means
- when higher timeframe bias matters
- how to reject conflicting setups
- what minimum quality is acceptable

Now those rules have a shared home.
