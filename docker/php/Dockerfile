FROM php:8.4-cli-bookworm

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        zip \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libwebp-dev \
        libonig-dev \
        libicu-dev \
        supervisor \
    # `--with-jpeg` and `--with-webp` rebuild GD with JPEG + WebP support so Intervention Image
    # can decode camera photos (without these, gd_info() reports JPEG Support: empty).
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        bcmath \
        pcntl \
        gd \
        zip \
        intl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/supervisord.conf /etc/supervisor/conf.d/laravel.conf
COPY docker/php/conf.d/uploads.ini /usr/local/etc/php/conf.d/zz-uploads.ini
COPY docker/entrypoint/laravel-init.sh /usr/local/bin/laravel-init.sh
RUN chmod +x /usr/local/bin/laravel-init.sh

WORKDIR /app

EXPOSE 8000 8080
