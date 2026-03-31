# Local Development Environment Architecture

## Status
Defines the local development architecture used to run SignalCore consistently during development.

## Purpose
This document answers the question:

**How is SignalCore expected to run locally as a multi-service development system?**

It exists so development is not treated as a loose pile of containers and assumptions.

## Goals
- document the local runtime architecture
- clarify service responsibilities
- define network and host mapping expectations
- define local runtime assumptions
- capture setup boundaries and dependencies

## Local development model
SignalCore runs locally as a **Docker-based multi-service environment**.

The current local runtime is organized around these major areas:
- `apps/api` -> Laravel API
- `apps/web` -> Vue/Vite frontend
- `services/scanner` -> Python scanner service
- PostgreSQL -> shared application database
- nginx -> local reverse proxy

The development model assumes the stack is started through Docker Compose rather than by manually running services directly on the host.

## Runtime services

### 1. postgres
Purpose:
- persistent relational database for application storage

Current local role:
- stores core platform data such as symbols, watchlists, signals, outcomes, and later options data

Container details:
- image: `postgres:16`
- exposed host port: `5432`
- persistent volume: `postgres_data`

## 2. api
Purpose:
- Laravel application backend
- owns application domain logic, database migrations, HTTP APIs, and operational workflows

Current local role:
- primary application/server-side entry point for platform logic

Container details:
- built from `infra/docker/api/Dockerfile`
- mounted source: `./apps/api`
- internal app runtime port: `8000` (exposed internally through nginx)

## 3. web
Purpose:
- Vue 3 / Vite frontend application

Current local role:
- local dashboard and operator UI

Container details:
- built from `infra/docker/web/Dockerfile`
- mounted source: `./apps/web`
- dev server command runs Vite on `0.0.0.0:5173`
- `node_modules` is preserved through the named volume `signalcore_web_node_modules`

## 4. nginx
Purpose:
- reverse proxy and local traffic entry point

Current local role:
- presents the local stack through a single ingress point
- proxies frontend and backend traffic according to the nginx config

Container details:
- image: `nginx:1.27-alpine`
- host port: `80`
- config source: `infra/docker/nginx/default.conf`

## 5. scanner
Purpose:
- Python runtime for scanner/bot-related processing

Current local role:
- hosts market-scanning or strategy evaluation logic separate from the API runtime

Container details:
- built from `infra/docker/scanner/Dockerfile`
- mounted source: `./services/scanner`

## Service responsibilities by area

### apps/api
Responsibilities:
- database schema and migrations
- domain models and application logic
- workflow orchestration
- future scheduler/background-job integration points
- API endpoints for frontend and operational features

### apps/web
Responsibilities:
- operator-facing UI
- dashboards, lists, detail views, and monitoring surfaces
- consuming API outputs rather than directly reading the database

### services/scanner
Responsibilities:
- scanning and strategy logic
- future market-data ingestion helpers or strategy-processing workloads as needed
- isolated Python-side logic that should not be forced into the Laravel runtime

### postgres
Responsibilities:
- shared state persistence
- single local source of truth for persisted application data

### nginx
Responsibilities:
- local ingress and routing layer
- host-facing entry point for browser traffic

## Network and host mapping
The current local runtime is organized around container-to-container communication with nginx as the main browser-facing entry point.

### Host-facing ports
- `80` -> nginx
- `5432` -> postgres

### Internal service ports
- api -> `8000`
- web -> `5173`

### Routing assumptions
The browser is expected to reach the stack through nginx rather than by treating every service as a separate direct host target.

The exact proxy behavior is controlled by:
- `infra/docker/nginx/default.conf`

## Volume and persistence assumptions

### Persistent database state
PostgreSQL data is persisted through:
- `postgres_data`

### Frontend dependency persistence
Frontend `node_modules` is persisted through:
- `signalcore_web_node_modules`

### Source mounting
Development source is mounted from the host into the service containers for active code editing:
- `./apps/api` -> Laravel container
- `./apps/web` -> web container
- `./services/scanner` -> scanner container

This local model assumes live code iteration through bind mounts.

## Local runtime assumptions
The development environment currently assumes:
- Docker Compose is the primary local orchestrator
- PostgreSQL is available through the compose stack
- Laravel runs inside the API container
- Vue/Vite runs inside the web container
- scanner code runs inside the scanner container
- nginx fronts the application locally

The local setup should not depend on manually starting Laravel, Vite, or scanner processes on the host as the default workflow.

## Setup dependencies
A healthy local setup depends on:
- Docker / Docker Compose availability
- ability to build the API, web, and scanner images
- nginx config being present and valid
- Laravel environment/config being resolvable inside `apps/api`
- database credentials matching the compose environment

## Development boundary notes

### 1. Docker is the source of runtime consistency
The local stack should be treated as container-first.
That reduces host-specific drift.

### 2. Services should remain role-separated
The API, web, scanner, and database should not collapse into one runtime blob just because local development is “temporary.”

### 3. nginx is part of the local architecture, not an afterthought
If routing assumptions change, they should be documented through the platform/ops layer rather than silently patched in local instructions.

### 4. Database migrations belong to the API runtime
Schema changes should be run from the API container so the development workflow matches the real application boundary.

### 5. Scanner and API are intentionally separate
Even if both touch signal-related logic, they serve different runtime concerns and should remain explicitly separated in the local architecture.

## Directory alignment
Current top-level runtime alignment:
- `apps/api` -> application backend
- `apps/web` -> frontend application
- `services/scanner` -> scanner service
- `infra/docker` -> container definitions and runtime ingress config
- `docs/architecture` -> runtime/system architecture docs
- `docs/product` -> feature and product requirements
- `docs/database` -> schema/table docs

## Known future extensions
This architecture should remain compatible with later additions such as:
- scheduler/queue workers
- background job runners
- health-check endpoints and diagnostics flows
- improved logging/observability plumbing
- environment-specific config layering

## Acceptance criteria
This task is complete when:
- Docker services overview is documented
- app/service responsibilities are documented
- network and host mapping is documented
- local runtime assumptions are documented
- environment setup dependencies are documented
- local boundary notes are clear

## Summary
SignalCore local development is a Docker-based multi-service runtime composed of API, web, scanner, database, and nginx.

The first responsibility of this document is simple:
- make the local stack understandable
- make service boundaries explicit
- reduce hidden runtime assumptions
- give future platform/ops work a stable base to build on
