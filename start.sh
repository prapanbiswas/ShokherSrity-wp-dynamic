#!/bin/bash
set -e

WP_DIR="$(pwd)/wordpress"
PHP_PORT=5000

echo "==> Starting WordPress..."

mkdir -p "$WP_DIR/wp-content/uploads"
chmod 755 "$WP_DIR/wp-content/uploads"

mkdir -p "$WP_DIR/wp-content/database"
chmod 755 "$WP_DIR/wp-content/database"

echo "==> WordPress running at http://localhost:$PHP_PORT"
PHP_CLI_SERVER_WORKERS=8 php -S 0.0.0.0:$PHP_PORT -t "$WP_DIR" "$WP_DIR/router.php"
