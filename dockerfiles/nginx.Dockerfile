# Use the Nginx image with stable-alpine version as the base image
FROM nginx:stable-alpine

# Set the working directory to /etc/nginx/conf.d inside the container
WORKDIR /etc/nginx/conf.d

# Copy the default Nginx configuration file from the 'nginx' directory of the project to the working directory inside the container
COPY nginx/nginx.conf .

# Rename the default Nginx configuration file to default.conf
RUN mv nginx.conf default.conf

# Set the working directory to /var/www/html inside the container
WORKDIR /var/www/html

# Copy all content from the 'src' directory of the project to the working directory inside the container
COPY src .

# Add a label to specify who is responsible for this Dockerfile
LABEL maintainer="leonardobryan32@gmail.com"
