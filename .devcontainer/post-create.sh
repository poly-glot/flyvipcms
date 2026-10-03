#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/.."

git config --global --add safe.directory "$PWD"

for _ in {1..30}; do
  mysqladmin ping -h db -uflyvip -pflyvip --silent 2>/dev/null && break
  sleep 2
done

mysql -h db -uroot -proot -e "CREATE DATABASE IF NOT EXISTS flyvip_test; GRANT ALL ON flyvip_test.* TO 'flyvip'@'%';"

[ -f .env ] || cp .env.example .env

composer install --no-interaction --prefer-dist

php spark migrate --all
php spark db:seed DemoSeeder

sudo chown vscode:vscode node_modules
npm ci --no-audit --no-fund
npx playwright install chromium

cat >> "$HOME/.zshrc" <<'ALIASES'
alias dbcli="mysql -h db -uflyvip -pflyvip flyvip"
alias qa="composer quality"
alias e2e="npm run test:e2e"
alias serve="php spark serve --host 0.0.0.0 --port 8080"
ALIASES
