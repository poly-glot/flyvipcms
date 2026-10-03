#!/usr/bin/env bash
set -uo pipefail

BASE="${BASE_URL:-http://127.0.0.1:8090}"
JAR="$(mktemp)"
FAILURES=0

csrf() {
  curl -s -b "$JAR" -c "$JAR" "$BASE$1" | grep -o 'name="csrf_test_name" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//'
}

login() {
  : > "$JAR"
  local token
  token="$(csrf /login)"
  curl -s -o /dev/null -b "$JAR" -c "$JAR" -d "csrf_test_name=$token" --data-urlencode "email=$1" --data-urlencode "password=$2" "$BASE/login"
}

expect() {
  local want="$1" path="$2" got
  got="$(curl -s -o /dev/null -w '%{http_code}' -b "$JAR" -c "$JAR" "$BASE$path")"
  if [ "$got" = "200" ] && curl -s -b "$JAR" -c "$JAR" "$BASE$path" | grep -qiE 'ErrorException|Undefined (variable|array key)|Fatal error'; then got="200+error-in-body"; fi
  if [ "$got" = "$want" ]; then
    printf 'ok    %s %s\n' "$got" "$path"
  else
    printf 'FAIL  %s (wanted %s) %s\n' "$got" "$want" "$path"
    FAILURES=$((FAILURES + 1))
  fi
}

echo "== anonymous"
: > "$JAR"
expect 200 /
expect 200 /login
expect 302 /admin
expect 302 /portal

echo "== admin"
login admin@flyvip.test 'FlyVIP-Admin-2026!'
for path in /admin /admin/members /admin/members/new /admin/members/2 /admin/members/2/edit /admin/payments /admin/payments/new '/admin/payments/new?user_id=2' /admin/points /admin/points/adjust /admin/points/transfer /admin/reservations /admin/reservations/new /admin/reservations/1 /admin/aircrafts /admin/aircrafts/new /admin/aircrafts/1/edit /admin/aircraft-types /admin/aircraft-types/new /admin/maintenance /admin/maintenance/new /admin/airports /admin/airports/new /admin/routes /admin/routes/new /admin/routes/1/edit /admin/pilots /admin/pilots/new /admin/pilots/1/edit /admin/pilot-documents /admin/pilot-documents/new /admin/pilot-certifications /admin/pilot-certifications/new /admin/flight-schedule /admin/flight-schedule/new; do
  expect 200 "$path"
done
expect 302 /portal

echo "== member (personal)"
login ana.personal@flyvip.test 'FlyVIP-Personal-2026!'
for path in /portal /portal/points /portal/profile /portal/reservations /portal/reservations/new; do
  expect 200 "$path"
done
expect 302 /admin
expect 302 /admin/members

echo "== sub-member"
login lucia.family@flyvip.test 'FlyVIP-Submember-2026!'
for path in /portal /portal/points /portal/profile /portal/reservations; do
  expect 200 "$path"
done
expect 302 /admin

echo "== banned"
login banned.member@flyvip.test 'FlyVIP-Banned-2026!'
expect 302 /portal

echo "== sign out"
login ana.personal@flyvip.test 'FlyVIP-Personal-2026!'
token="$(csrf /portal)"
got="$(curl -s -o /dev/null -w '%{http_code}' -b "$JAR" -c "$JAR" -d "csrf_test_name=$token" "$BASE/logout")"
[ "$got" = "302" ] || [ "$got" = "303" ] && echo "ok    $got POST /logout" || { echo "FAIL  $got POST /logout"; FAILURES=$((FAILURES + 1)); }
expect 302 /portal

rm -f "$JAR"
[ "$FAILURES" -eq 0 ] && echo "ALL OK" || { echo "$FAILURES failure(s)"; exit 1; }
