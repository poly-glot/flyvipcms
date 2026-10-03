#!/bin/sh
set -eu

if [ "${SKIP_MIGRATIONS:-0}" != "1" ]; then
    php spark migrate --all --no-interaction
fi

exec "$@"
