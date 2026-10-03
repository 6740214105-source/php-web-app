FROM php:8.2-fpm-alpine
RUN apk add --no-cache nginx && docker-php-ext-install mysqli && echo "clear_env = no" >> /usr/local/etc/php-fpm.d/www.conf
COPY index.php /var/www/html/index.php
COPY default.conf.template /etc/nginx/default.conf.template
COPY start.sh /start.sh
RUN chmod +x /start.sh
CMD ["/start.sh"]
