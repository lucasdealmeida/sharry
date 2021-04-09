FROM php:8-apache
RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libicu-dev \
    zip \
    unzip

RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl pdo_mysql

RUN sed -i 's/var\/www\/html/var\/www\/html\/public/g' /etc/apache2/sites-enabled/000-default.conf
RUN a2enmod rewrite

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer