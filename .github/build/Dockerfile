ARG HUB
ARG STACK_NAME
ARG IMAGE_NAMESPACE
FROM ${HUB}/${STACK_NAME}/${IMAGE_NAMESPACE}-base:latest AS base_image

FROM base_image AS builder
WORKDIR /app

# Install composer and nodejs for building
RUN apk add --no-cache nodejs npm composer && npm install -g pnpm@9

# Build PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs --no-scripts

# Copy application code (needed for Vite plugins that run artisan)
COPY . .

# Build Node dependencies & assets
RUN pnpm install --frozen-lockfile
RUN pnpm run build

FROM base_image
WORKDIR /app

# Copy dependencies and compiled assets
COPY --from=builder /app/vendor/ /app/vendor/
COPY --from=builder /app/public/build/ /app/public/build/

# Copy application code
COPY . .

# Adjust permissions for Laravel
RUN chown -R www-data:www-data /app \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Remove local .env if it exists
RUN rm -f .env

RUN php artisan package:discover --ansi
