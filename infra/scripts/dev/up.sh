#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../.." && pwd)"
cd "$ROOT_DIR"

docker compose up -d --build

echo
echo "SignalCore local stack is starting."
echo "- nginx: http://localhost"
echo "- postgres: localhost:5432"
