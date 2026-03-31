# Options Contract Suggestion Logic Requirements

## Status
Defines how directional signals should be enriched with suggested option contracts.

## Purpose
Options contract suggestion logic answers the question:

**Given an underlying directional signal, which option contracts should SignalCore suggest as the most practical candidates?**

This layer should turn underlying signal intent into options-oriented contract guidance without pretending to be full execution automation.

## Product role
Suggestion logic sits above:
- underlying trade signals
- option contract identity
- chain snapshot data
- contract liquidity/filtering rules

It is a recommendation layer.
It is not a brokerage execution engine.
It should not bypass hard eligibility filters.

## Source of truth
Suggestion logic should use:
- the underlying signal direction
- eligible option contracts after filtering
- expiration and strike heuristics
- liquidity-qualified chain data

It should not use:
- raw unfiltered chains
- purely arbitrary contract picks
- hidden heuristics that operators cannot understand later

## Core goals
1. map directional signals to option contract candidates consistently
2. keep recommendations liquid and practical
3. preserve explainable heuristics
4. support later scoring/refinement without redesign
5. avoid suggesting contracts that violate the eligibility layer

## Direction-to-contract mapping
The model must define how underlying signal direction maps to contract type.

### Minimum mapping
- bullish signal -> prefer `call` contracts
- bearish signal -> prefer `put` contracts

This mapping should remain explicit rather than implied.

## Expiration heuristics
Suggestion logic must support expiration selection heuristics.

Purpose:
- avoid contracts that are too close to expiry by default
- avoid unnecessarily distant expirations when the intended move horizon is shorter

Requirement:
- the model should support a bounded expiration selection heuristic
- the exact numeric defaults can evolve later

Examples later:
- prefer contracts inside a target days-to-expiration window
- rank nearer expirations higher only when still compatible with liquidity and strategy horizon

## Strike heuristics
Suggestion logic must support strike-selection heuristics.

Purpose:
- avoid arbitrary strike choice
- stay within practical distance from the underlying context

Requirement:
- the model should support configurable strike heuristics such as:
  - near-the-money preference
  - bounded percent distance from underlying price
  - bounded delta preference later

The first version does not need full pricing optimization, but it must define a disciplined selection approach.

## Liquidity checks
Suggestion logic must reuse the contract liquidity/filtering rules.

Important rule:
- a contract that fails the eligibility layer should not be promoted as a valid suggestion candidate

This keeps suggestion logic from acting like a loophole around risk controls.

## Optional delta-based selection
The design should remain compatible with optional delta-aware selection later.

Examples later:
- prefer calls within a target delta band for bullish signals
- prefer puts within a target delta band for bearish signals

For this definition task:
- delta-aware selection is optional
- the model should support it later without redesigning the recommendation contract

## Suggestion workflow
Recommended sequence:

1. take underlying directional signal
2. determine preferred option type from direction
3. fetch eligible contracts for the underlying
4. apply expiration heuristics
5. apply strike heuristics
6. preserve liquidity-qualified candidates only
7. rank or shortlist suggestion candidates
8. return explainable suggestion output

## Suggestion output guidelines
A first-pass suggestion output should support at least:
- underlying signal reference
- suggested contract id / symbol
- contract type
- strike
- expiration date
- liquidity summary fields
- rationale / heuristic summary
- optional alternate candidates

## Ranking expectations
If more than one contract remains eligible, the logic should support ranking or shortlisting.

The first version may use a simple heuristic rank, but it should be able to explain why one candidate is preferred over another.

Examples of ranking inputs later:
- nearest acceptable expiration
- nearest acceptable strike
- better spread quality
- stronger volume/open interest
- optional delta fit

## Explainability requirements
The system should later be able to explain suggestion rationale.

Examples:
- matched bullish signal with call contracts
- selected expiration inside preferred window
- preferred strike closest to the target moneyness profile
- excluded alternatives due to weak liquidity or excessive spread

This task does not require the UI yet, but the logic should support explainable outputs.

## Relationship to filtering rules
Filtering rules decide which contracts are eligible.
Suggestion logic decides which eligible contracts are best.

That separation must remain explicit.

## Relationship to underlying signals
Suggestion logic should enrich the underlying signal, not replace it.

This means:
- the underlying signal remains the source strategy/thesis
- the options layer adds a practical execution-oriented contract suggestion on top

## Risk boundary
The suggestion layer should remain recommendation-only in MVP.

It should not:
- place orders
- imply guaranteed profitability
- silently override liquidity and eligibility gates

## Acceptance criteria
This task is complete when:
- direction-to-contract mapping is defined
- expiration heuristics are defined
- strike heuristics are defined
- liquidity checks are defined
- optional delta-based selection compatibility is defined
- suggestion workflow is defined
- suggestion output guidelines are defined
- explainability expectations are clear

## Summary
Options contract suggestion logic should translate directional signals into practical, explainable, liquidity-aware contract candidates.

The first version should prioritize:
1. explicit direction mapping
2. bounded expiration and strike heuristics
3. mandatory liquidity gating
4. explainable candidate selection
5. recommendation-only MVP behavior
