FROM php:5.6-apache

MAINTAINER miroslav kubelik <koubel@seznam.cz>

RUN apt-get -y update

RUN apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev

RUN docker-php-ext-configure gd --with-jpeg-dir=/usr/include/ \
        --with-png-dir=/usr/include --with-freetype-dir=/usr/include/ \
    && docker-php-ext-install gd \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install zip

ADD site.apache.conf /etc/apache2/sites-available/dbm.mirin.dkl.conf
RUN a2ensite dbm.mirin.dkl
RUN a2dissite 000-default

# setup phpMyAdmin
RUN curl -sS https://files.phpmyadmin.net/phpMyAdmin/4.6.4/phpMyAdmin-4.6.4-english.tar.gz \
        | tar xz -C /var/www \
    && mv /var/www/phpMyAdmin-4.6.4-english /var/www/pma
ADD config.inc.php /var/www/pma/config.inc.php

# setup adminer
RUN mkdir /var/www/adminer
RUN curl https://www.adminer.org/static/download/4.2.5/adminer-4.2.5-mysql.php \
    > /var/www/adminer/adminer.php

# workaround for the shared folders in virtualbox guest
# see https://github.com/docker-library/mysql/issues/99
# GID - 999 is determined in the guest vm by $ getent group vboxsf
RUN groupmod -o -g 999 www-data; chown -R root.www-data /var/www

# for vim modeline: (do not edit)
# vim: set ts=4 sw=4 et:
