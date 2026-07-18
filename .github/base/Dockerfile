FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql opcache \
    && rm -rf /var/cache/apk/*

RUN printf "server {\n\
    listen 80;\n\
    server_name _;\n\
    root /app/public;\n\
    index index.php index.html;\n\
    location / {\n\
        try_files \$$uri \$$uri/ /index.php?\$$query_string;\n\
    }\n\
    location ~ \.php\$$ {\n\
        include fastcgi_params;\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_param SCRIPT_FILENAME \$$document_root\$$fastcgi_script_name;\n\
    }\n\
}\n" > /etc/nginx/http.d/default.conf

RUN printf "[supervisord]\n\
nodaemon=true\n\
logfile=/dev/null\n\
logfile_maxbytes=0\n\
\n\
[program:php-fpm]\n\
command=php-fpm -F\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n\
\n\
[program:nginx]\n\
command=nginx -g \"daemon off;\"\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n" > /etc/supervisord.conf

WORKDIR /app

EXPOSE 80

CMD ["supervisord", "-c", "/etc/supervisord.conf"]
