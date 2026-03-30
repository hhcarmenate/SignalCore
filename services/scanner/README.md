# Scanner Service

Python service for SignalCore scanner execution and market analysis.

## Signal scoring and ranking model

The scanner now includes a reusable scoring and ranking layer for comparing generated signals.

### Score dimensions
The current MVP scoring model uses these dimensions:
- trend alignment
- confidence
- volume confirmation
- volatility quality
- structure quality

### Weighting approach
Default weights:
- trend alignment ? `0.30`
- confidence ? `0.25`
- structure quality ? `0.20`
- volume confirmation ? `0.15`
- volatility quality ? `0.10`

### Output model
Signals can now carry:
- `score_breakdown`
- `ranking_score`
- `ranking_position`

### Rules
- all scoring dimensions are normalized to a 0-100 scale before weighting
- the weighted composite becomes the reusable ranking score baseline
- ranking should sort highest-quality signals first
- ties can fall back to confidence after ranking score
