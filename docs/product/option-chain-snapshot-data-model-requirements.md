# Option Chain Snapshot Data Model Requirements

## Status
Defines the storage model used for option chain snapshots and contract pricing data.

## Purpose
The option chain snapshot model answers the question:

**How should SignalCore store time-based option contract market data without confusing it with stable contract identity?**

This model is required for:
- contract pricing snapshots
- liquidity analysis
- options filtering
- later contract suggestion logic

## Product role
A chain snapshot represents market data for an option contract at a specific point in time.

Important separation:
- option contract = stable identity
- option chain snapshot = time-based market state

This model should support storing repeated snapshots over time for the same contract.

## Source of truth
Snapshot storage should be driven by:
- normalized option contracts
- provider market data responses
- explicit snapshot timestamps

It should not rely on:
- overwriting the contract record with the latest bid/ask values
- UI-only cache state as the durable source of chain data

## Core goals
1. store repeated market snapshots safely
2. preserve time awareness explicitly
3. support later filtering and scoring workflows
4. preserve provider provenance
5. keep snapshot records queryable by contract and time window

## Required dimensions
Each chain snapshot must support at least:
- contract linkage
- bid / ask / mark fields
- volume and open interest
- implied volatility
- snapshot timestamp
- provider tracking

## Required fields
The snapshot model should support at least:
- `option_contract_id`
- `snapshot_at`
- `bid_price`
- `ask_price`
- `mark_price`
- `last_price`
- `volume`
- `open_interest`
- `implied_volatility`
- `provider`
- `provider_snapshot_id` (optional when available)
- `provider_metadata`
- `is_stale`

Optional later fields:
- `delta`
- `gamma`
- `theta`
- `vega`
- `rho`
- `intrinsic_value`
- `extrinsic_value`
- `spread`

## Field definitions

### option_contract_id
- foreign key to the option contract identity record
- required

### snapshot_at
- required timestamp
- represents when the market snapshot was taken or observed
- must be explicit, not inferred from `created_at`

### bid_price
- nullable decimal
- best current bid for the contract at snapshot time

### ask_price
- nullable decimal
- best current ask for the contract at snapshot time

### mark_price
- nullable decimal
- provider mark or internal midpoint-style mark when available

### last_price
- nullable decimal
- last traded price when available

### volume
- nullable integer
- daily trading volume at snapshot time when available

### open_interest
- nullable integer
- open interest at snapshot time when available

### implied_volatility
- nullable decimal
- normalized IV value when available

### provider
- required
- keeps source provenance explicit

### provider_snapshot_id
- optional
- supports source reconciliation if the provider supplies a snapshot identifier

### provider_metadata
- optional JSON
- supports source-specific payload details without polluting the normalized schema

### is_stale
- boolean flag indicating the snapshot is known to be stale or degraded

## Snapshot storage rules

### Rule 1: do not overwrite historical snapshots by default
The system should preserve snapshot history rather than only the latest row.

### Rule 2: uniqueness should be time-aware
Recommended uniqueness dimensions:
- `option_contract_id`
- `provider`
- `snapshot_at`

This allows multiple snapshots per contract across time while preventing duplicate same-source inserts for the same timestamp.

### Rule 3: contract identity and snapshot state must stay separate
A contract record should not become the latest market-data cache object.
That creates mixing between stable identity and time-based state.

### Rule 4: nullable market fields are allowed
Some providers will return partial snapshots.
The schema should tolerate incomplete fields while still preserving the snapshot record.

## Provider tracking rules
Snapshots should preserve provider provenance explicitly.

Why:
- chain data may later come from multiple providers
- field availability may differ by source
- stale/missing values should remain auditable by provider

## Query expectations
The snapshot model should support querying by:
- contract
- underlying symbol via contract relation
- expiration date via contract relation
- option type via contract relation
- latest snapshot per contract
- snapshot time range
- provider
- stale vs non-stale records

## Relationship to liquidity and filtering
Liquidity and filtering logic should not invent data structures separate from snapshots.
They should consume normalized snapshot fields like:
- bid/ask
- spread derivation
- volume
- open interest
- implied volatility

## Snapshot freshness expectations
The data model should support later freshness logic.

Examples:
- latest usable snapshot
- stale market data detection
- age-based filtering for contract suggestions

This task does not implement freshness policy yet, but the schema should support it.

## Relationship to later suggestion logic
Contract suggestion logic will later need snapshot data to answer questions like:
- which contracts are liquid enough?
- which spreads are acceptable?
- which expirations and strikes are attractive right now?

That means snapshot storage should prioritize clarity and queryability over clever denormalization.

## Acceptance criteria
This task is complete when:
- bid/ask/mark fields are defined
- volume and open interest are defined
- implied volatility is defined
- snapshot timestamps are defined
- provider tracking is defined
- snapshot storage rules are defined
- required fields list is defined
- relationship to contract identity is clarified

## Summary
The option chain snapshot model should give SignalCore a durable, time-aware storage layer for options pricing and market state.

The first version should prioritize:
1. explicit timestamped snapshots
2. separation from contract identity
3. provider provenance
4. storage rules that preserve history
5. compatibility with filtering and suggestion workflows
