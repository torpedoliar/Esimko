#!/usr/bin/env bash
# Only inside a disposable --network none container with the repository at /app.
# Deliberately does NOT use /var/lib/mysql, application .env, or bundled DB contents.
set -euo pipefail
work=$(mktemp -d /tmp/esimko-mysql-test.XXXXXX)
child=''
cleanup() {
    if [[ -n "$child" ]]; then
        mysqladmin --protocol=socket --socket="$work/mysql.sock" -uroot shutdown >/dev/null 2>&1 || true
        wait "$child" 2>/dev/null || true
    fi
}
trap cleanup EXIT
mysqld --no-defaults --initialize-insecure --user=root --datadir="$work/data" >"$work/init.log" 2>&1
mysqld --no-defaults --user=root --datadir="$work/data" --socket="$work/mysql.sock" \
    --pid-file="$work/mysql.pid" --skip-networking --mysqlx=0 --event-scheduler=OFF \
    --log-error="$work/server.log" &
child=$!
for attempt in $(seq 1 60); do
    if mysqladmin --protocol=socket --socket="$work/mysql.sock" -uroot ping >/dev/null 2>&1; then break; fi
    sleep 1
done
mysql --protocol=socket --socket="$work/mysql.sock" -uroot -e \
    'CREATE DATABASE esimko_synthetic_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
export APP_ENV=testing DB_CONNECTION=mysql DB_HOST=localhost DB_DATABASE=esimko_synthetic_test
export DB_USERNAME=root DB_PASSWORD='' DB_SOCKET="$work/mysql.sock"
export ESIMKO_ISOLATED_MYSQL_TEST=1
export MAIL_DRIVER=log QUEUE_CONNECTION=sync SESSION_DRIVER=array CACHE_DRIVER=array
export APP_KEY='base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA='
cd /app
# The test must explicitly check ESIMKO_ISOLATED_MYSQL_TEST and database name before DDL.
php "$@"
