#!/usr/bin/env bash
# Jalankan tunnel publik dan simpan URL aktif ke deploy/LIVE_URL.txt setiap kali restart.
set -u
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BIN="/home/itah/.local/bin/cloudflared"
LOG="$ROOT/deploy/cloudflared.log"
URLFILE="$ROOT/deploy/LIVE_URL.txt"

"$BIN" tunnel --no-autoupdate --url http://127.0.0.1:8091 > "$LOG" 2>&1 &
PID=$!

for _ in $(seq 1 30); do
  URL="$(grep -oE 'https://[a-zA-Z0-9-]+\.trycloudflare\.com' "$LOG" 2>/dev/null | tail -n1 || true)"
  if [ -n "$URL" ]; then
    printf '%s\n' "$URL" > "$URLFILE"
    break
  fi
  sleep 1
done

wait "$PID"
