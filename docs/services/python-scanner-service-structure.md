# Python Scanner Service Structure

## Status
Scanner service structure and core execution building blocks defined through Tasks #24-#30.

## Scanner run tracking model

Task #30 adds the reusable run tracking model used for auditing, visibility, and debugging.

### Core tracking pieces
- `ScannerRunTracker` converts execution reports into normalized run records
- `ScannerRunRecord` captures one run with status, timestamps, metrics, metadata, and errors
- `ScannerRunSummary` provides a compact reporting shape
- `ScannerRunError` normalizes per-failure error details

### Current tracked metrics
- `symbols_scanned_count`
- `strategies_executed_count`
- `signals_found_count`
- `error_count`
- execution lifecycle event count in metadata

### Current status model
- `completed`
- `completed_with_errors`
- `failed`

### Why this matters
This gives the scanner a stable run-tracking shape before persistence is wired into Laravel or a database table.

That means the next layers can evolve cleanly toward:
- audit history
- run monitor UI
- scheduler visibility
- debugging of partial failures

without forcing each strategy or executor to invent its own reporting format.

### Design rule
Execution and tracking stay separated:
- execution framework produces lifecycle + results + failures
- run tracker transforms that into a stable run record

That separation keeps the code easier to test and easier to persist later.
