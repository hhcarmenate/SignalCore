# Scanner Run Monitor View Requirements

## Status
Defines the requirements for the scanner run monitor view in the dashboard.

## Purpose
The Scanner Run Monitor View is the operational surface for understanding scanner executions over time.

It should answer these questions quickly:
- Did the scanner run successfully?
- Which runs failed or completed with errors?
- How many symbols were scanned?
- How many signals were found?
- Which runs need investigation?
- Which run should I inspect next?

## Product role
This is an operations/visibility view, not a signal list.

Its job is to expose run health, throughput, and anomalies across scanner executions.

## Source of truth
The view should be driven by persisted or normalized scanner run tracking data, not inferred from the signal list.

It should reflect the run tracking model defined in the Scanner Engine work.

## Core layout
Recommended structure:
- page header
  - title
  - summary count or quick run totals
  - filter controls
- run monitor table/list
- optional run detail side panel or drill-down route
- loading / empty / error states

## Required list columns
The default run list must show:

1. **Run reference**
   - run id or run reference
   - must uniquely identify the run visually

2. **Watchlist**
   - which watchlist the run targeted

3. **Timeframe**
   - `4h`, `1d`, etc.

4. **Status**
   - `completed`
   - `completed_with_errors`
   - `failed`
   - later maybe `running`

5. **Symbols scanned**
   - count of scanned symbols

6. **Strategies executed**
   - count of executed strategy targets or grouped strategy runs

7. **Signals found**
   - count of resulting signals

8. **Error count**
   - number of target failures or tracked errors

9. **Started at**
   - run start timestamp

10. **Completed at**
   - run end timestamp

## Nice-to-have columns
- duration
- source scheduler / trigger type
- batch count
- lifecycle event count
- model/version reference

These can wait if they complicate MVP too much.

## Default sorting
Recommended default order:
1. newest runs first (`started_at` desc)
2. then failed/completed_with_errors should still be visually easy to spot

Alternative acceptable approach:
- recent first
- status badges carry the health scanning burden

Reason:
- operators usually inspect the latest runs first
- health/state visibility can be handled with strong status styling

## Required filters
- status
- timeframe
- watchlist
- date / recency scope

Recommended later:
- trigger type
- run reference search
- only runs with errors
- minimum signals found

## Required row behavior
Each row must support:
- click to inspect run detail
- clear hover affordance
- strong scanability for status and metrics

Rows should behave more like an ops monitor than a plain database table.

## Visual language
Use clear status styling:
- completed -> success
- completed_with_errors -> warning
- failed -> danger
- running (future) -> info/accent

Use metric emphasis for:
- signals found
- error count

Error count should stand out when non-zero.

## Run detail drill-down requirements
Selecting a run should reveal or navigate to a detail surface that can show:
- high-level run summary
- watchlist/timeframe context
- status
- metrics
- error summary
- lifecycle / event visibility
- links to related signals if available later

The monitor list view does not need to contain all of this inline.

## Loading / empty / error states

### Loading state
- skeleton table or structured placeholders
- preserve layout stability

### Empty state
Show when there are no recorded runs yet.
Should explain that scanner run history is not available yet.

### Error state
Show when the monitor data fails to load.
Include retry affordance.

## Relationship to current system
Current scanner work already defined:
- run tracking model
- execution lifecycle
- failures
- summary metrics

This view should expose those concepts cleanly in UI.

## Frontend data expectations
The frontend monitor list should expect at least:
- `run_reference` or `id`
- `watchlist`
- `timeframe`
- `status`
- `symbols_scanned_count`
- `strategies_executed_count`
- `signals_found_count`
- `error_count`
- `started_at`
- `completed_at`

Optional:
- duration
- metadata summary
- lifecycle event count

## UX priorities
Prioritize in this order:
1. health visibility
2. recency awareness
3. failure discoverability
4. drill-down usefulness
5. visual polish

## Acceptance criteria
This task is complete when:
- required list columns are defined
- default sorting is defined
- filters are defined
- row behavior is defined
- run detail drill-down needs are defined
- loading/empty/error states are defined
- frontend data expectations are explicit

## Summary
The Scanner Run Monitor View should become the operational visibility surface for scanner executions.

It must help users quickly understand run health, throughput, and where to investigate next.
