FROM php:7.4

RUN apt-get update && apt-get install -y \
 git zip unzip zlib1g-dev libzip-dev libonig-dev && apt-get clean && apt-get autoclean

RUN apt-get update && \
    apt-get install -y \
      zlib1g-dev

RUN pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.idekey=docker" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_connect_back=on" >> /usr/local/etc/php/conf.d/xdebug.ini

RUN docker-php-ext-enable xdebug

RUN pecl install redis && docker-php-ext-enable redis

RUN docker-php-ext-install zip && docker-php-ext-enable zip
RUN docker-php-ext-install mbstring && docker-php-ext-enable mbstring
RUN docker-php-ext-install pdo_mysql && docker-php-ext-enable pdo_mysql

RUN mkdir -p /var/www/bin

WORKDIR /var/www

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '795f976fe0ebd8b75f26a6dd68f78fd3453ce79f32ecb33e7fd087d39bfeb978342fb73ac986cd4f54edd0dc902601dc') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN php -r "unlink('composer-setup.php');"

COPY . /var/www/

RUN COMPOSER_ALLOW_SUPERUSER=1 php /usr/local/bin/composer install