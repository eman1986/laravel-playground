FROM php:8.4-fpm-alpine AS base

ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

COPY docker/conf.d/10-app.ini $PHP_INI_DIR/app.conf.d/

# Install install-php-extensions
RUN curl -L "https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions" -o /usr/local/bin/install-php-extensions
RUN chmod +x /usr/local/bin/install-php-extensions

# Download composer binary.
RUN curl -L "https://getcomposer.org/download/latest-stable/composer.phar" -o /usr/local/bin/composer && chmod +x /usr/local/bin/composer

RUN install-php-extensions igbinary imagick openswoole pcntl pdo_mysql redis uuid zip

WORKDIR /opt/webapp

COPY . .

FROM base AS api-local

RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN composer install

ENTRYPOINT ["php", "artisan", "octane:start", "--watch", "-workers=4", "--task-workers=12", "--max-requests=500"]

FROM base AS api-production

RUN install-php-extensions opcache

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN composer install --no-dev --optimize-autoloader --classmap-authoritative

# warm-up laravel's cache.
RUN php artisan config:cache
RUN php artisan event:cache
RUN php artisan route:cache
RUN php artisan view:cache

ENTRYPOINT ["php", "artisan", "octane:start", "-workers=8", "--task-workers=24", "--max-requests=1000"]

FROM base AS worker

RUN install-php-extensions opcache

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN composer install --no-dev --optimize-autoloader --classmap-authoritative

# warm-up laravel's cache.
RUN php artisan config:cache
RUN php artisan event:cache
RUN php artisan route:cache
RUN php artisan view:cache
