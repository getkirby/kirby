FROM php:7.1-apache

RUN echo yes | pecl install -f apcu
ADD php.ini /usr/local/etc/php
ENV PHPRC=/usr/local/etc/php
RUN kill -USR2 1

ADD entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
