# Table: bot_runs

## Purpose
Stores each bot execution instance for monitoring and analytics.

## Columns

| Column | Type | Null | Notes |
|---|---|---:|---|
| id | big integer / identity | no | Primary key |
| bot_id | foreign key | no | References `bots.id` |
| status | varchar(20) | no | `pending`, `running`, `success`, `failed` |
| timeframe | varchar(20) | no | Run timeframe |
| started_at | timestamp | no | Execution start |
| finished_at | timestamp | yes | Execution end |
| duration_ms | integer | yes | Derived duration |
| symbols_scanned | integer | no | Default `0` |
| signals_created | integer | no | Default `0` |
| error_message | text | yes | Error details |
| meta | jsonb | yes | Runtime metadata |
| created_at | timestamp | no | Laravel standard |
| updated_at | timestamp | no | Laravel standard |

## Constraints
- primary key: `id`
- foreign key: `bot_id` → `bots.id`

## Indexes
- index on `bot_id`
- index on `status`
- index on `started_at`
- composite index on (`bot_id`, `started_at`)
