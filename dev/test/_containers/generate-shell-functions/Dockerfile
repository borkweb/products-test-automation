FROM php:7.3-cli

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
COPY stub-file.sh /usr/local/bin/stub-file.sh

RUN chmod a+x /usr/local/bin/docker-entrypoint.sh \
    && chmod a+x /usr/local/bin/stub-file.sh

WORKDIR /project

ENTRYPOINT ["docker-entrypoint.sh"]
