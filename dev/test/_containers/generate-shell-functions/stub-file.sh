#!/usr/bin/env bash
FILE="$1"

# Aggregate the files.
cat "${FILE}" \
  | sed '/^<?php/d'  \
  | sed '/^namespace/d' \
  | sed '/^require/d' \
  | sed '/^[[:space:]]{2,}*$/d' \
  | sed '/^use/d' \
  >> dev/setup/php-shell-functions.php

 # Squeeze blank lines.
sed -i 'N;/^\n$/D;P;D;' dev/setup/php-shell-functions.php
