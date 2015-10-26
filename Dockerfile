FROM richarvey/nginx-php-fpm
RUN rm /usr/share/nginx/html/*
COPY . /usr/share/nginx/html/
