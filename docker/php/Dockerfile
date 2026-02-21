FROM php:8.2-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git curl unzip \
    libpq-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install \
        pdo_pgsql mbstring xml bcmath zip \
    && apt-get clean

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

CMD ["php-fpm"]

# Buat user non-root (samakan dengan host)
ARG UID=1000
ARG GID=1000

RUN groupadd -g ${GID} appuser \
 && useradd -u ${UID} -g appuser -m appuser

# Set permission workdir
RUN chown -R appuser:appuser /var/www

USER appuser

