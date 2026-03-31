# Infra Scripts

Bootstrap, development, and maintenance scripts for local infrastructure.

## Development scripts
- `infra/scripts/dev/up.sh` -> start/build the local stack
- `infra/scripts/dev/down.sh` -> stop the local stack
- `infra/scripts/dev/status.sh` -> inspect compose service status
- `infra/scripts/dev/migrate-api.sh` -> run Laravel migrations inside the API container

## Principle
Use scripts here for repeatable local platform actions that should not depend on remembering long compose commands.
