FROM webdevops/php-nginx:7.2

# Install php-intl module
RUN apt-get -y update \
   && apt-get install -y libicu-dev\
   && docker-php-ext-configure intl \
   && docker-php-ext-install intl