# Health Checks and Operational Diagnostics

## Status
Defines the health checks and diagnostic mechanisms needed to detect runtime and data-pipeline problems.

## Purpose
This document answers the question:

**How should SignalCore determine whether the platform is healthy right now, and how should operators investigate problems when it is not?**

## Goals
- define service health checks
- define scheduler health visibility
- define ingestion health visibility
- define scanner health visibility
- define diagnostic command or endpoint expectations
- define basic troubleshooting guidance

## Core principle
Health checks answer **is it healthy now?**
Diagnostics answer **what is wrong and where should I look next?**

SignalCore needs both.
If it only has one, operations becomes guesswork.

## Health check layers
The platform should support health visibility at least across:
- service/runtime health
- scheduler/job health
- ingestion workflow health
- scanner health
- data freshness / pipeline health

## 1. service health checks
Minimum service health coverage should include:
- API health
- database reachability
- web/runtime ingress reachability when relevant
- scanner service reachability when applicable

The goal is not to prove every business function works perfectly.
The goal is to detect whether core runtime components are available enough to operate.

## 2. scheduler health visibility
The platform should be able to answer:
- is the scheduler running?
- when did it last run expected workflows?
- are scheduler-driven workflows lagging or stalled?

Minimum expectation:
- visibility into whether scheduled workflows are executing on time
- ability to detect obvious scheduler silence or backlog conditions later

## 3. ingestion health visibility
The platform should be able to answer:
- are contract/snapshot ingestion workflows running?
- when did ingestion last succeed?
- are provider fetches failing repeatedly?
- is stored data becoming stale?

Minimum expectation:
- visibility into last successful ingestion activity
- visibility into recent ingestion failures or stale data conditions

## 4. scanner health visibility
The platform should be able to answer:
- is the scanner reachable?
- when did the last scanner run succeed?
- are scanner runs repeatedly failing or degrading?

Minimum expectation:
- scanner run recency visibility
- scanner success/failure visibility
- enough context to distinguish runtime failure from strategy/output issues

## 5. data freshness visibility
Health checks should remain compatible with later freshness diagnostics such as:
- last market data update
- last signal evaluation pass
- last options snapshot sync
- stale operational data warnings

This matters because a platform can be “up” while still operationally stale.

## Diagnostic command / endpoint expectations
The platform should remain compatible with diagnostic tools such as:
- health endpoints
- operational status endpoints
- CLI diagnostics commands
- scheduler/job inspection commands
- failed-workflow summaries

This task does not require final implementation shape, but it must define what those diagnostics need to answer.

## Minimum diagnostic questions
Operators should be able to answer at least:
- which service is unhealthy?
- did the scheduler stop or lag?
- did ingestion fail recently?
- is scanner output stale or failing?
- is the data pipeline behind?
- what should be checked next?

## Diagnostic outputs
Recommended diagnostic outputs should later support:
- status (`healthy`, `degraded`, `unhealthy`)
- last success timestamp
- last failure timestamp when applicable
- summary reason
- next inspection target

That is more useful than vague “something is broken” output.

## Troubleshooting guidance expectations
Basic troubleshooting guidance should be able to point operators toward the next likely check.

Examples:
- if DB reachability fails -> inspect database container/service
- if scheduler is stale -> inspect scheduler process/job runner
- if ingestion is stale -> inspect provider failures / queue backlog
- if scanner is unhealthy -> inspect scanner container/runtime and latest run logs

## Relationship to logging and observability
Health checks and diagnostics depend on the logging/observability baseline, but they are not interchangeable.

- logs provide evidence and history
- health checks provide a current operational signal
- diagnostics provide a fast path to investigation

## Relationship to platform/runtime architecture
Health checks should align with the actual runtime boundaries of the local/platform architecture.
They should not assume a single-process application if the platform is multi-service.

## Scope boundary
The first version does not need a full enterprise monitoring stack.
It does need enough structure so operations can detect:
- total outages
- degraded workflows
- stale data paths
- obvious runtime failures

## Acceptance criteria
This task is complete when:
- service health checks are defined
- scheduler health visibility is defined
- ingestion health visibility is defined
- scanner health visibility is defined
- diagnostic command/endpoint expectations are defined
- troubleshooting guidance expectations are defined

## Summary
SignalCore should be able to tell operators whether the platform is healthy and, when it is not, point them toward the next useful place to investigate.

The first responsibility of this architecture is simple:
- detect outages
- detect degraded workflows
- detect stale pipelines
- reduce blind troubleshooting
