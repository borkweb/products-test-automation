#!/usr/bin/env sh

# Find where this script is running from.
SCRIPT_DIR="$( cd "$(dirname "$0")" >/dev/null 2>&1 || exit ; pwd -P )"

# Start PHP interactive mode, loading our bootstrap file first.
php -a \
  -d "auto_prepend_file=${SCRIPT_DIR}/setup/php-shell-functions.php" \
  -d "cli.prompt=\e[1;36mtribe \>\e[0m "

