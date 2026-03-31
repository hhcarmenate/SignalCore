# Infra Scripts

Bootstrap, development, and maintenance scripts for local infrastructure.

## Development scripts
- `infra/scripts/dev/up.sh` -> start/build the local stack
- `infra/scripts/dev/down.sh` -> stop the local stack
- `infra/scripts/dev/status.sh` -> inspect compose service status
- `infra/scripts/dev/migrate-api.sh` -> run Laravel migrations inside the API container
- `infra/scripts/dev/queue-work.sh` -> run the Laravel queue worker inside the API container
- `infra/scripts/dev/schedule-work.sh` -> run the Laravel scheduler loop inside the API container
- `infra/scripts/dev/validate-env.sh` -> validate that required environment templates exist

## Principle
Use scripts here for repeatable local platform actions that should not depend on remembering long compose commands.
