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
5. Separate data access, runtime orchestration, indicators, and market context.
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
|     |  |- scanner_run_request.py
|     |  |- strategy_definition.py
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
   \- test_runtime_plan.py
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
- scanner run request payloads

This should become the foundation for Task #26.

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

Examples:
- moving averages
- ATR
- RSI
- volume moving averages

This should become the foundation for Task #27.

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

### Query strategy
The current design assumes:
- scanner runs are watchlist-scoped
- candles are filtered by timeframe
- lookback is capped per symbol, not globally across the full result set
- final-only reads are often desirable, but must remain configurable

The PostgreSQL query builder uses a window-function pattern:
- partition by `symbol_id, timeframe`
- order by `bar_time desc`
- apply `ROW_NUMBER()`
- keep the latest `N` candles per symbol

This is the correct shape for scanner reads because strategies usually need the last X bars for each symbol in the watchlist, not the last X rows globally.

### Performance notes
For scanner-facing reads, the intended database behavior is:
- filter by `symbol_id`
- filter by `timeframe`
- sort by `bar_time desc`
- use the existing candles uniqueness/indexing strategy

This aligns with the current Laravel migration indexes:
- `(symbol_id, timeframe, bar_time)`
- `(timeframe, bar_time)`

Near-term rule:
- scanner code should prefer batched watchlist reads over one query per symbol where practical
- strategies must not issue ad hoc SQL on their own

## Strategy activation architecture

Strategies must be:
- available in code
- visible in the dashboard
- enabled/disabled from the database

Therefore the scanner service should not decide active strategies from hardcoded if/else rules alone.

## Watchlist execution architecture

The runtime must support:
- many watchlists
- one strategy assigned to many watchlists
- one watchlist assigned to many strategies

The intended flow is:
1. strategy registry defines what strategies exist
2. database stores global enabled/disabled state per strategy
3. database stores watchlist-to-strategy assignments
4. dashboard updates both kinds of state
5. scanner runtime reads both repositories
6. execution plan includes only strategies assigned to the target watchlist and enabled globally

## Why this matters now
This avoids a bad architecture where:
- Python strategy files become the source of truth for active strategies
- dashboard toggles become cosmetic only
- watchlists cannot customize strategy selection
- every new strategy requires runtime rewiring
- strategies become responsible for SQL and symbol-universe logic

Instead:
- the registry defines availability
- the database defines activation
- watchlist assignments define scope
- the data access layer defines read patterns
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
- tests proving watchlist assignment + enabled-state planning
- tests proving candle access plan and lookback query behavior

That is enough structure to make the next scanner tasks incremental instead of architectural rewrites.

## Required Laravel follow-up
This architecture requires Laravel-owned schema support for at least:
- a strategy catalog table
- a watchlist-to-strategy assignment table
- enabled/disabled state persisted in database records

That schema work should be handled in a dedicated Laravel task so migrations remain ordered and owned by Laravel.

## Follow-up task mapping
- #26 define formal scanner contracts inside `contracts/`
- #27 build reusable indicators inside `indicators/`
- #28 build context helpers inside `market_context/`
- #29 extend orchestration inside `runtime/`
- #30 add run tracking integration points from `runtime/`
- #31 implement the three MVP strategies in `strategies/implementations/`
- #32 add score/ranking output support to contracts and runtime
- #33 add multi-timeframe confirmation logic shared by runtime and strategies
- new Laravel task implement scanner strategy catalog and watchlist assignment migrations
