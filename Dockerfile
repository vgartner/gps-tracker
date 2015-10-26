FROM richarvey/nginx-php-fpm
CMD rm -rf /usr/share/nginx/html
ADD . /usr/share/nginx/html
