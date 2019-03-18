FROM php:5.6

RUN apt-get update && apt-get install -y \
        libxml2-dev \
        git \
        nodejs nodejs-legacy \
    && docker-php-source extract \
    && docker-php-ext-install -j$(nproc) dom \
    && docker-php-source delete \

    # Cleanup
    && apt-get autoremove -y \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*
