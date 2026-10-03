FROM dunglas/frankenphp:1-php8.4-bookworm

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
  && apt-get install -y --no-install-recommends unzip \
  && rm -rf /var/lib/apt/lists/* \
  && install-php-extensions \
       bcmath \
       exif \
       gd \
       intl \
       mbstring \
       opcache \
       pdo_mysql \
       mysqli \
       zip

RUN { \
      echo 'date.timezone = UTC'; \
      echo 'error_log = /dev/stderr'; \
      echo 'max_execution_time = 60'; \
      echo 'memory_limit = 256M'; \
      echo 'opcache.enable = 1'; \
      echo 'opcache.max_accelerated_files = 20000'; \
      echo 'opcache.memory_consumption = 128'; \
      echo 'opcache.validate_timestamps = 0'; \
      echo 'post_max_size = 16M'; \
      echo 'upload_max_filesize = 16M'; \
    } > /usr/local/etc/php/conf.d/flyvip.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative \
  && mkdir -p writable/cache writable/logs writable/session writable/uploads \
  && chmod -R ug+rwX writable

COPY Caddyfile /etc/caddy/Caddyfile
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENV CI_ENVIRONMENT=production

EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
