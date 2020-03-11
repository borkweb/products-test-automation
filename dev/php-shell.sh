#!/usr/bin/env sh

SCRIPT_DIR=$(dirname "$0")
php -a -d "auto_prepend_file=${SCRIPT_DIR}/bootstrap.php"
