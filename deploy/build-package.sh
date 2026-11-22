#!/usr/bin/env bash
# Buat paket ZIP siap unggah (tanpa .env, log, dan unggahan pengguna).
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUT="$ROOT/deploy"
NAME="berkahrasa-tugas4-$(date +%Y%m%d-%H%M).zip"
mkdir -p "$OUT"

cd "$ROOT"
zip -r "$OUT/$NAME" . \
  -x '.git/*' \
  -x '.env' \
  -x 'logs/*.log' \
  -x 'gambar/uploads/*' \
  -x 'deploy/*.zip' \
  -x '_screenshot_login.php' \
  -x 'tests/tmp/*' \
  -x '*.swp' \
  -x '.DS_Store'

echo "Paket dibuat: $OUT/$NAME"
