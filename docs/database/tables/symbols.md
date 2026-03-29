# Table: symbols

## Purpose
Stores the master catalog of instruments/assets that SignalCore can track across multiple domains.

## Supported domains in v1+
- stocks
- ETFs
- options
- crypto
- sports bets
- prediction markets (including Polymarket-style markets)

## Columns

| Column | Type | Null | Notes |
|---|---|---:|---|
| id | big integer / identity | no | Primary key |
| asset_type | varchar(50) | no | `stock`, `etf`, `option`, `crypto`, `sports_bet`, `prediction_market` |
| symbol | varchar(120) | no | Human-facing symbol/ticker/code |
| name | varchar(255) | yes | Human-readable name |
| market | varchar(50) | no | Example: `us_equities`, `crypto`, `sports`, `prediction` |
| exchange | varchar(100) | yes | Example: `NASDAQ`, `BINANCE`, `POLYMARKET` |
| status | varchar(30) | no | Default `active` |
| currency | varchar(10) | yes | Example: `USD`, `USDT` |
| provider | varchar(50) | no | Default `manual` |
| provider_symbol | varchar(180) | yes | Provider-specific symbol/code |
| base_symbol_id | foreign key | yes | Self-reference to parent/base symbol |
| metadata | jsonb/json | yes | Domain/provider-specific details |
| created_at | timestamp | no | Laravel standard |
| updated_at | timestamp | no | Laravel standard |

## Constraints
- primary key: `id`
- foreign key: `base_symbol_id` → `symbols.id` (nullable, `nullOnDelete`)
- unique: (`asset_type`, `provider`, `provider_symbol`)

## Indexes
- index on (`market`, `asset_type`)
- index on `symbol`
- index on `status`

## Notes
`provider = manual` and `status = active` are used as defaults when a symbol is created manually from the watchlist flow.
