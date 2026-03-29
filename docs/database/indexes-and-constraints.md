# Indexes and Constraints

## Required unique constraints
- `symbols.ticker`
- `watchlists.slug` if slug is used
- `watchlist_items (watchlist_id, symbol_id)`
- `bots.key`
- `signal_outcomes.signal_id`

## Required foreign keys
- `watchlist_items.watchlist_id` → `watchlists.id`
- `watchlist_items.symbol_id` → `symbols.id`
- `bot_runs.bot_id` → `bots.id`
- `signals.bot_id` → `bots.id`
- `signals.symbol_id` → `symbols.id`
- `notifications.signal_id` → `signals.id`
- `notifications.bot_id` → `bots.id`
- `signal_outcomes.signal_id` → `signals.id`

## Important read-performance indexes
- `signals.triggered_at`
- `signals.status`
- `signals.bot_id`
- `signals.symbol_id`
- `bot_runs.started_at`
- `notifications.is_read`
- `watchlist_items.watchlist_id`
- `watchlist_items.symbol_id`
