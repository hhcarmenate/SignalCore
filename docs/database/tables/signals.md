# Table: signals

## Purpose
Stores signals detected by bots.

## Columns

| Column | Type | Null | Notes |
|---|---|---:|---|
| id | big integer / identity | no | Primary key |
| bot_id | foreign key | no | References `bots.id` |
| symbol_id | foreign key | yes | References `symbols.id`; nullable for future generic entities |
| domain | varchar(50) | no | Example: `stock` |
| market_type | varchar(50) | no | Example: `equities_options` |
| strategy_key | varchar(100) | no | Strategy identifier |
| timeframe | varchar(20) | no | Example: `daily`, `4h` |
| direction | varchar(20) | no | Example: `bullish`, `bearish` |
| execution_hint | varchar(30) | no | Example: `call`, `put` |
| confidence_score | numeric(5,2) | no | Example: `0.00` to `100.00` |
| signal_strength | numeric(5,2) | yes | Optional secondary score |
| thesis | text | no | Human-readable explanation |
| signal_payload | jsonb | no | Structured technical details |
| status | varchar(20) | no | `new`, `read`, `reviewed`, `dismissed`, `expired` |
| triggered_at | timestamp | no | Signal trigger time |
| expires_at | timestamp | yes | Optional expiration |
| created_at | timestamp | no | Laravel standard |
| updated_at | timestamp | no | Laravel standard |

## Constraints
- primary key: `id`
- foreign key: `bot_id` → `bots.id`
- foreign key: `symbol_id` → `symbols.id`

## Indexes
- index on `bot_id`
- index on `symbol_id`
- index on `domain`
- index on `timeframe`
- index on `direction`
- index on `status`
- index on `triggered_at`
- composite index on (`bot_id`, `triggered_at`)
- composite index on (`symbol_id`, `triggered_at`)
- optional composite index on (`status`, `triggered_at`)

## Notes
`signal_payload` should include explainable reasons, such as:

- trend state
- EMA alignment
- RSI values
- breakout level
- volume confirmation
- score components

This keeps the signal auditable.
