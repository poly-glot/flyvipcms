FROM mcr.microsoft.com/devcontainers/php:1-8.4-bookworm

ENV DEBIAN_FRONTEND=noninteractive

RUN rm -f /etc/apt/sources.list.d/yarn.list \
  && apt-get update \
  && apt-get install -y --no-install-recommends \
       default-mysql-client \
       libfreetype6-dev \
       libicu-dev \
       libjpeg-dev \
       libonig-dev \
       libpng-dev \
       libzip-dev \
       unzip \
       zip \
  && docker-php-ext-configure intl \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" \
       bcmath \
       exif \
       gd \
       intl \
       mbstring \
       mysqli \
       opcache \
       pdo_mysql \
       zip \
  && pecl install pcov \
  && docker-php-ext-enable pcov \
  && rm -rf /var/lib/apt/lists/*

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
       libasound2 \
       libatk-bridge2.0-0 \
       libatk1.0-0 \
       libatspi2.0-0 \
       libcairo2 \
       libcups2 \
       libdrm2 \
       libgbm1 \
       libnspr4 \
       libnss3 \
       libpango-1.0-0 \
       libwayland-client0 \
       libx11-6 \
       libxcomposite1 \
       libxdamage1 \
       libxext6 \
       libxfixes3 \
       libxkbcommon0 \
       libxrandr2 \
  && rm -rf /var/lib/apt/lists/*

ENV PLAYWRIGHT_BROWSERS_PATH=/ms-playwright
RUN mkdir -p /ms-playwright && chown -R vscode:vscode /ms-playwright

RUN { \
      echo 'date.timezone = UTC'; \
      echo 'max_execution_time = 120'; \
      echo 'memory_limit = 512M'; \
      echo 'pcov.directory = /workspaces/flyvipcms/app'; \
      echo 'post_max_size = 64M'; \
      echo 'upload_max_filesize = 64M'; \
    } > /usr/local/etc/php/conf.d/codeigniter.ini
