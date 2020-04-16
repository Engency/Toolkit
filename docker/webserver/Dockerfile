FROM php:7.4.0-apache

WORKDIR /var/www/html
ENV PATH /var/www/html:$PATH

RUN echo "nameserver 8.8.8.8" >> /etc/resolv.conf

# Apache configuration
RUN a2enmod rewrite
COPY virtualserver.conf /etc/apache2/sites-available/000-default.conf

RUN apt-get update \
        && apt-get install -y openssh-server ssh zlib1g-dev libzip-dev unzip sqlite3 libsqlite3-dev git \
        zlib1g-dev libzip-dev libssl-dev libmcrypt-dev --no-install-recommends \
        && docker-php-ext-install pdo pdo_mysql pdo_sqlite zip

RUN  echo "    IdentityFile ~/.ssh/id_rsa" >> /etc/ssh/ssh_configs

# install APCu
RUN pecl install apcu && echo "extension=apcu.so" > /usr/local/etc/php/conf.d/apcu.ini

# install gd
RUN buildDeps=" \
		libfreetype6-dev \
		libjpeg-dev \
        libwebp-dev \
        libjpeg62-turbo-dev \
        libxpm-dev \
		libpng-dev \
	"; \
	set -x \
	&& apt-get update && apt-get install -y $buildDeps --no-install-recommends \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# install tidy
RUN apt install -y zlib1g-dev libzip-dev libtidy-dev && docker-php-ext-install tidy && docker-php-ext-enable tidy

####################################
#                                  #
#            Composer              #
#                                  #
####################################

ENV COMPOSER_HOME /tmp
ENV COMPOSER_VERSION 1.10.5

RUN curl --silent --fail --location --retry 3 --output /tmp/installer.php --url https://raw.githubusercontent.com/composer/getcomposer.org/a5874d7ceecca18772d44ed19e7da5fd267ba0a4/web/installer \
     && php -r " \
        \$signature = 'e0012edf3e80b6978849f5eff0d4b4e4c79ff1609dd1e613307e16318854d24ae64f26d17af3ef0bf7cfb710ca74755a'; \
        \$hash = hash('sha384', file_get_contents('/tmp/installer.php')); \
        if (!hash_equals(\$signature, \$hash)) { \
            unlink('/tmp/installer.php'); \
            echo 'Integrity check failed, installer is either corrupt or worse.' . PHP_EOL; \
            exit(1); \
        }" \
     && php /tmp/installer.php --no-ansi --install-dir=/usr/bin --filename=composer --version=${COMPOSER_VERSION} \
     && composer --ansi --version --no-interaction \
     && rm -f /tmp/installer.php

####################################
#                                  #
#            Timezone              #
#                                  #
####################################

ARG TZ="Europe/Amsterdam"
RUN ln -sfn /usr/share/zoneinfo/${TZ} /etc/localtime && echo ${TZ} > /etc/timezone
RUN echo "date.timezone=$TZ" > /usr/local/etc/php/conf.d/datetime.ini

####################################
#                                  #
#            opcache               #
#                                  #
####################################

COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini

ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="0" \
    PHP_OPCACHE_MAX_ACCELERATED_FILES="32531" \
    PHP_OPCACHE_MEMORY_CONSUMPTION="256" \
    PHP_OPCACHE_MAX_WASTED_PERCENTAGE="10"

RUN docker-php-ext-install opcache

COPY virtualserver.conf /etc/apache2/sites-available/000-default.conf

CMD ["/usr/sbin/apache2ctl", "-e", "info", "-D", "FOREGROUND"]
