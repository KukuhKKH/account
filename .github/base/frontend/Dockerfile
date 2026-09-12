FROM nginx:alpine

RUN apk --no-cache add tzdata \
    && cp /usr/share/zoneinfo/Asia/Jakarta /etc/localtime \
    && echo "Asia/Jakarta" > /etc/timezone

ENV TZ=Asia/Jakarta

RUN rm /etc/nginx/conf.d/default.conf

COPY ./files/etc/nginx/nginx.conf /etc/nginx/conf.d
