FROM nginx:1.19.0-alpine

ADD ./docker/nginx/config/default.conf /etc/nginx/conf.d/default.conf
ADD ./docker/nginx/config/nginx.conf /etc/nginx/nginx.conf

RUN mkdir -p /var/www/html/public
ADD ./public/ /var/www/html/public
