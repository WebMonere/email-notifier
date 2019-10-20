#FROM kreait/php:7.1
FROM php:7.1
# Install prerequisites
RUN apt-get update && apt-get install -y \
curl
RUN apt install php-bcmath
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer 

# Install dependencies
COPY composer.json composer.json
RUN composer install --prefer-dist --no-scripts --no-dev --no-autoloader && rm -rf /root/.composer

# Copy codebase
COPY . ./
# Finish composer
RUN composer dump-autoload --no-scripts --no-dev --optimize
ENTRYPOINT ["php","worker.php"]