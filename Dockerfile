FROM php:8.0-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    libssl-dev \
    pkg-config && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

RUN pecl install mongodb && \
    docker-php-ext-enable mongodb

WORKDIR /app

COPY benchmark.php /app/
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN composer require mongodb/mongodb

ENTRYPOINT ["php", "/app/benchmark.php"]
