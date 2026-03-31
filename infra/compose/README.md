# Compose

Docker Compose is the canonical local orchestrator for SignalCore development.

## Current local entry point
- Root compose file: `docker-compose.yml`

## Current local runtime services
- `postgres` -> PostgreSQL database
- `api` -> Laravel backend
- `web` -> Vue/Vite frontend
- `nginx` -> local ingress / reverse proxy
- `scanner` -> Python scanner service

## Directory purpose
This folder exists to support future compose layering such as:
- shared compose fragments
- local-only overrides
- environment-specific compose additions

## Current guidance
Until compose layering is introduced, treat `docker-compose.yml` at the repository root as the source of truth for local runtime orchestration.
