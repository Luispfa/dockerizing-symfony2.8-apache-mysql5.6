FROM php:7.2-apache

RUN apt-get update && \
    apt-get install -y net-tools git acl zip unzip zlib1g-dev libpng-dev libjpeg-dev curl

RUN docker-php-ext-install gd
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install sockets

COPY php/php.ini /etc/php/7.2/php.ini
COPY certificate/wildcard.juntadeaccionistas.es.crt /etc/apache2/ssl/wildcard.juntadeaccionistas.es.crt
COPY certificate/ca.bundle.openprovider.esentialssl.wildcard /etc/apache2/ssl/ca.bundle.openprovider.esentialssl.wildcard
COPY certificate/wildcard.juntadeaccionistas.es.key /etc/apache2/ssl/wildcard.juntadeaccionistas.es.key

COPY vhosts/000-default.conf /etc/apache2/sites-enabled/
COPY vhosts/default-ssl.conf /etc/apache2/sites-enabled/
COPY config/apache2.conf /etc/apache2/

RUN a2enmod rewrite
RUN a2enmod ssl

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
   mv composer.phar /usr/local/bin/composer

RUN usermod -u 1000 www-data 
RUN chown www-data:1000  /usr/local/bin/composer -R
RUN chown www-data:1000 . -R

WORKDIR /var/www/html/
