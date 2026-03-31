# Table: signal_outcomes

## Purpose
Stores post-signal outcome tracking for analytics, validation, and later strategy comparison.

## Columns

| Column | Type | Null | Notes |
|---|---|---:|---|
| id | big integer / identity | no | Primary key |
| trade_signal_id | foreign key | no | References `trade_signals.id` |
| evaluation_state | varchar(50) | no | Example: `pending`, `target_hit`, `stop_hit`, `entry_not_reached` |
| outcome_label | varchar(30) | no | Example: `win`, `loss`, `neutral`, `unresolved` |
| entry_reached | boolean | no | Whether entry was reached |
| entry_reached_at | timestamp | yes | First time entry was reached |
| target_hit | boolean | no | Whether target was hit |
| target_hit_at | timestamp | yes | First time target was hit |
| stop_hit | boolean | no | Whether stop was hit |
| stop_hit_at | timestamp | yes | First time stop was hit |
| expired_without_entry | boolean | no | Evaluation expired before entry activation |
| expired_after_entry | boolean | no | Evaluation expired after entry but before resolution |
| evaluation_started_at | timestamp | yes | When evaluation began |
| evaluation_completed_at | timestamp | yes | When evaluation finished |
| expired_at | timestamp | yes | Effective evaluation expiry timestamp |
| evaluation_assumption_key | varchar(100) | yes | Example: `first_touch_v1` |
| ambiguity_reason | varchar(100) | yes | Example: `ambiguous_same_bar` |
| notes | text | yes | Optional review or evaluator notes |
| max_favorable_excursion | numeric(14,4) | yes | Optional favorable excursion metric |
| max_adverse_excursion | numeric(14,4) | yes | Optional adverse excursion metric |
| price_after_1d | numeric(14,4) | yes | Optional future benchmark snapshot |
| price_after_3d | numeric(14,4) | yes | Optional future benchmark snapshot |
| price_after_5d | numeric(14,4) | yes | Optional future benchmark snapshot |
| created_at | timestamp | no | Laravel standard |
| updated_at | timestamp | no | Laravel standard |

## Constraints
- primary key: `id`
- foreign key: `trade_signal_id` -> `trade_signals.id`
- unique: `trade_signal_id`

## Indexes
- unique index on `trade_signal_id`
- index on `evaluation_state`
- index on `outcome_label`
- composite index on (`evaluation_state`, `outcome_label`)
- index on `evaluation_completed_at`

## Notes
This table stores modeled outcome evaluation, not brokerage execution truth.

Important distinctions:
- signal lifecycle and signal outcome are separate concepts
- outcome evaluation may remain unresolved if not enough data exists yet
- ambiguity should be preserved explicitly rather than hidden when intrabar ordering cannot be proven

Snapshot fields like `price_after_1d`, `price_after_3d`, and `price_after_5d` are optional supporting analytics fields and do not replace explicit outcome state tracking.
