# Scanner Service

Python service for SignalCore scanner execution and market analysis.

## Current scope

This service now includes the scanner run tracking model on top of:
- contracts
- candle data access
- indicators
- market context
- execution framework

## Run tracking model

The run tracking layer now provides reusable primitives for:
- scanner run records
- run summaries
- run-level errors
- status classification from execution outcomes

### Current tracking outputs
- `ScannerRunRecord`
- `ScannerRunSummary`
- `ScannerRunError`
- `ScannerRunTracker`

### Current tracked fields
- watchlist id
- timeframe
- status
- started at
- completed at
- symbols scanned count
- strategies executed count
- signals found count
- error count
- execution metadata
- normalized run errors

### Current status model
- `completed`
- `completed_with_errors`
- `failed`

### Tracking rule
The tracking layer is derived from execution reports.
That means:
- execution lifecycle and target failures happen in the execution framework
- run records and summaries are built from those results
- later persistence can store these records without changing strategy code
