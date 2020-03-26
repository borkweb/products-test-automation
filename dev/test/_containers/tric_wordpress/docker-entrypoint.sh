#!/usr/bin/env bash

echo "APACHE_RUN_USER: ${APACHE_RUN_USER}"
echo "APACHE_RUN_GROUP: ${APACHE_RUN_GROUP}"
echo "UID: $(id -u)"
echo "GID: $(id -g)"

# Running this the first time will let the WordPress container docker-entrypoint.sh script scaffold the WordPress
# installation if required.
/usr/local/bin/docker-entrypoint.sh apache2-noop

# Now run the fixuid script.
# This will do two things:
# 1. Remap the UID and GID of the www-data user to the UID and GID of the current (host machine) user.
# 2. Remap the ownership (chown) of the `/var/www/html` and `/plugins` directories to avoid file permission issues.
eval $( fixuid )

# Now the WordPress files are in place, start the Apache webserver as the container would.
# The www-data user, at this point, is still in the root wheel and will still be able to bind port 80.
/usr/local/bin/docker-entrypoint.sh apache2-foreground
