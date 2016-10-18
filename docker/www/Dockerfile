# base image for most pilulka.cz web based projects
FROM php:7.0-apache

MAINTAINER miroslav kubelik <koubel@seznam.cz>

RUN apt-get -y update

RUN apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    libedit-dev \
    libicu-dev \
    libssl-dev

RUN docker-php-ext-configure gd --with-jpeg-dir=/usr/include/ \
        --with-png-dir=/usr/include --with-freetype-dir=/usr/include/ \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install intl \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install zip \
    && docker-php-ext-install calendar \
    && docker-php-ext-install xmlrpc \
    && docker-php-ext-install sysvsem

RUN pecl install redis\
    && docker-php-ext-enable redis

# setup Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

ADD site.apache.conf /etc/apache2/sites-available/www.mirin.dkl.conf
RUN a2ensite www.mirin.dkl
RUN a2enmod rewrite

# correct timezone
ENV TZ=Europe/Prague
RUN echo $TZ | tee /etc/timezone
RUN dpkg-reconfigure --frontend noninteractive tzdata

# workaround for the shared folders in virtualbox guest
# see https://github.com/docker-library/mysql/issues/99
# GID - 999 is determined in the guest vm by $ getent group vboxsf
RUN groupmod -o -g 999 www-data

COPY php.ini /usr/local/etc/php
WORKDIR /var/www/mirin_cz

# for vim modeline: (do not edit)
# vim: set ts=4 sw=4 et:
