FROM php:8.2-apache

ENV PROJECT_PATH /var/www/html/
ENV APACHE_DOCUMENT_ROOT ${PROJECT_PATH}/public

COPY ./ ${PROJECT_PATH}/

WORKDIR ${PROJECT_PATH}/
RUN rm -f composer.lock
RUN rm -f composer.phar

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions ctype dom filter iconv json libxml pdo mbstring phar tokenizer xml xmlwriter pdo_mysql zip gd

#download composer.phar
RUN php -r "copy('https://getcomposer.org/download/2.8.8/composer.phar', 'composer.phar');"

RUN rm -rf var/
RUN mkdir ${PROJECT_PATH}/var

RUN php composer.phar install --prefer-dist

# USER root
RUN a2enmod rewrite
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
