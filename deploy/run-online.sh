#!/usr/bin/env bash
# Publikasikan aplikasi ke URL publik sementara via Cloudflare Quick Tunnel.
# Tidak memerlukan akun/domain sendiri. Aktif selama proses tunnel berjalan.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PORT="${PORT:-8091}"
BIN="${CLOUDFLARED:-/tmp/opencode/cloudflared}"
LOG="$ROOT/deploy/cloudflared.log"
URLFILE="$ROOT/deploy/LIVE_URL.txt"

if ! curl -sS -o /dev/null -w '%{http_code}' --max-time 3 "http://127.0.0.1:$PORT/index.php" | grep -q 200; then
  echo "Menjalankan server PHP di port $PORT ..."
  cd "$ROOT"
  nohup setsid php -S "127.0.0.1:$PORT" > "$ROOT/deploy/php-server.log" 2>&1 < /dev/null &
  sleep 2
fi

if [ ! -x "$BIN" ]; then
  echo "Mengunduh cloudflared ..."
  curl -sSL -o "$BIN" https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64
  chmod +x "$BIN"
fi

echo "Membuka Cloudflare Quick Tunnel ..."
nohup setsid "$BIN" tunnel --no-autoupdate --url "http://127.0.0.1:$PORT" > "$LOG" 2>&1 < /dev/null &

for _ in $(seq 1 20); do
  URL="$(grep -oE 'https://[a-zA-Z0-9-]+\.trycloudflare\.com' "$LOG" | head -n1 || true)"
  [ -n "$URL" ] && break
  sleep 1
done

if [ -z "${URL:-}" ]; then
  echo "Gagal mendapatkan URL. Lihat $LOG" >&2
  exit 1
fi

printf '%s\n' "$URL" > "$URLFILE"
echo "Aplikasi online di: $URL"
echo "URL juga disimpan di $URLFILE"
