FROM php:7.4.0-apache

WORKDIR /var/www/html
ENV PATH /var/www/html:$PATH

RUN echo "nameserver 8.8.8.8" >> /etc/resolv.conf

RUN apt-get update && apt-get install -y openssh-server ssh libpcre3-dev zlib1g-dev libzip-dev unzip sqlite3 libsqlite3-dev git --no-install-recommends
RUN  echo "    IdentityFile ~/.ssh/id_rsa" >> /etc/ssh/ssh_config

RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite opcache zip
RUN a2enmod rewrite

# install APCu
RUN pecl install apcu && echo "extension=apcu.so" > /usr/local/etc/php/conf.d/apcu.ini

# install gd
RUN buildDeps=" \
        mariadb-client \
		libfreetype6-dev \
		libjpeg-dev \
        libwebp-dev \
        libjpeg62-turbo-dev \
        libxpm-dev \
		libldap2-dev \
		libmcrypt-dev \
		libpng-dev \
		zlib1g-dev \
		libzip-dev \
        libtidy-dev \
        libssl-dev \
	"; \
	set -x \
	&& apt-get update && apt-get install -y $buildDeps --no-install-recommends \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
	&& docker-php-ext-install tidy \
    && docker-php-ext-enable tidy

####################################
#                                  #
#            Composer              #
#                                  #
####################################

ENV COMPOSER_HOME /tmp
ENV COMPOSER_VERSION 1.9.3

RUN curl --silent --fail --location --retry 3 --output /tmp/installer.php --url https://raw.githubusercontent.com/composer/getcomposer.org/cb19f2aa3aeaa2006c0cd69a7ef011eb31463067/web/installer \
     && php -r " \
        \$signature = '48e3236262b34d30969dca3c37281b3b4bbe3221bda826ac6a9a62d6444cdb0dcd0615698a5cbe587c3f0fe57a54d8f5'; \
        \$hash = hash('sha384', file_get_contents('/tmp/installer.php')); \
        if (!hash_equals(\$signature, \$hash)) { \
            unlink('/tmp/installer.php'); \
            echo 'Integrity check failed, installer is either corrupt or worse.' . PHP_EOL; \
            exit(1); \
        }" \
     && php /tmp/installer.php --no-ansi --install-dir=/usr/bin --filename=composer --version=${COMPOSER_VERSION} \
     && composer --ansi --version --no-interaction \
     && rm -f /tmp/installer.php

CMD ["scripts/startup.sh"]
