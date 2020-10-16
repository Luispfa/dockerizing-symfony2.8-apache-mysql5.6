FROM php:5.6-apache

RUN apt-get update && \
    apt-get install -y net-tools git acl zip unzip

RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install sockets

COPY php.ini /etc/php/5.6/php.ini
COPY certificate/wildcard.juntadeaccionistas.es.crt /etc/apache2/ssl/wildcard.juntadeaccionistas.es.crt
COPY certificate/ca.bundle.openprovider.esentialssl.wildcard /etc/apache2/ssl/ca.bundle.openprovider.esentialssl.wildcard
COPY certificate/wildcard.juntadeaccionistas.es.key /etc/apache2/ssl/wildcard.juntadeaccionistas.es.key

COPY vhosts/000-default.conf /etc/apache2/sites-enabled/
COPY vhosts/default-ssl.conf /etc/apache2/sites-enabled/
COPY vhosts/apache2.conf /etc/apache2/

RUN a2enmod rewrite
RUN a2enmod ssl

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
   mv composer.phar /usr/local/bin/composer

WORKDIR /var/www/html/