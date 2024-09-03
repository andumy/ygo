FROM php:8.2-fpm

RUN apt-get update

RUN apt-get install -y \
    git \
    zip \
    curl \
    sudo \
    unzip \
    supervisor \
    libxml2-dev \
    libicu-dev \
    libbz2-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libjpeg-dev \
    libmcrypt-dev \
    libreadline-dev \
    g++

RUN docker-php-ext-install \
    gd \
    bz2 \
    intl \
    iconv \
    bcmath \
    opcache \
    calendar \
    pdo_mysql \
    xml \
    zip \
    pdo \
    mysqli \
    sockets

RUN curl --insecure https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer

RUN rm /etc/localtime
RUN ln -s /usr/share/zoneinfo/Europe/Bucharest /etc/localtime
RUN "date"

RUN apt-get install -y ca-certificates curl gnupg
RUN sudo mkdir -p /etc/apt/keyrings
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | sudo gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_20.x nodistro main" | sudo tee /etc/apt/sources.list.d/nodesource.list
RUN apt-get update
RUN apt-get install nodejs -y

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

ARG uid
ARG gid

RUN groupadd -g $gid ygo
RUN useradd -G www-data,root -u $uid -g $gid -d /home/ygo ygo
RUN usermod -a -G ygo www-data
RUN mkdir -p /home/ygo/.composer && \
    chown -R ygo:ygo /home/ygo && \
    su ygo

RUN chown -R ygo:ygo /home/ygo


WORKDIR /var/www/ygo
