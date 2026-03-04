# Dockerfile
ARG PHP_VERSION=8.2
FROM php:${PHP_VERSION}-apache

# Apache modules needed by Symfony (routing, assets, etc.)
RUN a2enmod rewrite headers ssl

# System deps + PHP extensions commonly needed for Symfony
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libicu-dev libzip-dev \
 && docker-php-ext-configure intl \
 && docker-php-ext-install -j$(nproc) intl pdo pdo_mysql opcache zip \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer (official method: copy from composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache vhost (DocumentRoot => /var/www/html/public)
COPY docker/php/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/vhost-ssl.conf /etc/apache2/sites-available/000-default-ssl.conf

RUN a2ensite 000-default-ssl

# (Optionnel) opcache dev-friendly; à ajuster selon ton usage
RUN { \
  echo "opcache.enable=1"; \
  echo "opcache.enable_cli=0"; \
  echo "opcache.validate_timestamps=1"; \
  echo "opcache.revalidate_freq=0"; \
} > /usr/local/etc/php/conf.d/opcache-recommended.ini

WORKDIR /var/www/html

