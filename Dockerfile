FROM php:8.2-apache
RUN docker-php-ext-install mysqli
COPY index.php /var/www/html/index.php
COPY start.sh /start.sh
RUN chmod +x /start.sh
CMD ["/start.sh"]
