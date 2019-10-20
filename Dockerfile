#FROM kreait/php:7.1
FROM php
# Install prerequisites
RUN apt-get update && apt-get install -y \
curl

RUN sudo add-apt-repository ppa:ondrej/php
RUN sudo apt-get update
RUN apt install php7.1-bcmath

RUN apt-get install php7.0-bcmath

#RUN apt -qy install git unzip zlib1g-dev && \
   # docker-php-ext-install bcmath sockets pcntl zip
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer 

# Install dependencies
COPY composer.json composer.json
RUN composer install --prefer-dist --no-scripts --no-dev --no-autoloader && rm -rf /root/.composer

# Copy codebase
COPY . ./
# Finish composer
RUN composer dump-autoload --no-scripts --no-dev --optimize
ENTRYPOINT ["php","worker.php"]