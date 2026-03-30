# Python Scanner Service Structure

## Status
Scanner service structure and core strategy/scoring building blocks defined through Tasks #24-#32.

## Signal scoring and ranking model

Task #32 adds the reusable scoring and ranking model used to compare generated signals.

### Score dimensions
The current model scores signals across:
- trend alignment
- confidence
- volume confirmation
- volatility quality
- structure quality

### Default weighting
- trend alignment ? `0.30`
- confidence ? `0.25`
- structure quality ? `0.20`
- volume confirmation ? `0.15`
- volatility quality ? `0.10`

### Current scoring pieces
- `SignalScoreInput`
- `SignalScoreBreakdown`
- `ScoreWeights`
- `SignalScorer`
- `SignalRanker`

### Ranking rules
- scores are normalized into a weighted composite
- signals are ranked by `ranking_score` when present, otherwise by `score`
- confidence acts as a secondary tie-breaker
- ranking assigns an explicit `ranking_position`

### Design outcome
The scanner signal payload can now carry:
- base score
- ranking score
- ranking position
- structured score breakdown

This prepares the scanner for:
- prioritization in the dashboard
- better monitoring of signal quality
- clearer downstream filtering and review rules
