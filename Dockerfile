FROM php:7.4-fpm

# FastAdmin 常用扩展（数据库 + 验证码GD）
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       libfreetype6-dev \
       libjpeg62-turbo-dev \
       libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli pdo pdo_mysql gd \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html/kaoqin

# 仅复制业务代码目录，避免把无关文件打进镜像
COPY kaoqin/ /var/www/html/kaoqin/

RUN chown -R www-data:www-data /var/www/html/kaoqin

EXPOSE 9000
CMD ["php-fpm"]