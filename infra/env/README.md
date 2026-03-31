# Environment Templates

Environment variable templates for API, web, and scanner services.

## Purpose
This directory contains non-secret environment templates and service-level config contracts.

## Rules
- safe templates/examples only
- no live secrets
- document required keys and ownership clearly
- local secret values should stay outside tracked repository files

## Service templates
- `infra/env/api/.env.example`
- `infra/env/web/.env.example`
- `infra/env/scanner/.env.example`

## Current baseline
- root `.env.example` -> shared local stack defaults
- `apps/api/.env.example` -> Laravel-specific application defaults
- `infra/env/*/.env.example` -> service ownership and template references
