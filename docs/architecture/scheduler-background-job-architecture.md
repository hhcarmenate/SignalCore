# Scheduler and Background Job Architecture

## Status
Defines how scheduled jobs and background processes should run across SignalCore.

## Purpose
This document answers the question:

**How should SignalCore run scheduled work, background jobs, and asynchronous operational tasks without turning runtime orchestration into chaos?**

## Goals
- define scheduler responsibilities
- define queue/job boundaries
- define scanner trigger options
- define retry behavior
- define failure-handling expectations

## Architectural principle
SignalCore should separate:
- request/response application work
- scheduled orchestration work
- asynchronous/background processing work
- scanner-side processing work

The system should not assume every periodic or long-running responsibility belongs inside the same synchronous runtime path.

## Runtime layers

### 1. application request layer
Owned primarily by:
- Laravel API HTTP runtime

Responsibilities:
- serve API responses
- accept operator actions
- expose monitoring/diagnostic endpoints

Should not be responsible for:
- long-running scans
- retry loops
- periodic evaluation sweeps executed inline with user requests

## 2. scheduler layer
Purpose:
- trigger recurring operational workflows on a defined cadence

Recommended owner:
- Laravel scheduler as the central orchestration entry point for platform jobs

Responsibilities:
- trigger due workflow jobs
- schedule periodic data synchronization and evaluation work
- trigger scanner coordination jobs
- trigger cleanup, diagnostics, and reporting routines later

The scheduler should orchestrate work, not perform all heavy work inline.

## 3. background job layer
Purpose:
- execute asynchronous platform work outside user-facing request flows

Recommended owner:
- Laravel queue/job system for application-side background work

Responsibilities:
- outcome evaluation runs
- analytics aggregation refreshes
- options ingestion coordination
- notifications or reporting tasks later
- retryable operational tasks

## 4. scanner execution layer
Purpose:
- run scanner-specific or Python-specific workloads that are better kept outside the Laravel app runtime

Recommended owner:
- scanner service runtime triggered through defined coordination boundaries

Responsibilities:
- scanning market universes
- strategy computation workloads
- heavy Python-side processing

## Scheduler responsibilities
The scheduler should act as the top-level orchestrator for periodic work.

Recommended scheduler responsibilities include:
- trigger signal evaluation sweeps
- trigger analytics refresh workflows
- trigger options ingestion workflows
- trigger scanner run coordination
- trigger operational diagnostics checks

### Important rule
The scheduler should dispatch jobs or coordination events.
It should not become a giant script that performs every heavy task directly inside one scheduler tick.

## Queue / job boundaries
Background jobs should be used when work is:
- retryable
- non-trivial in duration
- potentially failure-prone
- asynchronous by nature
- not required to complete inside a user request

Examples:
- ingest option contracts
- ingest option chain snapshots
- evaluate pending signals
- refresh strategy comparison outputs
- generate tuning recommendations

## Recommended boundary rule
If a workflow may take meaningful time, call external providers, or benefit from retries, it should be modeled as a background job rather than synchronous inline logic.

## Scanner trigger options
The architecture should support scanner execution through explicit orchestration paths.

Recommended options:
- scheduler triggers scanner coordination on a cadence
- application jobs request scanner work in a controlled way
- manual operator-triggered reruns remain possible through application workflows later

### Important rule
Scanner execution should have a defined trigger boundary.
Laravel should not become tightly coupled to arbitrary direct shell hacks as the only scanner orchestration model.

## Job categories
The job architecture should support at least these conceptual categories:
- data ingestion jobs
- evaluation jobs
- analytics refresh jobs
- scanner coordination jobs
- diagnostics/maintenance jobs

This keeps background work organized and easier to reason about operationally.

## Retry behavior
Retry handling must be explicit.

Recommended rules:
- transient provider failures should be retryable
- malformed input or permanent domain errors should not retry forever
- retries should be bounded
- failure reason should remain visible/auditable

### Suggested retry model
Use retry-friendly jobs with:
- bounded attempt count
- delay/backoff support
- explicit terminal failure behavior

## Failure handling expectations
The architecture should support failure handling that is operationally honest.

Examples:
- log the failure with enough context
- preserve failed job visibility
- allow operator review or rerun later
- avoid silent drops for important scheduled workflows

### Important rule
No critical scheduler-driven workflow should fail invisibly.

## Idempotency expectations
Scheduled/background workflows should be designed to be retry-safe where practical.

Examples:
- contract sync should not create duplicate identities
- snapshot sync should not create duplicate time-equivalent records
- repeated evaluation runs should not corrupt outcome state

## Queue separation guidance
The system should remain compatible with separate queue categories later.

Examples:
- `default`
- `scanner`
- `analytics`
- `ops`

This task does not require final queue naming now, but the architecture should allow operational separation later.

## Operational run guidelines
Recommended high-level flow:
1. scheduler decides what is due
2. scheduler dispatches jobs or coordination tasks
3. queue/background layer executes application jobs
4. scanner layer handles specialized compute where needed
5. failures and retries are surfaced clearly
6. diagnostics can later inspect runtime health

## Relationship to local runtime
The local environment should remain compatible with later additions such as:
- dedicated scheduler container/process
- dedicated queue worker container/process
- scanner worker lifecycle coordination

This task defines the architecture, not the final container topology.

## Relationship to future observability
The scheduler/job architecture should support later observability work such as:
- run logs
- job status visibility
- failed-job diagnostics
- scheduler heartbeat and lag checks

## Acceptance criteria
This task is complete when:
- scheduler responsibilities are defined
- queue/job boundaries are defined
- scanner trigger options are defined
- retry behavior is defined
- failure handling expectations are defined
- operational run guidelines are documented

## Summary
SignalCore should use the scheduler as an orchestration layer, background jobs as the asynchronous execution layer, and the scanner service as a specialized compute layer.

The first responsibility of this architecture is simple:
- keep runtime responsibilities separated
- keep periodic work explicit
- keep retries and failures honest
- avoid turning the platform into one giant synchronous mess
