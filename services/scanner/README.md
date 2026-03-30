# Scanner Service

Python service for SignalCore scanner execution and market analysis.

## Multi-timeframe confirmation rules

The scanner now includes a reusable multi-timeframe confirmation layer.

### Current rule set
- higher timeframe alignment scoring
- trigger vs higher timeframe conflict filtering
- minimum quality threshold enforcement
- reusable confirmation result contract

### Current behavior
A setup is confirmed only when:
- the higher timeframe is aligned or at least not contradictory
- no trend conflict is detected between trigger and higher timeframe contexts
- the trigger score passes the minimum quality threshold

### Current output
The confirmation layer returns:
- `is_confirmed`
- `alignment_score`
- `conflict_detected`
- `passed_minimum_threshold`
- diagnostic notes
