FROM php:8.3-apache
RUN docker-php-ext-install pdo_mysql mysqli
RUN a2enmod rewrite
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"