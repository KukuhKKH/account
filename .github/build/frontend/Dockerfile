ARG HUB
ARG STACK_NAME
ARG IMAGE_NAMESPACE

# Stage 1: Build Nuxt SPA
FROM node:24-alpine AS builder
WORKDIR /app
RUN corepack enable && corepack prepare pnpm@latest --activate
COPY frontend/ ./
RUN pnpm install
RUN pnpm generate

# Stage 2: Final Image
FROM ${HUB}/${STACK_NAME}/base-${IMAGE_NAMESPACE}-frontend:latest

COPY --from=builder /app/.output/public /usr/share/nginx/html
COPY .github/base/frontend/files/etc/nginx/nginx.conf /etc/nginx/conf.d/nginx.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
