# Options Data Ingestion Workflow Requirements

## Status
Defines how option chain and contract data should be ingested and updated in the platform.

## Purpose
The options ingestion workflow answers the question:

**How should SignalCore bring option contracts and option chain snapshots into the system reliably over time?**

This workflow is required so the Options Layer can operate on normalized, current-enough data instead of ad hoc fetches.

## Product role
The options ingestion workflow sits between:
- provider options data
- normalized option contract storage
- normalized option chain snapshot storage
- later filtering and suggestion logic

It governs process and update behavior.
It does not define contract identity or snapshot schema itself.

## Source of truth
The workflow should be driven by:
- approved option contract and snapshot models
- provider responses
- explicit sync scheduling rules
- explicit storage/update rules

## Core goals
1. sync contract identity cleanly
2. sync snapshot data repeatedly over time
3. avoid destructive overwrites of history
4. define scheduling expectations clearly
5. define failure/retry behavior clearly

## High-level workflow
Recommended sequence:

1. determine underlying symbols eligible for options ingestion
2. fetch option contract universe for each eligible underlying
3. normalize and upsert contract identities
4. fetch chain snapshot data for active contracts or selected expirations
5. persist time-aware snapshots
6. mark stale or failed sync states as needed
7. expose data for filtering and suggestion workflows

## Contract sync flow
The workflow should support contract identity synchronization separately from chain snapshot synchronization.

Recommended contract sync steps:
- fetch provider contract listings for an underlying
- normalize contract identity fields
- insert new contracts
- update activity/lifecycle status for known contracts
- avoid deleting historical contracts just because the provider no longer returns them in a narrow current window

## Chain snapshot sync flow
The workflow should support repeated snapshot ingestion for already-known contracts.

Recommended snapshot sync steps:
- fetch provider chain/pricing payloads
- map rows to known contract identities
- create new timestamped snapshot rows
- preserve provider provenance and snapshot timestamps
- mark stale/degraded snapshots when the provider data is incomplete or delayed

## Scheduling approach
The ingestion workflow should support scheduled operation by default.

Recommended cadence categories:
- slower contract identity sync cadence
- faster chain snapshot sync cadence

Why:
- contract identities change less frequently
- pricing/liquidity snapshots change more frequently

The exact cron frequency can come later, but the workflow must distinguish these two rhythms.

## Update frequency expectations

### Contract identity updates
Recommended expectations:
- refresh at a moderate cadence
- sufficient to capture new expirations/listings and lifecycle changes

### Chain snapshot updates
Recommended expectations:
- refresh more frequently during relevant market windows
- acceptable to reduce cadence outside active market periods if needed later

## Storage/update rules

### Rule 1: contract sync should favor upsert behavior
If a contract identity already exists:
- update lifecycle-relevant fields when needed
- preserve stable identity

### Rule 2: chain snapshots should append by time
Chain snapshots should create new time-aware records rather than overwrite historical rows.

### Rule 3: provider gaps should not force destructive cleanup
If a provider temporarily omits data:
- preserve existing history
- mark freshness/staleness issues explicitly later when possible

### Rule 4: inactive contracts should remain historically available
Expired/delisted contracts should remain queryable for later analytics and historical context.

## Error handling requirements
The workflow should define behavior for:
- provider request failures
- partial chain payloads
- malformed contract rows
- duplicate or inconsistent provider symbols
- rate limit scenarios

Recommended behavior:
- fail gracefully at the unit-of-work level when possible
- record retry-safe failure state or reason
- allow later retry without creating identity corruption

## Retry expectations
The ingestion workflow should support retry-safe behavior.

This means:
- re-running contract sync should not create duplicate identities
- re-running snapshot sync for the same provider/timestamp should not create duplicate records when uniqueness rules apply
- temporary provider failures should not permanently poison the ingest path

## Scope targeting expectations
The ingestion workflow should support targeting by:
- underlying symbol
- watchlist-linked underlying set later if needed
- expiration window later if needed
- provider

This prevents the workflow from becoming all-or-nothing from day one.

## Active market considerations
The workflow should remain compatible with market-aware cadence later.

Examples later:
- reduced snapshot frequency outside regular trading hours
- faster sync during active chain monitoring windows

This task does not require market-hours automation yet, but the process should not contradict it.

## Relationship to filtering and suggestion logic
Filtering and suggestion logic should consume normalized stored data.
They should not fetch provider chain data directly as their primary source.

That separation is important for:
- repeatability
- auditability
- reduced provider coupling

## Auditability expectations
The ingestion workflow should later be able to answer:
- when was this contract last synced?
- when was this snapshot observed?
- which provider supplied it?
- why is this data stale or missing?

This task does not require a full sync-monitor UI yet, but the workflow should support traceability.

## Acceptance criteria
This task is complete when:
- contract sync flow is defined
- chain snapshot sync flow is defined
- scheduling approach is defined
- update frequency expectations are defined
- error handling is defined
- storage/update rules are defined
- retry-safe expectations are defined
- relationship to filtering/suggestions is clear

## Summary
The options ingestion workflow should give SignalCore a reliable path for bringing contracts and chain data into the system without collapsing identity and time-based market state into one messy flow.

The first version should prioritize:
1. separate contract sync from snapshot sync
2. scheduled repeatability
3. append-friendly snapshot storage
4. retry-safe behavior
5. provider-failure resilience without destructive cleanup
