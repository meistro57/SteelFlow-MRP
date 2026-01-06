#!/usr/bin/env bash
# tests/update_sh_test.sh

set -euo pipefail

# Basic smoke test to ensure update.sh supports dry-run execution without Docker.
REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO_ROOT"

assert_in_output() {
    local needle=$1
    local haystack=$2

    if [[ "$haystack" != *"$needle"* ]]; then
        echo "Expected to find '$needle' in output" >&2
        exit 1
    fi
}

# Run the script in dry-run mode to avoid Docker requirements.
OUTPUT=$(DRY_RUN=1 ./update.sh 2>&1)

assert_in_output "Dry run enabled - skipping Docker availability check" "$OUTPUT"
assert_in_output "Dry run enabled - skipping MySQL readiness check" "$OUTPUT"
assert_in_output "SteelFlow MRP Update & Deploy Script" "$OUTPUT"
assert_in_output "Dry run enabled - skipping docker compose ps" "$OUTPUT"

echo "update.sh dry-run test passed"
