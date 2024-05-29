# use image php 8.2 FPM for base image
FROM php:8.2-fpm


# set-up working dir

WORKDIR /var/www/html

COPY src .

# Install dependencies for PostgreSQL and Redis
RUN apt-get update \
    && apt-get install -y libpq-dev \
    && apt-get install -y libhiredis-dev \
    && apt-get install -y iputils-ping \
    && apt-get install -y dnsutils \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo pdo_pgsql

# add group and user

RUN addgroup --gid 1000 app && adduser --ingroup app --gecos '' --disabled-password --shell /bin/sh --home /home/laravel laravel


USER laravel

# Tambahkan metadata
LABEL maintainer="leonardobryan32@gmail.com"
