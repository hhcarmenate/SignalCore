# SignalCore

SignalCore is a modular signal detection platform focused initially on stock-based opportunity detection with options-oriented execution hints.

## Initial scope
- Stocks / ETFs as signal source
- Options (`call` / `put`) as execution hint
- Python bots by strategy
- Laravel API
- Vue 3 dashboard
- PostgreSQL database
- Docker-based local development

## Documentation
- `docs/database/` -> initial database design documentation
- `docs/architecture/` -> runtime, platform, and service-boundary architecture
- `docs/product/` -> feature and product requirements
- `infra/env/` -> service-level environment templates and config ownership references

## Local development quickstart
- Start stack: `infra/scripts/dev/up.sh`
- Stop stack: `infra/scripts/dev/down.sh`
- Status: `infra/scripts/dev/status.sh`
- Run API migrations: `infra/scripts/dev/migrate-api.sh`
- Validate environment templates: `infra/scripts/dev/validate-env.sh`

## Status
Planning and architecture phase.
