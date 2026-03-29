# Table: notifications

## Purpose
Stores in-app notifications shown in the dashboard UI.

## Columns

| Column | Type | Null | Notes |
|---|---|---:|---|
| id | big integer / identity | no | Primary key |
| type | varchar(50) | no | Example: `signal.created`, `bot.failed` |
| title | varchar(255) | no | UI title |
| message | text | no | UI message |
| channel | varchar(30) | no | Initial value: `ui` |
| is_read | boolean | no | Default `false` |
| read_at | timestamp | yes | Read timestamp |
| signal_id | foreign key | yes | References `signals.id` |
| bot_id | foreign key | yes | References `bots.id` |
| payload | jsonb | yes | Optional metadata |
| created_at | timestamp | no | Laravel standard |
| updated_at | timestamp | no | Laravel standard |

## Constraints
- primary key: `id`
- foreign key: `signal_id` → `signals.id`
- foreign key: `bot_id` → `bots.id`

## Indexes
- index on `type`
- index on `channel`
- index on `is_read`
- index on `created_at`
- composite index on (`is_read`, `created_at`)
