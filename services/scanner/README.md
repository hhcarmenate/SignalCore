# Scanner Service

Python service for SignalCore scanner execution and market analysis.

## Current scope

This service is structured to support the MVP scanner engine for:
- Trend Continuation
- Breakout Confirmation
- Mean Reversion to Trend

## Architecture principles

- Strategies live in Python code, but execution enable/disable state is controlled from the database.
- Watchlists may execute one or many strategies.
- Candle reads must be watchlist-scoped and timeframe-aware.
- Shared logic must live outside individual strategies to keep the service DRY.
- Data access, runtime orchestration, indicators, market context, and signal contracts must remain separated.

## Execution framework

The execution framework now provides reusable building blocks for scanner runs:
- `ScannerRuntime` for runtime planning
- `ExecutionPlanner` for expanding runtime plans into execution targets and batches
- `ScannerExecutionEngine` for batch execution with target-level error isolation
- `ExecutionLifecycleState` for lifecycle event tracking
- `ExecutionReport` for collecting results and failures

### Current lifecycle states
- `started`
- `batch_started`
- `target_started`
- `target_completed`
- `target_failed`
- `completed`

### Execution rules
- runtime plans are watchlist- and timeframe-aware
- enabled strategies are expanded into symbol-level execution targets
- batching is controlled through `batch_size`
- one failed target must not stop the rest of the run
- lifecycle state is captured even when a target fails

## Existing layers
- contracts for strategy input/output
- candle query and data access layer
- indicator computation layer
- market context layer
- runtime execution framework
