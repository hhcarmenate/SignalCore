# Python Scanner Service Structure

## Status
Approved implementation structure for Task #24.

## Purpose
Define the Python service layout that will support the SignalCore scanner engine in a way that is modular, testable, and aligned with the MVP roadmap.

This structure is intentionally designed to support the three initial strategies:
- Trend Continuation
- Breakout Confirmation
- Mean Reversion to Trend

It also assumes strategy activation will be controlled from the dashboard through database-backed state, not hardcoded in Python.

## Design goals
1. Keep scanner-specific logic in Python.
2. Keep persistence ownership in Laravel and PostgreSQL.
3. Allow strategies to be registered in code but toggled in the database.
4. Allow each watchlist to execute one or many strategies.
5. Separate data access, runtime orchestration, indicators, market context, and scanner contracts.
6. Make downstream tasks (#25 through #33) fit naturally into the same service layout.

## Proposed project layout

```text
services/scanner/
|- README.md
|- pyproject.toml
|- requirements.txt
|- src/
|  \- signalcore_scanner/
|     |- __init__.py
|     |- application/
|     |  \- main.py
|     |- config/
|     |  \- settings.py
|     |- contracts/
|     |  |- candle_access_plan.py
|     |  |- candle_point.py
|     |  |- candle_query.py
|     |  |- scanner_run_output.py
|     |  |- scanner_run_request.py
|     |  |- scanner_signal_output.py
|     |  |- strategy_definition.py
|     |  |- strategy_execution_input.py
|     |  |- strategy_state.py
|     |  |- watchlist_strategy_assignment.py
|     |  \- watchlist_symbol.py
|     |- data_access/
|     |  |- candle_access_planner.py
|     |  |- candle_repository.py
|     |  |- postgres_candle_query_builder.py
|     |  |- sql_candle_query_spec.py
|     |  |- strategy_state_repository.py
|     |  |- watchlist_strategy_assignment_repository.py
|     |  \- watchlist_symbol_repository.py
|     |- indicators/
|     |  |- atr.py
|     |  |- exponential_moving_average.py
|     |  |- indicator_calculator.py
|     |  |- indicator_snapshot.py
|     |  |- moving_averages.py
|     |  \- rsi.py
|     |- market_context/
|     |- runtime/
|     |  |- runtime_plan.py
|     |  \- scanner_runtime.py
|     |- strategies/
|     |  |- registry.py
|     |  \- implementations/
|     \- support/
\- tests/
   |- test_candle_data_access.py
   |- test_runtime_plan.py
   \- test_strategy_contracts.py
```

## Module responsibilities

### `application/`
Holds executable entrypoints for the scanner service.

Near-term responsibility:
- bootstrap runtime configuration
- build a runtime plan for a selected watchlist
- later trigger scanner execution flows

### `config/`
Holds Python-side configuration models.

Near-term responsibility:
- database connection settings
- runtime flags
- scanner batch sizing
- environment-derived defaults

### `contracts/`
Holds stable internal contracts for the scanner service.

Near-term responsibility:
- strategy definitions
- strategy state snapshots
- watchlist strategy assignments
- watchlist symbols
- candle query contracts
- strategy execution inputs
- signal outputs
- run outputs
- scanner run request payloads

This is now the foundation for Task #26.

### `data_access/`
Responsible for database-facing reads needed by the scanner.

Near-term responsibility:
- global strategy enable/disable state reads
- watchlist-to-strategy assignment reads
- watchlist symbol universe reads
- candle query planning
- SQL query construction for lookback reads
- in-memory/fake repository support for tests

This is now the foundation for Task #25.

### `indicators/`
Holds reusable indicator calculations shared by multiple strategies.

Current MVP coverage:
- SMA 20
- SMA 50
- EMA 20
- EMA 50
- RSI 14
- ATR 14
- volume SMA 20
- current close snapshot

Design rules:
- low-level utilities should stay pure and series-based
- high-level indicator aggregation should happen through `IndicatorCalculator`
- the reusable output shape should be `IndicatorSnapshot`
- strategies should consume shared snapshots instead of reimplementing formulas ad hoc

This is now the foundation for Task #27.

### `market_context/`
Holds reusable higher-level analysis helpers that are not strategy-specific.

Examples:
- trend direction
- structure bias
- support/resistance context
- regime classification

This should become the foundation for Task #28.

### `runtime/`
Owns scanner orchestration and execution planning.

Near-term responsibility:
- determine available strategies
- determine globally enabled strategies
- determine strategies assigned to a target watchlist
- build an execution plan from the valid intersection

Later responsibility:
- batch execution
- failure isolation
- per-watchlist run control
- scanner run tracking hooks

This should become the foundation for Tasks #29 and #30.

### `strategies/`
Owns strategy registration and shared strategy-facing abstractions.

Near-term responsibility:
- register the three MVP strategies in code
- keep a canonical strategy key per implementation

Current canonical MVP keys:
- `trend_continuation`
- `breakout_confirmation`
- `mean_reversion_to_trend`

### `strategies/implementations/`
Holds concrete strategy classes/modules.

Important rule:
- no direct database access in strategy implementations
- strategies should consume normalized inputs from runtime/data-access layers
- strategies should emit normalized outputs through scanner contracts

### `support/`
Holds generic helpers that do not belong to scanner domain modules.

Examples:
- formatting helpers
- time utilities
- lightweight parsing helpers

## Candle query and data access architecture

The candle data access layer should support scanner reads without coupling strategies to PostgreSQL details.

The intended read flow is:
1. load the target watchlist
2. resolve the active symbol universe for that watchlist
3. build a candle access plan from watchlist + timeframe + lookback inputs
4. build a PostgreSQL query shape optimized for per-symbol lookback reads
5. fetch candles grouped by symbol
6. pass normalized candles to runtime / strategies

### Why a planner + repository split
The split is intentional:
- `CandleAccessPlanner` decides what should be read
- `CandleRepository` decides how candles are fetched
- `PostgresCandleQueryBuilder` decides the SQL shape

This keeps responsibilities cleaner and makes test coverage easier.

## Scanner input/output contracts

Strategies should not receive raw database rows or free-form dictionaries.

### Input contract
Each strategy should receive a `StrategyExecutionInput` containing:
- `strategy_key`
- `watchlist_id`
- `symbol`
- `timeframe`
- `candles`
- `market_context`
- `max_lookback`
- `run_metadata`

### Output contract
Each strategy should return normalized `ScannerSignalOutput` entries wrapped in a `ScannerStrategyResult` and aggregated by `ScannerRunOutput`.

The signal payload contract standardizes:
- `strategy_key`
- `symbol`
- `timeframe`
- `direction`
- `thesis`
- `confidence`
- `score`
- `signal_category`
- `execution_hint`
- `levels` (`entry`, `stop_loss`, `target`)
- `indicators`
- `context`
- `metadata`

## Indicator computation architecture

The indicator layer exists to prevent every strategy from calculating its own incompatible version of the same studies.

### Core MVP indicators
The current indicator layer standardizes:
- simple moving averages (`sma_20`, `sma_50`)
- exponential moving averages (`ema_20`, `ema_50`)
- RSI (`rsi_14`)
- ATR (`atr_14`)
- volume moving average (`volume_sma_20`)
- latest close snapshot

### Boundaries
- indicator utilities operate on normalized numeric series only
- indicator utilities do not know about watchlists, SQL, or strategy toggles
- `IndicatorCalculator` aggregates studies from candle inputs into a reusable snapshot
- `IndicatorSnapshot` is the stable payload shape strategies can consume or embed in outputs

### Reuse rules
Strategies should reuse this layer for shared calculations instead of:
- redefining EMA logic per strategy
- inventing different RSI implementations
- embedding ATR formulas inside setup-specific code

That keeps the scanner more DRY, easier to test, and much less likely to drift into contradictory signals.

## Why this matters now
This avoids a bad architecture where:
- Python strategy files become the source of truth for active strategies
- dashboard toggles become cosmetic only
- watchlists cannot customize strategy selection
- every new strategy requires runtime rewiring
- strategies become responsible for SQL and symbol-universe logic
- every strategy invents a different signal payload shape
- every strategy invents a different indicator formula

Instead:
- the registry defines availability
- the database defines activation
- watchlist assignments define scope
- the data access layer defines read patterns
- the contracts define execution boundaries
- the indicator layer defines shared technical studies
- the runtime reconciles all of that before execution

## Boundaries with Laravel
Laravel remains the owner of:
- migrations
- schema definition
- operational scheduling
- API/dashboard controls
- persistence contracts
- strategy catalog and watchlist strategy assignment records

The Python scanner service remains responsible for:
- reading normalized scanner inputs
- building a valid execution plan per watchlist
- building candle access plans per watchlist/timeframe/lookback
- computing shared indicator snapshots
- executing strategy logic
- returning normalized scanner outputs

## Initial implementation guidance
For this phase, the repo should include:
- the directory layout
- a strategy registry
- a basic runtime planner
- a repository abstraction for global strategy state
- a repository abstraction for watchlist strategy assignments
- a repository abstraction for watchlist symbol reads
- a candle repository abstraction
- a PostgreSQL query builder for candle lookbacks
- stable strategy execution input/output contracts
- a reusable indicator computation layer and snapshot contract
- tests proving watchlist assignment + enabled-state planning
- tests proving candle access plan and lookback query behavior
- tests proving signal payload and run output contract behavior
- tests proving indicator utility and snapshot behavior

That is enough structure to make the next scanner tasks incremental instead of architectural rewrites.

## Follow-up task mapping
- #28 build context helpers inside `market_context/`
- #29 extend orchestration inside `runtime/`
- #30 add run tracking integration points from `runtime/`
- #31 implement the three MVP strategies in `strategies/implementations/`
- #32 add score/ranking output support to contracts and runtime
- #33 add multi-timeframe confirmation logic shared by runtime and strategies
- new Laravel task implement scanner strategy catalog and watchlist assignment migrations
