FROM php:5.6-apache AS base

RUN sed -i s/deb.debian.org/archive.debian.org/g /etc/apt/sources.list
RUN sed -i s/security.debian.org/archive.debian.org/g /etc/apt/sources.list
RUN sed -i s/stretch-updates/stretch/g /etc/apt/sources.list

RUN apt update \
	&& apt-get install -y \
		git                                             `# composer` \
		zip                                             `# composer` \
		unzip                                           `# composer` \
		rsync                                           `# tests` \
		zlib1g-dev                                      `# memcached` \
		libmemcached-dev                                `# memcached` \
		libmcrypt-dev                                   `# mcrypt` \
		libfreetype6-dev                                `# gd` \
		libpng-dev                                      `# gd` \
		libjpeg-dev                                     `# gd` \
		libicu-dev                                      `# intl` \
		libxml2-dev                                     `# soap` \
	&& docker-php-ext-configure gd \
		--with-freetype-dir=/usr/include/ \
		--with-jpeg-dir=/usr/include/ \
		--with-png-dir=/usr/include/ \
	&& docker-php-ext-configure intl \
	&& docker-php-ext-install -j$(nproc) \
		pdo_mysql \
		gd \
		mcrypt \
		opcache \
		intl \
		soap \
		sockets \
    && pecl install \
		memcached-2.2.0 \
		# xdebug-2.7.0 \
    && docker-php-ext-enable \
		memcached \
		# xdebug \
	&& apt-get purge -y \
		zlib1g-dev \
		libmemcached-dev \
		libmcrypt-dev \
		libfreetype6-dev \
		libpng-dev \
		libjpeg-dev \
		libicu-dev \
		libxml2-dev \
	&& rm -rf /var/lib/apt/lists/*

COPY docker/php/php-site.ini /usr/local/etc/php/conf.d/local.ini
COPY docker/php/apache-site.conf /etc/apache2/sites-enabled/000-default.conf
COPY docker/php/apache-security.conf /etc/apache2/conf-available/security.conf
RUN a2enmod env info rewrite headers

WORKDIR /var/www

#### Composer Install - cache friendly way to pre-install dependencies
COPY --from=composer:1 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock /var/www/
RUN composer install --no-interaction --no-autoloader --no-scripts

COPY ./ /var/www/
RUN chown -R www-data:www-data ./tmp
RUN composer install --no-interaction

ENTRYPOINT ["docker/php/entrypoint.sh"]
CMD ["apache2-foreground"]
