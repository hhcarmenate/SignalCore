# Logging and Observability Baseline

## Status
Defines the minimum logging and observability baseline required to operate SignalCore reliably.

## Purpose
This document answers the question:

**What is the minimum visibility SignalCore needs so operators can understand what the platform is doing when things work, degrade, or fail?**

## Goals
- define application logging expectations
- define job and scheduler logging expectations
- define scanner run logging expectations
- define error logging expectations
- define minimum operational visibility requirements

## Baseline principle
SignalCore does not need enterprise observability theater on day one.
It does need enough visibility to answer:
- what ran?
- what failed?
- why did it fail?
- what data path was involved?
- what should an operator inspect next?

## Observability layers
The baseline should cover at least:
- application logs
- background job/scheduler logs
- scanner run logs
- error events
- operational state visibility

## 1. application logs
Application logs should cover:
- important domain workflow transitions
- provider-facing operations that affect platform behavior
- request-level failures when relevant
- major orchestration actions triggered by the app

The baseline should avoid noisy debug spam by default.
Logs should prefer structured, meaningful events over walls of unstructured chatter.

## 2. scheduler / job logs
The platform should log scheduler and job activity clearly enough to answer:
- which scheduled workflow ran?
- when did it start?
- did it dispatch work successfully?
- did the background job succeed, retry, or fail?

Minimum expectation:
- scheduled task start/finish visibility
- retry/failure visibility
- enough context to connect a failure back to the relevant workflow

## 3. scanner run logs
Scanner-related activity should be visible as a distinct operational stream.

Minimum visibility should include:
- scanner run start
- scanner run completion
- scanner run failure/warning state
- enough identifiers to match logs with scanner runs or monitor views later

The scanner should not be an opaque black box that only says “something broke.”

## 4. error logging
Error logging should preserve enough context to support diagnosis.

Recommended minimum error context:
- workflow or component name
- relevant ids or references
- provider or integration involved when relevant
- retry state when relevant
- failure reason/message

Important rule:
- errors should be loggable without leaking sensitive secrets into plain logs

## 5. operational visibility requirements
The baseline should support later answering questions like:
- is the scheduler alive?
- are jobs backing up?
- are scanner runs failing repeatedly?
- are ingestion workflows silently degrading?
- which platform component is currently unhealthy?

This task defines the minimum visibility expectations, not a full monitoring dashboard.

## Logging categories
The platform should remain compatible with at least these logging categories later:
- `application`
- `scheduler`
- `jobs`
- `scanner`
- `provider`
- `ops`

The exact implementation can evolve, but the architecture should not collapse everything into an indistinguishable log stream.

## Log quality expectations
Logs should be:
- actionable
- scoped to real workflow steps
- consistent enough to search later
- specific enough to diagnose failures

Logs should not be:
- cryptic one-line fragments with no context
- noisy dumps of full provider payloads by default
- secret-heavy output

## Error severity expectations
The platform should remain compatible with severity distinctions such as:
- info
- warning
- error
- critical

This is especially important for:
- scanner degradation
- ingestion failures
- repeated job failures
- scheduler outages

## Minimum diagnostic coverage
The baseline should give operators enough visibility to diagnose at least:
- application exceptions
- provider request failures
- job retries/failures
- scanner run failures
- ingestion workflow failures
- stale or missing operational workflows later

## Correlation expectations
The system should remain compatible with later correlation across:
- job runs
- scanner runs
- provider operations
- signal/outcome workflows

This does not require a full tracing platform now, but it should support carrying enough identifiers to connect related failures later.

## Storage/output expectations
The baseline should support log outputs that are:
- available locally during development
- container-friendly in Docker runtimes
- exportable or redirectable later to centralized observability systems

This task does not require choosing a full observability vendor.

## Relationship to health checks
Logging and observability are not the same as health checks.

- logging tells us what happened
- health checks tell us whether the platform appears operational right now

Both are needed, but they solve different problems.

## Relationship to platform evolution
This baseline should support later additions such as:
- failed-job dashboards
- scanner monitoring views
- structured log sinks
- operational alerting
- latency and freshness metrics

## Acceptance criteria
This task is complete when:
- application log expectations are defined
- job/scheduler log expectations are defined
- scanner run log expectations are defined
- error logging expectations are defined
- operational visibility requirements are defined
- minimum diagnostic coverage is defined

## Summary
SignalCore needs a practical logging and observability baseline that is good enough to diagnose real operational behavior without drowning the platform in useless noise.

The first responsibility of this baseline is simple:
- make important work visible
- make failures diagnosable
- make operations less guessy
- keep future monitoring work grounded in explicit expectations
