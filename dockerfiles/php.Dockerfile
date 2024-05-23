# use image php 8.2 FPM for base image
FROM php:8.2-fpm

# Tambahkan metadata
LABEL maintainer="HaiPigGi"

# set-up working dir

WORKDIR /var/www/html

# copy all from src

COPY src .

# install dependencies for postgresql

RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo pdo_pgsql

# add group and user

RUN addGroup -g 1000 app && addUser -G laravel -g laravel -s /bin/sh -D laravel

USER laravel

