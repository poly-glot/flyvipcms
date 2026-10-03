#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/.."

pkill -f "spark serve" 2>/dev/null || true
[ -f spark ] || exit 0

setsid nohup php spark serve --host 0.0.0.0 --port 8080 </dev/null >/tmp/serve.log 2>&1 &
disown || true
