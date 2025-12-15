FROM php:8.3-fpm

# OSパッケージ（zip, git など）とPHP拡張を入れる
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev libonig-dev \
    && docker-php-ext-install pdo_mysql zip intl \
    && rm -rf /var/lib/apt/lists/*

# composer を入れる（公式イメージからコピー）
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# php.ini
COPY docker/php/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www