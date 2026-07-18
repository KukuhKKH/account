ARG HUB
ARG STACK_NAME
ARG IMAGE_NAMESPACE
FROM ${HUB}/${STACK_NAME}/${IMAGE_NAMESPACE}-base:latest AS base_image

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs

FROM node:24-alpine AS frontend
WORKDIR /app
RUN npm install -g pnpm
COPY package.json pnpm-lock.yaml ./
RUN pnpm install --frozen-lockfile
COPY . .
RUN pnpm run build

FROM base_image
WORKDIR /app

# Copy dependencies
COPY --from=vendor /app/vendor/ /app/vendor/
COPY --from=frontend /app/public/build/ /app/public/build/

# Copy application code
COPY . .

# Adjust permissions for Laravel
RUN chown -R www-data:www-data /app \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Remove local .env if it exists
RUN rm -f .env
