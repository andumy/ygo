FROM nginx:latest

COPY /docker/nginx/vhosts /etc/nginx/conf.d

RUN sed -i "s:PHP_PORT:9000:g" /etc/nginx/conf.d/default.conf
RUN sed -i "s:YGOPHP:ygo-php:g" /etc/nginx/conf.d/default.conf

ARG uid
ARG gid

RUN groupadd -g $gid ygo
RUN useradd -G www-data,root -u $uid -g $gid -d /home/ygo ygo
RUN usermod -a -G ygo www-data
RUN mkdir -p /home/ygo/.composer && \
    chown -R ygo:ygo /home/ygo

WORKDIR /var/www/ygo
