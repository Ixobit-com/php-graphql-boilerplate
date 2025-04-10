FROM php:8.3-fpm-alpine

ENV XDEBUG_VERSION=3.3.2

ARG UID

RUN apk update && apk add --no-cache autoconf g++ make automake sudo git linux-headers \
    libzip libpng libjpeg-turbo libwebp freetype \
    zlib-dev libzip-dev \
    libpng-dev libwebp-dev libjpeg-turbo-dev freetype-dev mysql-client mariadb-connector-c \
    && docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo_mysql \
    && pecl install xdebug-$XDEBUG_VERSION \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable xdebug
RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

RUN apk add bash unzip

COPY ./conf.d /usr/local/etc/php/conf.d

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet && \
    rm -rf /root/.composer/cache

RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

ENV USER appuser
RUN adduser --disabled-password -u $UID $USER \
    && echo "$USER ALL=(ALL) NOPASSWD: ALL" > /etc/sudoers.d/$USER \
    && chmod 0440 /etc/sudoers.d/$USER

USER appuser

WORKDIR /var/www/html
