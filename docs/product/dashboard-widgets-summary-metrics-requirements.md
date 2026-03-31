# Dashboard Widgets and Summary Metrics Requirements

## Status
Defines the dashboard widgets and summary metrics needed for fast situational awareness.

## Purpose
The dashboard home should answer one question quickly:

**What needs my attention right now?**

It should surface the most useful summary signals from the system without forcing the user to first open the signals list or run monitor.

## Product role
The dashboard overview is the summary layer above:
- Signals List View
- Signal Detail View
- Scanner Run Monitor

It is not a replacement for those views.
It is the entry point for fast context and prioritization.

## Design goals
1. Surface the most actionable information first.
2. Keep the first screen scannable in a few seconds.
3. Show both signal activity and scanner health.
4. Avoid vanity metrics that do not change decisions.
5. Provide obvious drill-down paths into the detailed views.

## Recommended dashboard widget set

### 1. New signals count
Purpose:
- show how many fresh signals need attention

Recommended definition:
- count of signals with status `new`

Why it matters:
- tells the operator whether fresh work exists right now

### 2. Pending review signals count
Purpose:
- show how many signals are currently queued for review

Recommended definition:
- count of signals with status `pending_review`

Why it matters:
- directly reflects workflow pressure

### 3. High-priority signals count
Purpose:
- surface the strongest opportunities immediately

Recommended definition:
- count of signals where `review_priority = high`

Why it matters:
- this is likely the most actionable metric on the page

### 4. Signals by direction
Purpose:
- show directional balance in current signal flow

Recommended breakdown:
- bullish count
- bearish count

Why it matters:
- gives fast market-orientation context

### 5. Signals by status
Purpose:
- show workflow distribution at a glance

Recommended breakdown:
- new
- pending_review
- accepted
- rejected
- actioned
- expired
- ignored

Why it matters:
- reveals whether signals are actually being processed or just piling up

### 6. Recent scanner runs summary
Purpose:
- expose scanner execution health without opening the run monitor

Recommended data shown:
- most recent run status
- recent failed/completed_with_errors count
- signals found from latest run

Why it matters:
- the dashboard should make it obvious when scanner health is degraded

### 7. Top-ranked opportunities widget
Purpose:
- highlight the best signals immediately

Recommended content:
- top 3 to 5 signals
- symbol
- strategy
- direction
- timeframe
- review priority
- review score or ranking score

Why it matters:
- gives immediate path from overview -> signal inspection

## Widget hierarchy
Recommended visual hierarchy:

### Top row
- New signals
- Pending review
- High-priority signals
- Recent run health

### Middle section
- Top-ranked opportunities

### Lower section
- Signals by direction
- Signals by status
- Recent scanner runs summary

Reason:
- the top row should answer urgent operational questions first
- charts/breakdowns should come after action-driving counts

## Widget behavior

### Click behavior
Each widget should act as a drill-down entry point when practical.

Examples:
- New signals count -> filtered signals list (`status = new`)
- Pending review -> filtered signals list (`status = pending_review`)
- High-priority signals -> filtered signals list (`review_priority = high`)
- Recent scanner runs -> run monitor view
- Top-ranked opportunities -> signal detail / signals list

### Empty behavior
If a widget has no data:
- do not break layout
- show zero or empty state gracefully

### Error behavior
If widget data fails to load:
- show a compact error treatment
- do not collapse the whole dashboard page

## Time windows
Widget metrics should use clearly defined windows where needed.

Recommended approach:
- current active signal state for signal counts
- recent scanner runs window for run summary (for example last 24h or last N runs)

The exact backend query window can be refined later, but the dashboard should avoid ambiguous metrics.

## Required metrics
Required MVP metrics:
- `new_signals_count`
- `pending_review_count`
- `high_priority_signals_count`
- `bullish_signals_count`
- `bearish_signals_count`
- `signals_by_status`
- `latest_run_status`
- `latest_run_signals_found_count`
- `recent_run_error_count`
- `top_ranked_signals`

## Nice-to-have metrics (not required first pass)
- average score of current active signals
- accepted vs rejected ratio
- watchlist with most current signals
- most active strategy
- signal conversion to actioned rate

These are interesting, but not mandatory for the first usable dashboard.

## Relationship to earlier work
This dashboard summary layer should reuse the fields already defined in:
- Signal Management
  - status
  - review priority
  - review score
  - direction
  - generated time
- Scanner Engine run tracking
  - run status
  - signals found
  - error count

This should be an aggregation layer, not a new data model invented in the frontend.

## UX priorities
Prioritize in this order:
1. actionability
2. scanability
3. drill-down usefulness
4. health visibility
5. visual polish

## Acceptance criteria
This task is complete when:
- the widget list is defined
- required summary metrics are defined
- widget hierarchy is defined
- click/drill-down behavior is defined
- empty/error expectations are defined
- relationship to signal and run data is explicit

## Summary
The dashboard widgets should give fast operational awareness, not vanity analytics.

They should help the user immediately see:
- what needs review
- what is high priority
- whether the scanner is healthy
- and which opportunities deserve attention first
