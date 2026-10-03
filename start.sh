#!/bin/sh
PORT="${PORT:-80}"
sed "s/__PORT__/${PORT}/" /etc/nginx/default.conf.template > /etc/nginx/http.d/default.conf
php-fpm -D
exec nginx -g 'daemon off;'
