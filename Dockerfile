FROM php:8.2-apache
RUN docker-php-ext-install mysqli
RUN a2dismod mpm_event mpm_worker 2>/dev/null; a2enmod mpm_prefork
COPY index.php /var/www/html/index.php
COPY start.sh /start.sh
RUN chmod +x /start.sh
CMD ["/start.sh"]
