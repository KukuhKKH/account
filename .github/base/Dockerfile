FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql opcache \
    && rm -rf /var/cache/apk/*

RUN echo 'server { \
    listen 80; \
    server_name _; \
    root /app/public; \
    index index.php index.html; \
    location / { \
        try_files $$uri $$uri/ /index.php?$$query_string; \
    } \
    location ~ \.php$$ { \
        include fastcgi_params; \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_param SCRIPT_FILENAME $$document_root$$fastcgi_script_name; \
    } \
}' > /etc/nginx/http.d/default.conf

RUN echo '[supervisord] \
nodaemon=true \
logfile=/dev/null \
logfile_maxbytes=0 \
[program:php-fpm] \
command=php-fpm -F \
stdout_logfile=/dev/stdout \
stdout_logfile_maxbytes=0 \
stderr_logfile=/dev/stderr \
stderr_logfile_maxbytes=0 \
[program:nginx] \
command=nginx -g "daemon off;" \
stdout_logfile=/dev/stdout \
stdout_logfile_maxbytes=0 \
stderr_logfile=/dev/stderr \
stderr_logfile_maxbytes=0' > /etc/supervisord.conf

WORKDIR /app

EXPOSE 80

CMD ["supervisord", "-c", "/etc/supervisord.conf"]
