ARG HUB
ARG STACK_NAME
ARG IMAGE_NAMESPACE

# Stage 1: Vendor Dependencies
FROM composer:2 AS vendor
WORKDIR /app
COPY backend/composer.json backend/composer.lock* ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Stage 2: Final Hypervel Production Image
FROM ${HUB}/${STACK_NAME}/base-${IMAGE_NAMESPACE}-backend:latest

WORKDIR /app

COPY backend/ .
COPY --from=vendor /app/vendor/ ./vendor/

EXPOSE 9501
CMD ["php", "artisan", "serve"]
