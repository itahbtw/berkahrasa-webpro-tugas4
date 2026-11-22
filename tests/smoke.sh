#!/usr/bin/env bash
# ============================================================
# Smoke test HTTP untuk Berkah Rasa (Tugas 4)
#
# Jalankan server lebih dulu, misalnya:
#   APP_ENV=local APP_DEBUG=1 php -S 127.0.0.1:8080
# Lalu:
#   bash tests/smoke.sh http://127.0.0.1:8080
#
# Akun demo dapat diganti lewat environment:
#   ADMIN_USER, ADMIN_PASS, CUSTOMER_USER, CUSTOMER_PASS
# ============================================================
set -u

BASE="${1:-http://127.0.0.1:8080}"
BASE="${BASE%/}"
ADMIN_USER="${ADMIN_USER:-admin}"
ADMIN_PASS="${ADMIN_PASS:-admin123}"
CUSTOMER_USER="${CUSTOMER_USER:-budi01}"
CUSTOMER_PASS="${CUSTOMER_PASS:-Budi#2026}"

TMP="$(mktemp -d /tmp/opencode/berkah-smoke.XXXXXX 2>/dev/null || mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

PASS=0
FAIL=0
FAILED_NAMES=()

green() { printf '\033[32m%s\033[0m' "$1"; }
red()   { printf '\033[31m%s\033[0m' "$1"; }

ok() {
  PASS=$((PASS + 1))
  echo "  $(green '✔') $1"
}

bad() {
  FAIL=$((FAIL + 1))
  FAILED_NAMES+=("$1")
  echo "  $(red '✘') $1${2:+ — $2}"
}

expect_code() {
  local name="$1" expected="$2" actual="$3"
  if [ "$expected" = "$actual" ]; then ok "$name (HTTP $actual)"; else bad "$name" "diharapkan HTTP $expected, didapat $actual"; fi
}

expect_contains() {
  local name="$1" file="$2" needle="$3"
  if grep -qiF -- "$needle" "$file"; then ok "$name"; else bad "$name" "teks '$needle' tidak ditemukan"; fi
}

csrf_from() {
  grep -o 'name="csrf-token" content="[^"]*"' "$1" | head -n1 | sed -E 's/.*content="([^"]*)"/\1/'
}

code_of() { printf '%s' "$1"; }

echo
echo "=== Berkah Rasa — Smoke Test HTTP ==="
echo "Target: $BASE"
echo

echo "[1] Halaman publik"
CODE=$(curl -sS -o "$TMP/index.html" -w '%{http_code}' "$BASE/index.php" || echo 000)
expect_code 'Beranda dapat dibuka' 200 "$CODE"
expect_contains 'Beranda memuat nama toko' "$TMP/index.html" 'Berkah Rasa'

CODE=$(curl -sS -o "$TMP/produk.html" -w '%{http_code}' "$BASE/produk.php" || echo 000)
expect_code 'Halaman produk dapat dibuka' 200 "$CODE"

CODE=$(curl -sS -o "$TMP/kontak.html" -w '%{http_code}' "$BASE/kontak.php" || echo 000)
expect_code 'Halaman kontak dapat dibuka' 200 "$CODE"
expect_contains 'Form kontak punya token CSRF' "$TMP/kontak.html" 'name="csrf_token"'

CODE=$(curl -sS -o "$TMP/masuk.html" -w '%{http_code}' "$BASE/masuk.php" || echo 000)
expect_code 'Halaman masuk dapat dibuka' 200 "$CODE"
expect_contains 'Halaman masuk memuat meta CSRF' "$TMP/masuk.html" 'name="csrf-token"'

echo
echo "[2] Proteksi area admin"
CODE=$(curl -sS -o /dev/null -w '%{http_code}' "$BASE/admin/index.php" || echo 000)
expect_code 'Dashboard admin menolak tamu (redirect)' 302 "$CODE"

CODE=$(curl -sS -o /dev/null -w '%{http_code}' "$BASE/admin/pengguna/index.php" || echo 000)
expect_code 'Kelola pengguna menolak tamu (redirect)' 302 "$CODE"

CODE=$(curl -sS -o /dev/null -w '%{http_code}' "$BASE/adminneo.php" || echo 000)
expect_code 'AdminNeo nonaktif secara default' 404 "$CODE"

echo
echo "[3] Proteksi CSRF API pesanan"
CODE=$(curl -sS -o "$TMP/order.json" -w '%{http_code}' \
  -X POST -H 'Content-Type: application/json' --data '{"nama":"Tes","no_wa":"081234567890","items":[{"id":1,"qty":1}]}' \
  "$BASE/api/order.php" || echo 000)
