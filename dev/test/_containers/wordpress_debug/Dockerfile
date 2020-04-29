# Builds a WordPress container extension from a base container.
# Some base containers would be `wordpress:latest` or `wordpress:cli`.

ARG WORDPRESS_BASE_CONTAINER

FROM ${WORDPRESS_BASE_CONTAINER}

ARG WORDPRESS_CONTAINER_USER=root

# Switch to root user to be able to build the modules.
USER root

# Install the XDebug extension.
# The extension will be configured at runtime using the env var in the docker-compose configuration file.
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions xdebug

# Copy over the custom entry point and make it world executable.
COPY docker-entrypoint.sh /usr/local/bin/tric-entrypoint.sh
RUN chmod a+x /usr/local/bin/tric-entrypoint.sh

# Switch back to the the previous user.
USER ${WORDPRESS_CONTAINER_USER}

# Use our custom entry-point.
ENTRYPOINT ["/usr/local/bin/tric-entrypoint.sh"]
