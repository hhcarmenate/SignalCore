# Relationships

## Current relationships

- one `watchlist` has many `watchlist_items`
- one `watchlist_item` belongs to one `watchlist`
- one `watchlist_item` belongs to one `symbol`
- one `symbol` can belong to many `watchlist_items`

- one `bot` has many `bot_runs`
- one `bot` has many `signals`

- one `bot_run` belongs to one `bot`

- one `signal` may belong to one `bot`
- one `signal` may belong to one `symbol`
- one `signal` may have many `notifications`
- one `signal` may have one `signal_outcome`

- one `notification` may belong to one `signal`
- one `notification` may belong to one `bot`

## Future-ready modeling notes

Signals should not be hardcoded to equities-only semantics.
The schema should support future domains through fields like:

- `domain`
- `market_type`
- `direction`
- `execution_hint`
- `signal_payload`
