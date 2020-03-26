FROM wordpress:latest

# Switch to root user to be able to build the modules and the dependencies.
USER root

# Install the XDebug extension.
# The extension will be configured at runtime using the env var in the docker-compose configuration file.
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions xdebug

# Create the apache2-null script, we'll call this to trigger the WordPress setup in the container.
# This script will allow us to trigger the WordPress container automatic scaffolding of the WordPress installation if
# required, but it will NOT start the Apache server.
RUN echo "#!/usr/bin/env sh\nexit 0" > /usr/local/bin/apache2-noop && \
    chmod a+x /usr/local/bin/apache2-noop

# Create the apache2-background script modifying the apache2-foreground one.
# Remove the `-DFOREGROUND` option from the original script, this will work like the apache2-foreground script, but
# it will be daemonized.
RUN cat /usr/local/bin/apache2-foreground | sed -e 's/\s+-D\s*FOREGROUND\s+/ /g' > /usr/local/bin/apache2-background

# Change Apache configuration to listen on ports 8880 and 8843 in place of 80 and 443.
# By default, on Linux, non-root users cannot bind on ports < 1024.
# The container might be running as any user (depending on the host) that would map to a non sudoer.
RUN sed -i'.bak' -e 's/Listen 80/Listen 8880/g' -e 's/Listen 443/Listen 8843/g' /etc/apache2/ports.conf

# Create the `docker` user (1000:1000) and group to allow user remapping by fixuid.
RUN addgroup --gid 1000 docker && \
    adduser --uid 1000 --ingroup docker --home /home/docker --shell /bin/sh --disabled-password --gecos "" docker

# Add the fixuid script and configure it to remap file modes for WordPress core and plugins.
RUN USER=www-data && \
    GROUP=www-data && \
    curl -SsL https://github.com/boxboat/fixuid/releases/download/v0.4/fixuid-0.4-linux-amd64.tar.gz | tar -C /usr/local/bin -xzf - && \
    chown root:root /usr/local/bin/fixuid && \
    chmod 4755 /usr/local/bin/fixuid && \
    mkdir -p /etc/fixuid && \
    printf "user: $USER\ngroup: $GROUP\npaths: [ '/var/www/html', '/plugins' ]" > /etc/fixuid/config.yml

# Replace the default entrypoint with one that will run fixuid.
# The default command (`apache2-foreground`) will not change.
COPY docker-entrypoint.sh /usr/local/bin/tric-entrypoint.sh
RUN chmod a+x /usr/local/bin/tric-entrypoint.sh
ENTRYPOINT ["/usr/local/bin/tric-entrypoint.sh"]

# Run this container as www-data, not `root`.
USER www-data:www-data
