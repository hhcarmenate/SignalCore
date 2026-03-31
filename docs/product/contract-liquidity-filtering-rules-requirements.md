# Contract Liquidity and Filtering Rules Requirements

## Status
Defines the rules used to filter option contracts for practical trade selection.

## Purpose
Contract liquidity and filtering rules answer the question:

**Which option contracts are eligible enough to be considered for suggestions or execution-oriented guidance?**

Without filtering, raw chains are too noisy and include contracts that are technically listed but operationally poor.

## Product role
These rules sit on top of:
- option contract identity
- option chain snapshots
- later suggestion logic

They define contract eligibility and quality gates.
They do not define final recommendation scoring by themselves.

## Source of truth
Filtering should use normalized stored data such as:
- volume
- open interest
- bid/ask values
- expiration date
- strike relative to underlying context later when needed

It should not depend on:
- provider-specific ad hoc filtering rules hidden from the platform
- purely visual/manual chain browsing

## Core goals
1. exclude obviously unusable contracts
2. keep the candidate set practical
3. preserve explicit and explainable gates
4. support later suggestion/ranking logic cleanly
5. reduce spread/liquidity risk in contract selection

## Required filtering dimensions
This ruleset must define at least:
- minimum volume
- minimum open interest
- maximum spread
- expiration window filters
- strike selection boundaries

## Minimum volume
The filtering layer should support a minimum daily volume threshold.

Why:
- zero-volume or near-zero-volume contracts can be listed but not practically tradable
- volume helps avoid dead contracts

Requirement:
- a configurable minimum volume threshold must exist
- contracts below that threshold should be filtered out by default unless a later workflow explicitly overrides it

## Minimum open interest
The filtering layer should support a minimum open interest threshold.

Why:
- open interest is a core signal of contract participation and tradability

Requirement:
- a configurable minimum open interest threshold must exist
- contracts below that threshold should be filtered out by default unless explicitly overridden later

## Maximum spread
The filtering layer should support spread-based gating.

Spread should be derivable from:
- `ask_price - bid_price`

The rules should support at least one of these later:
- absolute spread threshold
- relative spread threshold
- both

MVP requirement:
- the filtering model must support a maximum spread gate
- contracts with missing bid/ask should not pass liquidity screening by default

## Expiration window filters
The filtering layer should support expiration-based eligibility.

Why:
- too-near expirations may be too noisy or illiquid for some workflows
- too-far expirations may not match the intended holding window or contract suggestion logic

Requirement:
- the model must support a configurable minimum and maximum days-to-expiration window

This should later allow workflows such as:
- near-term only
- swing-friendly expirations
- exclude same-day/ultra-short-dated contracts by default

## Strike selection boundaries
The filtering layer should support strike-range gating.

Why:
- far out-of-the-money contracts may be too speculative or illiquid
- very deep in-the-money contracts may not match the intended suggestion profile

Requirement:
- the rules should support configurable strike selection boundaries
- the exact strike-selection formula can evolve later

The model should at least remain compatible with filters such as:
- near-the-money only
- bounded percent distance from underlying price
- bounded delta ranges later

## Default eligibility guidance
A contract should generally be considered eligible only if:
- volume is above the minimum threshold
- open interest is above the minimum threshold
- bid/ask spread is within the allowed maximum
- expiration falls within the accepted window
- strike falls inside the accepted selection boundaries
- required market fields are present

## Missing data handling
The rules must define behavior for incomplete snapshots.

Recommended behavior:
- contracts with missing critical fields should fail the eligibility screen by default
- critical fields include at least bid, ask, expiration context, and liquidity inputs needed by the active ruleset

Do not silently treat missing fields as “good enough.”

## Configurability expectations
The filtering layer should be configurable without rewriting the conceptual rules.

Examples later:
- conservative liquidity profile
- aggressive liquidity profile
- different expiration windows for different strategy families

The current task defines the rule categories and boundaries, not the final production threshold numbers.

## Relationship to suggestion logic
Filtering is the gate.
Suggestion logic is the ranking/selecting layer after the gate.

In simple terms:
- filtering decides who is allowed into the candidate pool
- suggestion logic decides which eligible contracts are best

That separation matters because it keeps hard exclusions separate from scoring logic.

## Relationship to future metrics
These rules should remain compatible with later reporting such as:
- how many contracts fail liquidity filters?
- which filters are most restrictive?
- do stricter liquidity gates improve suggestion quality?

## UX / explainability expectations
The system should later be able to explain why a contract is ineligible.

Examples:
- failed minimum volume
- failed open interest threshold
- spread too wide
- expiration out of range
- strike outside selection boundary

This task does not require the UI yet, but the rules should support explainable outcomes.

## Acceptance criteria
This task is complete when:
- minimum volume rules are defined
- minimum open interest rules are defined
- maximum spread rules are defined
- expiration window filters are defined
- strike selection boundaries are defined
- missing-data behavior is defined
- relationship to suggestion logic is explicit
- eligibility guidance is clear

## Summary
Contract liquidity and filtering rules should keep SignalCore focused on contracts that are practically usable rather than merely listed.

The first version should prioritize:
1. explicit liquidity gates
2. expiration window control
3. strike-boundary compatibility
4. default rejection of incomplete critical data
5. clear separation between filtering and ranking
