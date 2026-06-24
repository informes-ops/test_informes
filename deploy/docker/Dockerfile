FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql mysqli gd \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY deploy/docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY deploy/docker/php-uploads.ini /usr/local/etc/php/conf.d/99-uploads.ini
COPY deploy/docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
