#! /usr/bin/env bash

# Disable the XDebug extension if XDEBUG_DISABLE=1.
test "${XDEBUG_DISABLE:-0}" == 1 && {
  # Play the pipe game to avoid dealing with temp file issues.
  XDEBUG_CONFIGURATION_FILE="/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
  XDEBUG_CONFIGURATION=$(cat "${XDEBUG_CONFIGURATION_FILE}")
  echo "${XDEBUG_CONFIGURATION}" | sed -e '/^zend_extension.*xdebug/s/^zend_extension/;zend_extension/g' >"${XDEBUG_CONFIGURATION_FILE}"
  echo -e "\033[32mXDebug extension disabled.\033[0m"
}

## Now call the default entry-point file to start the web-server.
/usr/local/bin/docker-entrypoint.sh apache2-foreground
