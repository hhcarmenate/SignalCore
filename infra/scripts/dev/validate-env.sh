#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../.." && pwd)"
cd "$ROOT_DIR"

required_files=(
  ".env.example"
  "apps/api/.env.example"
  "infra/env/api/.env.example"
  "infra/env/web/.env.example"
  "infra/env/scanner/.env.example"
)

missing=0
for file in "${required_files[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing required env template: $file"
    missing=1
  fi
done

if [ "$missing" -ne 0 ]; then
  exit 1
fi

echo "SignalCore environment templates are present."
