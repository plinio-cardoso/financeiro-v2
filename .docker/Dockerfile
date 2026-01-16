FROM php:8.3.2-fpm

WORKDIR /var/www/html

RUN apt-get update
RUN apt-get install git -y
RUN apt-get install zip -y

# Extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Install Composer CLI
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug
