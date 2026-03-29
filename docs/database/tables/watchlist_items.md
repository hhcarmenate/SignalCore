# Table: watchlist_items

## Purpose
Stores symbol membership within a watchlist.

## Columns

| Column | Type | Null | Notes |
|---|---|---:|---|
| id | big integer / identity | no | Primary key |
| watchlist_id | foreign key | no | References `watchlists.id` |
| symbol_id | foreign key | no | References `symbols.id` |
| notes | text | yes | Optional operator notes |
| created_at | timestamp | no | Laravel standard |
| updated_at | timestamp | no | Laravel standard |

## Constraints
- primary key: `id`
- foreign key: `watchlist_id` → `watchlists.id` (`cascadeOnDelete`)
- foreign key: `symbol_id` → `symbols.id` (`cascadeOnDelete`)
- unique: (`watchlist_id`, `symbol_id`)

## Indexes
- index on `watchlist_id`
- index on `symbol_id`
- composite unique index on (`watchlist_id`, `symbol_id`)

## Notes
Rules implemented in v1:
- no duplicate symbols inside the same watchlist
- a symbol can appear in multiple watchlists
- items support nullable `notes`
- deleting a watchlist removes its items automatically
