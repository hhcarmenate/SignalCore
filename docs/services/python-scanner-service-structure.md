# Python Scanner Service Structure

## Status
Scanner service structure and core execution building blocks defined through Tasks #24-#29.

## Runtime execution framework

Task #29 adds the reusable execution framework that coordinates strategy runs across watchlists, symbols, and timeframes.

### Core runtime pieces
- `ScannerRuntime` builds the high-level runtime plan
- `ExecutionPlanner` expands a runtime plan into execution targets and batches
- `ScannerExecutionEngine` executes those targets with error isolation
- `ExecutionLifecycleState` tracks lifecycle events for later observability/run tracking
- `ExecutionReport` returns successful results plus failures without collapsing the full run

### Batch execution model
The current framework expands:
- one watchlist
- one timeframe
- N enabled strategies
- M symbols

into a sequence of execution targets.

Those targets are then grouped into batches using the runtime plan batch size.

This gives the system a clean path toward later:
- parallel execution
- worker distribution
- queue-based orchestration
- run monitoring

without rewriting the shape of a scanner run.

### Error isolation model
The framework isolates failures per target.

That means:
- one broken symbol or strategy run is recorded as a failure
- remaining targets continue to execute
- lifecycle events still show where the failure happened
- the final execution report includes both successes and failures

This is critical for real scanner operation because one bad symbol payload or one strategy exception should not kill the whole batch.

### Lifecycle definition
Current lifecycle states:
- `started`
- `batch_started`
- `target_started`
- `target_completed`
- `target_failed`
- `completed`

This is the base vocabulary for future run tracking and monitoring work in Task #30.

### Design rules
- runtime planning decides what should run
- execution planning decides how targets are grouped
- strategy executors decide how one target is evaluated
- the execution engine coordinates lifecycle and failure isolation

Keeping those boundaries separate makes the framework easier to test and extend.
