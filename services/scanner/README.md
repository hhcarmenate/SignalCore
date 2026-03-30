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
- Shared logic must live outside individual strategies to keep the service DRY.
- Data access, runtime orchestration, indicators, and market context must remain separated.

## Proposed package layout

- `src/signalcore_scanner/config/`
  - environment-driven settings and runtime config
- `src/signalcore_scanner/contracts/`
  - scanner input/output contracts and protocol-style abstractions
- `src/signalcore_scanner/data_access/`
  - candle reads, watchlist assignment reads, and strategy state reads from PostgreSQL
- `src/signalcore_scanner/indicators/`
  - reusable indicator computations shared by all strategies
- `src/signalcore_scanner/market_context/`
  - higher-level trend, structure, and regime helpers
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
6. execute only strategies assigned to the watchlist and enabled at the system level
7. load candles and related context through the data access layer
8. return normalized scanner outputs for persistence by downstream tasks

## Strategy activation model

Strategies must be discoverable in code but controllable in the database.

That means the service should support:
- a code registry of available strategies
- a database-backed enabled/disabled flag per strategy
- a database-backed watchlist-to-strategy assignment layer
- dashboard-driven state changes without Python code changes

## Watchlist execution model

The runtime must support:
- many watchlists
- one strategy assigned to many watchlists
- one watchlist assigned to many strategies

The effective execution set for a watchlist is:
- strategies registered in code
- intersected with globally enabled strategies
- intersected with strategies assigned to the selected watchlist

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
