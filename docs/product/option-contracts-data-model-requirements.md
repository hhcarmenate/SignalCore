# Option Contracts Data Model Requirements

## Status
Defines the data model used to represent option contracts linked to underlying symbols.

## Purpose
The option contracts data model answers the question:

**How should SignalCore represent individual option contracts so they can be stored, queried, filtered, and suggested consistently?**

This model is the foundation for:
- option chain storage
- contract filtering
- contract suggestion logic
- later options-oriented execution hints tied to underlying signals

## Product role
Option contracts are market instruments associated with an underlying symbol.
They are not the same as the underlying `symbols` records, but they must remain linked to them.

This model should support:
- contract identity
- contract lifecycle/activity state
- linkage to the underlying
- expiration and strike dimensions
- later snapshot/liquidity data attachment

## Source of truth
The option contract model should be driven by:
- the underlying symbol catalog
- normalized provider contract identifiers
- explicit contract attributes such as strike, expiration, and contract type

It should not depend on:
- provider-only ad hoc payload structures
- UI-generated contract strings as the primary identity

## Core goals
1. uniquely identify option contracts
2. preserve linkage to the underlying symbol
3. support active/inactive lifecycle handling
4. keep provider-specific identifiers without making them the only source of identity
5. support future chain snapshots and filtering workflows cleanly

## Relationship to underlying symbols
Every option contract should belong to exactly one underlying symbol.

Required relationship:
- `underlying_symbol_id` -> `symbols.id`

Questions this should answer cleanly:
- which underlying does this contract belong to?
- which contracts exist for this underlying?
- which expirations/strikes are available for the underlying?

## Required contract dimensions
Each option contract must support at least:
- underlying linkage
- option type
- strike
- expiration date
- contract symbol
- active/inactive handling

## Required fields
The data model should support at least these fields:
- `underlying_symbol_id`
- `contract_symbol`
- `provider_contract_symbol`
- `option_type`
- `strike_price`
- `expiration_date`
- `is_active`
- `status`
- `multiplier`
- `exercise_style`
- `shares_per_contract`
- `provider`
- `provider_metadata`
- `listed_at` (optional later)
- `delisted_at` (optional later)

## Field definitions

### underlying_symbol_id
- foreign key to the underlying record in `symbols`
- required

### contract_symbol
- normalized canonical contract symbol used internally
- required
- should be unique at the contract level

### provider_contract_symbol
- provider-specific contract identifier
- required when available
- useful for ingestion/reconciliation

### option_type
- required
- allowed values should support at least:
  - `call`
  - `put`

### strike_price
- required
- decimal precision must support realistic strike values

### expiration_date
- required
- date-type field, not free text

### is_active
- required boolean
- indicates whether the contract is currently considered active/usable

### status
- required string or enum
- suggested values:
  - `active`
  - `expired`
  - `delisted`
  - `inactive`

### multiplier
- optional in first pass but strongly recommended
- default should support the standard options contract multiplier model (typically `100`)

### exercise_style
- optional now, but model should allow values such as:
  - `american`
  - `european`

### shares_per_contract
- optional now, but useful for later valuation/position logic

### provider
- required
- preserves source provenance for contract identity and snapshots

### provider_metadata
- optional structured JSON for source-specific details

## Option type rules
The model must make option type explicit rather than encoding it only inside the symbol string.

Required values for MVP:
- `call`
- `put`

Why:
- filtering and suggestions should not need to parse type from a contract string every time

## Contract uniqueness rules
A contract should be uniquely identifiable by its core economic identity.

Recommended uniqueness dimensions:
- `underlying_symbol_id`
- `option_type`
- `strike_price`
- `expiration_date`

This composite uniqueness should define contract identity, even if the provider symbol format changes.

In addition, `contract_symbol` should be unique as the normalized internal identifier.

## Active/inactive handling
The model must support contract lifecycle state.

### Required behavior
- new/in-force contracts should be representable as active
- expired or delisted contracts should remain historically queryable
- historical snapshots and outcome context should not break just because a contract is no longer active

### Important rule
Do not hard-delete contracts simply because they become inactive.
Historical analytics will need them.

## Canonical contract identity guidance
SignalCore should prefer a normalized internal identity for contracts.

The canonical identity should be based on:
- underlying
- option type
- strike
- expiration

Provider symbols should support ingestion and reconciliation, but should not be the only durable identity layer.

## Relationship to options chain snapshots
The contract model defines **what the contract is**.
A chain snapshot model will later define **what the market data looked like at a point in time**.

That separation matters.

Examples:
- contract = stable identity
- snapshot = bid, ask, volume, open interest, greeks, etc. at a specific time

## Query expectations
The model should support querying by:
- underlying symbol
- expiration date
- option type
- strike range
- active status
- provider

## Future-compatible expectations
The model should remain compatible with later support for:
- multiple providers
- contract greeks snapshots
- contract liquidity scoring
- contract suggestion/ranking
- options-specific strategy execution helpers

## Acceptance criteria
This task is complete when:
- underlying linkage is defined
- option type is defined
- strike is defined
- expiration date is defined
- contract symbol handling is defined
- active/inactive handling is defined
- required fields list is defined
- uniqueness rules are defined
- relationship to snapshots is clarified

## Summary
The option contracts data model should give SignalCore a durable, normalized way to represent contracts without overfitting to provider payloads.

The first version should prioritize:
1. strong underlying linkage
2. normalized contract identity
3. explicit contract dimensions
4. lifecycle-safe historical handling
5. compatibility with later options chain and filtering layers
