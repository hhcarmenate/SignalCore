# Scanner Service

Python scanner service for SignalCore scanner execution and market analysis.

## Current scope

This service is structured to support the MVP scanner engine for:
- Trend Continuation
- Breakout Confirmation
- Mean Reversion to Trend

## Architecture principles

- Strategies live in Python code, but execution enable/disable state is controlled from the database.
- The dashboard is the control surface for toggling strategies on and off.
- Watchlists may execute one or many strategies.
- Candle reads must be watchlist-scoped and timeframe-aware.
- Shared logic must live outside individual strategies to keep the service DRY.
- Data access, runtime orchestration, indicators, market context, and signal contracts must remain separated.

## Proposed package layout

- `src/signalcore_scanner/config/`
  - environment-driven settings and runtime config
- `src/signalcore_scanner/contracts/`
  - scanner input/output contracts and protocol-style abstractions
- `src/signalcore_scanner/data_access/`
  - watchlist symbol reads, candle query planning, SQL query builders, and strategy state reads from PostgreSQL
- `src/signalcore_scanner/indicators/`
  - reusable indicator computations shared by all strategies
- `src/signalcore_scanner/market_context/`
  - higher-level trend, structure, regime, swing, and volume context helpers
- `src/signalcore_scanner/runtime/`
  - scanner runner, execution lifecycle, watchlist planning, and registry usage
- `src/signalcore_scanner/strategies/`
  - strategy registry, strategy base class, and strategy descriptors
- `src/signalcore_scanner/strategies/implementations/`
  - concrete MVP strategies
- `src/signalcore_scanner/support/`
  - shared utility helpers that do not belong to domain modules
- `tests/`
  - Python-side unit and integration tests for scanner logic

## Runtime direction

The scanner runtime should:
1. load configuration
2. load the target watchlist context
3. read globally enabled strategies from the database
4. read watchlist strategy assignments from the database
5. resolve strategy implementations from the registry
6. build a candle access plan for the selected watchlist and timeframe
7. pass a normalized input contract into each strategy
8. compute shared indicators from candle inputs
9. compute reusable market context from candles and indicators
10. collect normalized output contracts from each strategy
11. return normalized scanner outputs for persistence by downstream tasks

## Market context and trend analysis layer

The market context layer exists to keep higher-level interpretation out of individual strategies.

### MVP context helpers
The current layer now provides reusable helpers for:
- trend bias detection
- higher timeframe bias propagation
- swing high / swing low detection
- breakout and breakdown level definition
- volatility and regime classification
- volume context analysis

### Current structure
- `TrendBiasAnalyzer`
- `SwingLevelDetector`
- `RegimeClassifier`
- `VolumeContextAnalyzer`
- `MarketContextAnalyzer`
- `TrendAnalysisResult`

### Design rules
- market context should consume normalized candles and indicator snapshots
- market context should not know about watchlist assignments, dashboard toggles, or SQL
- strategies should reuse shared context snapshots instead of redefining breakout levels or trend bias logic ad hoc
- context outputs should be serializable and safe to embed in scanner payloads

### Why this matters
Without this layer, every strategy would invent its own idea of:
- what bullish trend means
- where the breakout level is
- whether volatility is high or normal
- whether volume is supportive

That would produce inconsistent signals fast. This layer keeps those reusable decisions centralized.

## Indicator computation layer

The indicator layer exists to prevent each strategy from recalculating or redefining common technical studies.

### MVP indicator set
The current MVP indicator layer supports:
- `sma_20`
- `sma_50`
- `ema_20`
- `ema_50`
- `rsi_14`
- `atr_14`
- `volume_sma_20`
- current `close`

### Design rules
- indicator functions operate on normalized numeric series
- strategies should consume shared indicator snapshots, not reimplement formulas ad hoc
- indicator utilities must not know about watchlists, strategy toggles, or SQL
- indicator output should remain machine-friendly and serializable into scanner payloads

### Current structure
- low-level utilities:
  - simple moving average
  - exponential moving average
  - RSI
  - ATR
- high-level aggregator:
  - `IndicatorCalculator`
- normalized result shape:
  - `IndicatorSnapshot`

### Why this matters
This gives the scanner a single reusable indicator layer for:
- trend continuation
- breakout confirmation
- mean reversion to trend

Without this layer, every strategy would drift into its own slightly different formulas and thresholds, which is exactly how signal systems become inconsistent and annoying to debug.

## Near-term follow-up tasks enabled by this structure

- #25 candle query and data access layer
- #26 scanner input and output contracts
- #27 indicator computation layer
- #28 market context and trend analysis layer
- #29 scanner execution framework
- #30 scanner run tracking model
- #31 MVP strategy set
- #32 signal scoring and ranking model
- #33 multi-timeframe confirmation rules
- new Laravel migration task for strategy catalog and watchlist-strategy assignments