expect_code 'Checkout tanpa token CSRF ditolak' 419 "$CODE"
expect_contains 'Respons API menyebut sesi kedaluwarsa' "$TMP/order.json" 'Sesi kedaluwarsa'

echo
echo "[4] Login admin & otorisasi"
curl -sS -c "$TMP/admin.jar" -o "$TMP/admin-login.html" "$BASE/masuk.php"
TOKEN=$(csrf_from "$TMP/admin-login.html")
if [ -z "$TOKEN" ]; then
  bad 'Token CSRF login admin terbaca'
else
  ok 'Token CSRF login admin terbaca'
  CODE=$(curl -sS -b "$TMP/admin.jar" -c "$TMP/admin.jar" -o /dev/null -w '%{http_code}' \
    -X POST --data-urlencode "csrf_token=$TOKEN" \
    --data-urlencode "username=$ADMIN_USER" --data-urlencode "password=$ADMIN_PASS" \
    "$BASE/masuk.php" || echo 000)
  expect_code 'Admin berhasil login' 302 "$CODE"

  CODE=$(curl -sS -b "$TMP/admin.jar" -o "$TMP/dashboard.html" -w '%{http_code}' "$BASE/admin/index.php" || echo 000)
  expect_code 'Admin dapat membuka dashboard' 200 "$CODE"
  expect_contains 'Dashboard menampilkan panel admin' "$TMP/dashboard.html" 'Dashboard'

  CODE=$(curl -sS -b "$TMP/admin.jar" -o /dev/null -w '%{http_code}' "$BASE/admin/pengguna/index.php" || echo 000)
  expect_code 'Admin dapat membuka kelola pengguna' 200 "$CODE"
fi

echo
echo "[5] Login pelanggan & batas peran"
curl -sS -c "$TMP/customer.jar" -o "$TMP/customer-login.html" "$BASE/masuk.php"
TOKEN=$(csrf_from "$TMP/customer-login.html")
if [ -z "$TOKEN" ]; then
  bad 'Token CSRF login pelanggan terbaca'
else
  ok 'Token CSRF login pelanggan terbaca'
  CODE=$(curl -sS -b "$TMP/customer.jar" -c "$TMP/customer.jar" -o /dev/null -w '%{http_code}' \
    -X POST --data-urlencode "csrf_token=$TOKEN" \
    --data-urlencode "username=$CUSTOMER_USER" --data-urlencode "password=$CUSTOMER_PASS" \
    "$BASE/masuk.php" || echo 000)
  expect_code 'Pelanggan berhasil login' 302 "$CODE"

  CODE=$(curl -sS -b "$TMP/customer.jar" -o "$TMP/akun.html" -w '%{http_code}' "$BASE/akun.php" || echo 000)
  expect_code 'Pelanggan dapat membuka akun' 200 "$CODE"
  expect_contains 'Halaman akun menampilkan menu pesanan' "$TMP/akun.html" 'Pesanan Saya'

  CODE=$(curl -sS -b "$TMP/customer.jar" -o /dev/null -w '%{http_code}' "$BASE/admin/index.php" || echo 000)
  expect_code 'Pelanggan ditolak dari dashboard admin (403)' 403 "$CODE"

  CODE=$(curl -sS -b "$TMP/customer.jar" -o "$TMP/pesanan.html" -w '%{http_code}' "$BASE/pesanan-saya.php" || echo 000)
  expect_code 'Pelanggan dapat membuka pesanan sendiri' 200 "$CODE"
fi

echo
echo "[6] Header keamanan"
curl -sS -D "$TMP/headers.txt" -o /dev/null "$BASE/index.php"
expect_contains 'X-Content-Type-Options: nosniff' "$TMP/headers.txt" 'X-Content-Type-Options: nosniff'
expect_contains 'X-Frame-Options: SAMEORIGIN' "$TMP/headers.txt" 'X-Frame-Options: SAMEORIGIN'
expect_contains 'Content-Security-Policy aktif' "$TMP/headers.txt" 'Content-Security-Policy:'

echo
echo "----------------------------------------"
echo "Hasil: $PASS lulus, $FAIL gagal."

if [ "$FAIL" -gt 0 ]; then
  echo "Gagal:"
  for name in "${FAILED_NAMES[@]}"; do echo " - $name"; done
  exit 1
fi

echo "Semua smoke test lulus."
exit 0
