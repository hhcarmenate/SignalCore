# Docker

Container definitions and service-specific Docker configuration.

## Current service builds
- `infra/docker/api/Dockerfile`
- `infra/docker/web/Dockerfile`
- `infra/docker/scanner/Dockerfile`
- `infra/docker/nginx/default.conf`

## Usage
These files define how the local multi-service runtime is built and routed.

The expected local entry point is still:
- `docker compose up -d --build`

## Boundary
Keep image build concerns and ingress configuration here.
Do not scatter runtime container definitions across unrelated documentation or app directories.
