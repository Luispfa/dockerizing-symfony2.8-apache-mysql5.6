FROM php:5.6-apache

#RUN apt-get update \
#    && apt-get install -y --no-install-recommends vim curl debconf subversion git apt-transport-https apt-utils \
#    build-essential locales acl mailutils wget zip unzip \
#    gnupg gnupg1 gnupg2 
#
#COPY php.ini /etc/php/5.6/php.ini
#
#COPY certificate/wildcard.juntadeaccionistas.es.crt /etc/apache2/ssl/wildcard.juntadeaccionistas.es.crt
#COPY certificate/ca.bundle.openprovider.esentialssl.wildcard /etc/apache2/ssl/ca.bundle.openprovider.esentialssl.wildcard
#COPY certificate/wildcard.juntadeaccionistas.es.key /etc/apache2/ssl/wildcard.juntadeaccionistas.es.key
#RUN mkdir -p /var/run/apache2/
#RUN docker-php-ext-install pdo pdo_mysql mbstring
#RUN a2enmod rewrite
#RUN a2enmod ssl
##RUN service apache2 restart
#
##RUN docker-php-ext-install pdo pdo_mysql
#
#RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
#   mv composer.phar /usr/local/bin/composer
#
#RUN groupadd dev -g 999
#RUN useradd dev -g dev -d /home/dev -m
#
#WORKDIR /var/www/html/

##RUN requirements="nano cron mysql-client-5.5 libpng12-dev libmcrypt-dev libmcrypt4 libcurl3-dev libxml2-dev libfreetype6 libjpeg62-turbo libpng12-dev libfreetype6-dev libjpeg62-turbo-dev" \
#RUN requirements="nano cron libmcrypt-dev libmcrypt4 libcurl3-dev libxml2-dev libfreetype6 libjpeg62-turbo libfreetype6-dev libjpeg62-turbo-dev git" \
#    && apt-get update && apt-get install -y --no-install-recommends $requirements && rm -rf /var/lib/apt/lists/* \
#    && docker-php-ext-install pdo pdo_mysql \
#    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
#    && docker-php-ext-install gd \
#    && docker-php-ext-install mcrypt \
#    && docker-php-ext-install mbstring \
#    && docker-php-ext-install soap \
#    && docker-php-ext-install mysqli \
#    && requirementsToRemove="libmcrypt-dev libcurl3-dev libxml2-dev libfreetype6-dev libjpeg62-turbo-dev" \
#    && apt-get purge --auto-remove -y $requirementsToRemove
#
##RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/ssl-cert-snakeoil.key -out /etc/ssl/certs/ssl-cert-snakeoil.pem -subj "/C=AT/ST=Vienna/L=Vienna/O=Security/OU=Development/CN=example.com"
#
#COPY php.ini /etc/php/5.6/php.ini
#
#COPY certificate/wildcard.juntadeaccionistas.es.crt /etc/apache2/ssl/wildcard.juntadeaccionistas.es.crt
#COPY certificate/ca.bundle.openprovider.esentialssl.wildcard /etc/apache2/ssl/ca.bundle.openprovider.esentialssl.wildcard
#COPY certificate/wildcard.juntadeaccionistas.es.key /etc/apache2/ssl/wildcard.juntadeaccionistas.es.key
#
#RUN a2enmod rewrite
#RUN a2ensite default-ssl
#RUN a2enmod ssl
#
#RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
#   mv composer.phar /usr/local/bin/composer
#
#RUN groupadd dev -g 999
#RUN useradd dev -g dev -d /home/dev -m
#
#WORKDIR /var/www/html/


#RUN groupadd -r apache && adduser apache
USER apache


RUN apt-get update
RUN apt-get install -y net-tools git acl
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install sockets

COPY php.ini /etc/php/5.6/php.ini
COPY certificate/wildcard.juntadeaccionistas.es.crt /etc/apache2/ssl/wildcard.juntadeaccionistas.es.crt
COPY certificate/ca.bundle.openprovider.esentialssl.wildcard /etc/apache2/ssl/ca.bundle.openprovider.esentialssl.wildcard
COPY certificate/wildcard.juntadeaccionistas.es.key /etc/apache2/ssl/wildcard.juntadeaccionistas.es.key

RUN a2enmod rewrite
RUN a2enmod ssl

COPY vhosts/000-default.conf /etc/apache2/sites-enabled/
COPY vhosts/default-ssl.conf /etc/apache2/sites-enabled/
COPY vhosts/apache2.conf /etc/apache2/
#ADD www-server/ www-server/

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
   mv composer.phar /usr/local/bin/composer

#RUN groupadd dev -g 999
#RUN useradd dev -g dev -d /home/dev -m
#
#RUN setfacl -R -m u:www-data:rwx -m u:`whoami`:rwx app/cache app/logs
#RUN setfacl -dR -m u:www-data:rwx -m u:`whoami`:rwx app/cache app/cache

WORKDIR /var/www/html/