# Signals List View Requirements

## Status
Defines the requirements for the main Signals List View used to review generated opportunities in the Dashboard and UI epic.

## Purpose
The Signals List View is the main working surface for browsing, triaging, and prioritizing persisted trade signals.

It should help the user answer these questions quickly:
- What are the best current opportunities?
- Which signals need review now?
- Which signals are already accepted, rejected, expired, or actioned?
- Which strategies, symbols, and timeframes are producing the best signals?

## Role in the product
This view is not just a marketing dashboard table.
It is the primary operational list for:
- review queue management
- quality triage
- strategy oversight
- signal discovery
- status visibility

## Source of truth
The view must be driven by persisted `trade_signals` data, not temporary scanner-memory samples.

The list should reflect the Signal Management layer fields already defined in the backend.

## Core layout
The list should support a dense, professional, dashboard-friendly table layout.

Recommended structure:
- header area
  - page title
  - result count
  - quick filter chips / controls
  - optional primary action(s)
- table/list area
  - sortable columns
  - row click opens signal detail view
- empty/loading/error states
- optional bulk-action toolbar for future expansion

## Required columns
The default list must show these columns:

1. **Symbol**
   - visible ticker/symbol
   - should be the strongest visual identifier in the row

2. **Strategy**
   - `strategy_key` or friendly strategy label
   - needed to compare signal origin quickly

3. **Direction**
   - bullish / bearish
   - visually distinguishable with color/status styling

4. **Execution Hint**
   - call / put
   - compact badge presentation is acceptable

5. **Timeframe**
   - e.g. `4h`, `1d`

6. **Status**
   - `new`, `pending_review`, `accepted`, `rejected`, `expired`, `actioned`, `ignored`
   - must be visible at a glance using clear badge styling

7. **Score**
   - core quality metric
   - should be numeric and sortable

8. **Confidence**
   - numeric confidence value
   - separate from score

9. **Review Priority**
   - high / medium / low
   - should be visually distinguishable

10. **Generated At**
   - `signal_generated_at`
   - supports recency judgment

## Nice-to-have columns (not required in first iteration)
- ranking position
- ranking score
- watchlist name
- notification priority
- expiration time
- source run reference

These can be added later or moved into advanced table settings.

## Default sorting
Default list ordering should favor review usefulness, not raw recency alone.

Recommended default sort:
1. `review_priority` ascending by importance (`high`, `medium`, `low`)
2. `review_score` descending
3. `signal_generated_at` descending

Reason:
- reviewers need to see the best actionable signals first
- older low-priority signals should not crowd out strong new setups

## Quick filtering requirements
The list needs lightweight, high-frequency filters available without opening an advanced modal.

Required quick filters:
- status
- direction
- timeframe
- strategy
- review priority

Recommended UI behavior:
- multi-select where practical
- sticky filters while navigating within the signals area
- clearly visible active filter state
- one-click reset / clear all

## Search behavior
The list should support a lightweight search input for:
- symbol
- strategy label/key
- maybe thesis text later

For first iteration, symbol + strategy search is enough.

## Row behavior
Each row should support:
- click to open signal detail
- clear hover state
- readable dense spacing
- badge-based scanning for direction/status/priority

Rows should not feel like generic CRUD rows.
This is an operator view, so scanability matters more than decorative styling.

## Badge and visual language
Use compact, consistent badges for:
- direction
- execution hint
- status
- review priority

Suggested tone mapping:
- bullish -> success / green
- bearish -> danger / red
- pending_review -> warning / amber
- accepted -> success
- rejected / ignored -> neutral or muted
- expired -> muted warning
- actioned -> accent / emphasized success
- high priority -> danger or strong accent
- medium priority -> warning
- low priority -> neutral

## States
The view must define explicit UI states.

### Loading state
- skeleton rows or table placeholders
- must preserve table structure so layout does not jump

### Empty state
Shown when there are zero signals overall.
Should explain:
- no signals exist yet
- scanner may not have produced any persisted signals

### Filtered empty state
Shown when filters remove all rows.
Should explain:
- no signals match current filters
- offer a quick clear-filters action

### Error state
Shown when signal loading fails.
Should include:
- concise error message
- retry action

## Density and responsiveness
Desktop-first is the right priority for this view.

### Desktop
- table layout should be the default
- columns should remain readable at common laptop widths

### Tablet / narrow layouts
- less important columns can collapse or hide
- row still needs core signal identity visible:
  - symbol
  - strategy
  - direction
  - status
  - score

### Mobile
Not the primary target for first pass.
It only needs to remain usable, not perfect.

## Relationship to current frontend
The current `SignalsTable.vue` is a basic placeholder showing:
- symbol
- direction
- hint
- score
- bot
- timeframe
- status

This should evolve into the persisted-signal operational view.

What should change:
- replace placeholder source data with real persisted signal shape
- remove `bot` as a core visible column unless product later needs it
- add review priority and confidence
- align status values with the Signal Management lifecycle
- support sorting/filtering based on real backend fields

## Data contract expectations for frontend
The frontend list view should expect each row to expose at least:
- `id`
- `symbol`
- `strategy_key` or `strategy_label`
- `direction`
- `execution_hint`
- `timeframe`
- `status`
- `score`
- `confidence`
- `review_priority`
- `review_score`
- `signal_generated_at`

Optional:
- `ranking_score`
- `ranking_position`
- `notification_priority`
- `expires_at`

## UX priorities
If trade-offs appear, prioritize in this order:
1. scanability
2. clear prioritization
3. low-friction filtering
4. stable sorting
5. visual polish

The list should feel like a real ops surface, not a toy table.

## Acceptance criteria
The Signals List View requirements are complete when:
- required columns are defined
- default sorting is defined
- quick filters are defined
- row behavior is defined
- state handling is defined
- relationship to persisted `trade_signals` is explicit
- current placeholder table gaps are clearly identified

## Summary
The Signals List View should become the main operational review table for persisted trade signals.

It must optimize for:
- discovering the best opportunities fast
- reviewing signals in priority order
- understanding status at a glance
- filtering signal flows without friction
