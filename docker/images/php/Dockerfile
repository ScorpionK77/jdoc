# Исходный образ обновлен до PHP 8.4
FROM php:8.4-fpm

# Установка системных зависимостей и базовых модулей
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
        librabbitmq-dev \
    && pecl install redis \
    && pecl install amqp \
    && docker-php-ext-install -j$(nproc) intl bcmath soap pgsql pdo_pgsql zip \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable redis amqp

# 🔥 ИДЕАЛЬНЫЙ СПОСОБ: Копируем готовый инструмент сборки из официального Docker-образа.
# Никаких curl, wget и проблем с кэшем GitHub!
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Устанавливаем ТОЛЬКО xdebug (остальные модули у вас уже собраны слоем выше)
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions xdebug

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Добавим свой php.ini
ADD php.ini /usr/local/etc/php/conf.d/40-custom.ini

# Указываем рабочую директорию для PHP
WORKDIR /app

CMD ["php-fpm"]
