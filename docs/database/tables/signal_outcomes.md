# Table: signal_outcomes

## Purpose
Stores post-signal tracking for analytics and bot comparison.

## Columns

| Column | Type | Null | Notes |
|---|---|---:|---|
| id | big integer / identity | no | Primary key |
| signal_id | foreign key | no | References `signals.id` |
| entry_reference_price | numeric(14,4) | no | Underlying price at signal |
| price_after_1d | numeric(14,4) | yes | Future benchmark |
| price_after_3d | numeric(14,4) | yes | Future benchmark |
| price_after_5d | numeric(14,4) | yes | Future benchmark |
| max_favorable_move | numeric(14,4) | yes | Best move after signal |
| max_adverse_move | numeric(14,4) | yes | Worst move after signal |
| outcome_label | varchar(30) | yes | Example: `win`, `loss`, `neutral` |
| notes | text | yes | Optional review notes |
| created_at | timestamp | no | Laravel standard |
| updated_at | timestamp | no | Laravel standard |

## Constraints
- primary key: `id`
- foreign key: `signal_id` → `signals.id`
- unique: `signal_id`

## Indexes
- unique index on `signal_id`
- index on `outcome_label`

## Notes
This table may be introduced in phase 2 or 3 if you want analytics earlier.
