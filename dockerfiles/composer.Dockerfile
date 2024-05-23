FROM composer:latest


# Tambahkan metadata
LABEL maintainer="leonardobryan32@gmail.com"

# Add group and set up user
RUN addgroup --gid 1000 laravel && adduser --ingroup laravel --gecos '' --disabled-password --shell /bin/sh --home /home/laravel laravel

# Set up the user

USER laravel

# Set up the working directory

WORKDIR /var/www/html

# create entrypoint

#--ignore-platform-reqs adalah opsi yang digunakan saat menjalankan Composer untuk mengabaikan persyaratan platform yang ditentukan dalam file composer.json

ENTRYPOINT [ "composer", "--ignore-platform-reqs" ]
