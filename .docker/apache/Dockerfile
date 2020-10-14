FROM php:5.6-apache

COPY php.ini /etc/php/5.6/php.ini

#RUN apt-get update \
#    && apt-get install -y git 
#    && apt-get php5-gd php5-mysql

#RUN docker-php-ext-install pdo pdo_mysql

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
   mv composer.phar /usr/local/bin/composer

RUN groupadd dev -g 999
RUN useradd dev -g dev -d /home/dev -m

WORKDIR /var/www/html/