FROM php:8.4-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev unzip \
    && docker-php-ext-install pdo_sqlite \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    PORT=10000

COPY . .
COPY docker/app.env .env
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts \
    && mkdir -p var \
    && chown -R www-data:www-data var

EXPOSE 10000

CMD ["sh", "./start.sh"]
