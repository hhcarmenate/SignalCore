# Table: watchlists

## Purpose
Stores named collections of symbols/assets that can be scanned or reviewed together.

## Columns

| Column | Type | Null | Notes |
|---|---|---:|---|
| id | big integer / identity | no | Primary key |
| name | varchar(150) | no | Watchlist name |
| description | text | yes | Optional description |
| is_active | boolean | no | Default `true` |
| created_at | timestamp | no | Laravel standard |
| updated_at | timestamp | no | Laravel standard |

## Constraints
- primary key: `id`

## Indexes
- index on `is_active`

## Notes
This v1 is intentionally minimal:
- no slug
- no color
- no sort order
- no source/criteria fields yet
- single-user scope for now
