# adapted from https://docs.docker.com/guides/laravel/
FROM php:8.5-fpm AS builder

# set up build-time dependencies
RUN apt-get update && apt-get install -y zip unzip
RUN docker-php-ext-install pdo_mysql
RUN apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

WORKDIR /var/www
COPY . /var/www

# install runtime dependencies
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist

FROM php:8.5-fpm AS production

# move system library files etc. from build stage
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --from=builder /usr/local/bin/docker-php-ext-* /usr/local/bin/

RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
COPY ./docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

COPY --from=builder /var/www /var/www

WORKDIR /var/www

RUN chown -R www-data:www-data /var/www
USER www-data

# use a separate entrypoint for automating migrations
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

EXPOSE 9000
CMD ["php-fpm"]
