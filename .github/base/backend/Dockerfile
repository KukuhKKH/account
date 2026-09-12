FROM hyperf/hyperf:8.3-alpine-v3.19-swoole-v6

RUN apk --no-cache add ca-certificates tzdata \
    && cp /usr/share/zoneinfo/Asia/Jakarta /etc/localtime \
    && echo "Asia/Jakarta" > /etc/timezone

ENV TZ=Asia/Jakarta

WORKDIR /app
