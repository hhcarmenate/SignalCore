# Environment Templates

Environment variable templates for API, web, and scanner services.

## Purpose
This directory is reserved for non-secret environment templates and service-level config contracts.

## Rules
- safe templates/examples only
- no live secrets
- document required keys and ownership clearly
- local secret values should stay outside tracked repository files

## Service areas
- `infra/env/api/`
- `infra/env/web/`
- `infra/env/scanner/`

## Current baseline
Repository-level `.env.example` remains the current shared starting point until service-specific templates are introduced.
