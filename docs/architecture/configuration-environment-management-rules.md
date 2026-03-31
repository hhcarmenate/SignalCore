# Configuration and Environment Management Rules

## Status
Defines how SignalCore should manage configuration and environment-specific settings across the project.

## Purpose
This document answers the question:

**How should SignalCore separate configuration, secrets, environment-specific settings, and shared defaults without creating drift or accidental leaks?**

## Goals
- define environment variable expectations
- define secrets handling boundaries
- define local vs shared config rules
- define service-specific config ownership
- define configuration documentation rules

## Core principle
Configuration should be explicit, environment-aware, and role-scoped.
Secrets should remain secret.
Shared defaults should remain documented.

The project should avoid:
- hidden machine-specific config assumptions
- secrets committed into tracked source files
- config values duplicated inconsistently across services

## Configuration categories
The platform should distinguish between at least these categories:
- shared documented defaults
- local developer overrides
- environment variables
- secrets
- service-specific runtime config

## Environment variable expectations
Environment variables should be used for:
- environment-dependent values
- infrastructure connection settings
- credentials and tokens
- runtime flags that vary by environment

They should not be used as a dumping ground for every possible constant without structure.

## Secrets handling boundaries
Secrets include examples such as:
- API keys
- provider tokens
- database passwords when environment-specific
- webhook secrets
- private service credentials

### Required rule
Secrets must not be hardcoded into tracked source or architecture docs.

### Required rule
Secret values should be injected through environment-specific mechanisms, not committed into shared repository defaults.

## Local vs shared config rules

### Shared config
Shared config should include:
- safe documented defaults
- config examples/templates
- required key names
- service-level config expectations

### Local config
Local config should include:
- machine-specific overrides
- local-only credentials
- developer-specific path or host adjustments when necessary

### Important rule
Local config should not become the hidden source of truth for how the platform works.
If a config key is required, it should be documented in shared project docs/templates.

## Service-specific config ownership
SignalCore is multi-service, so config ownership must remain explicit.

### API config ownership
The API should own configuration related to:
- application environment
- database connectivity
- queue/scheduler settings
- provider credentials used by Laravel-side workflows

### Web config ownership
The web app should own configuration related to:
- frontend runtime flags
- API base URL / frontend environment-specific values
- browser-safe public config only

### Scanner config ownership
The scanner service should own configuration related to:
- Python runtime behavior
- scanner-specific provider settings
- scanner-specific processing defaults

### nginx / infra config ownership
Infrastructure-level config should own:
- local host mapping
- routing rules
- ingress-level behavior

## Configuration documentation rules
The project should document:
- which config keys exist
- which service owns them
- whether they are required or optional
- whether they are secret or non-secret
- where they are expected to be supplied

Important rule:
- config docs should describe the contract, not reveal live secret values

## Local environment boundaries
The local environment should remain compatible with container-based configuration injection.

Recommended approach:
- keep shared config expectations documented in the repository
- keep local values outside tracked files when they include secrets or machine-specific overrides

## Multi-environment expectations
The platform should remain compatible with at least:
- local development
- shared/dev environments later
- production later

The configuration model should avoid assuming that local values are automatically valid for every environment.

## Drift-prevention guidance
To reduce configuration drift:
- required config contracts should be documented once clearly
- service ownership should be explicit
- defaults should be centralized where possible
- secret values should be injected rather than copied across random files

## Relationship to docs
Platform docs should explain:
- configuration boundaries
- ownership
- required variables
- secrets handling rules

Product docs should not become the main place where runtime config contracts are scattered.

## Relationship to operations
This configuration model should remain compatible with later:
- health diagnostics for missing config
- startup validation rules
- scheduler/queue/service-specific config checks
- deployment environment promotion flows

## Acceptance criteria
This task is complete when:
- environment variable expectations are defined
- secrets handling boundaries are defined
- local vs shared config rules are defined
- service-specific config ownership is defined
- configuration documentation rules are defined
- drift-prevention guidance is explicit

## Summary
SignalCore should manage configuration by separating shared contracts, local overrides, environment variables, and secrets cleanly.

The first responsibility of these rules is simple:
- reduce config drift
- protect secrets
- keep service ownership explicit
- make required runtime config understandable without exposing live values
