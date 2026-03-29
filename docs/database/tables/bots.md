# Table: bots

## Purpose
Stores metadata about each scanning strategy bot.

## Columns

| Column | Type | Null | Notes |
|---|---|---:|---|
| id | big integer / identity | no | Primary key |
| key | varchar(100) | no | Unique machine key |
| name | varchar(150) | no | Display name |
| domain | varchar(50) | no | Example: `stock` |
| strategy_key | varchar(100) | no | Internal strategy key |
| description | text | yes | Optional |
| is_active | boolean | no | Default `true` |
| default_timeframe | varchar(20) | no | Example: `daily`, `4h` |
| schedule_expression | varchar(100) | yes | Optional cron-like schedule |
| config | jsonb | yes | Strategy-specific config |
| created_at | timestamp | no | Laravel standard |
| updated_at | timestamp | no | Laravel standard |

## Constraints
- primary key: `id`
- unique: `key`

## Indexes
- unique index on `key`
- index on `domain`
- index on `is_active`
- optional index on `strategy_key`

## Initial records
- `trend-continuation`
- `breakout-confirmation`
- `mean-reversion-to-trend`
