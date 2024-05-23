FROM nginx:stable-alpine

# Tambahkan metadata
LABEL maintainer="leonardobryan32@gmail.com"

# set-up working dir

WORKDIR /etc/nginx/conf.D

# copy nginx config

COPY nginx/nginx.conf . 

# Move to the root directory

RUN mv nginx.conf default.conf

# set up the new working directory

WORKDIR /var/www/html

COPY src . 