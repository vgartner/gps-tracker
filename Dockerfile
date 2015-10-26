FROM richarvey/nginx-php-fpm
RUN mkdir /usr/share/nginx/temp/
COPY . /usr/share/nginx/temp/
RUN cp /usr/share/nginx/temp/ /usr/share/nginx/html/
RUN rm -rf /usr/share/nginx/temp
