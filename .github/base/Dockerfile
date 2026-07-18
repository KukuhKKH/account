FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql opcache \
    && rm -rf /var/cache/apk/*

COPY .github/base/nginx.conf /etc/nginx/http.d/default.conf
COPY .github/base/supervisord.conf /etc/supervisord.conf

WORKDIR /app

EXPOSE 80

CMD ["supervisord", "-c", "/etc/supervisord.conf"]
