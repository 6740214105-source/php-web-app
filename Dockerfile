FROM php:8.2-apache
RUN docker-php-ext-install mysqli
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && a2enmod mpm_prefork
COPY index.php /var/www/html/index.php
COPY start.sh /start.sh
RUN chmod +x /start.sh
CMD ["/start.sh"]
