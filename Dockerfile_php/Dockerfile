FROM php:7-fpm-alpine
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN apk add --no-cache ffmpeg
RUN apk --no-cache add shadow && usermod -u 1000 www-data
