# Исходный образ обновлен до PHP 8.4
FROM php:8.4-fpm

# Установка системных зависимостей и модулей PHP
# Удален устаревший mcrypt, так как он не совместим с PHP 8.4 и не нужен для Symfony 8
RUN apt-get update && apt-get install -y \
        curl \
        wget \
        git \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libicu-dev \
        libxml2-dev \
        libpq-dev \
        libzip-dev \
    && docker-php-ext-install -j$(nproc) intl bcmath soap pgsql pdo_pgsql zip \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Добавим свой php.ini
ADD php.ini /usr/local/etc/php/conf.d/40-custom.ini

# Указываем рабочую директорию для PHP
WORKDIR /app

CMD ["php-fpm"]