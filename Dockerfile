FROM php:7.4-fpm

# FastAdmin 常用数据库扩展
RUN docker-php-ext-install mysqli pdo pdo_mysql

WORKDIR /var/www/html/kaoqin

# 仅复制业务代码目录，避免把无关文件打进镜像
COPY kaoqin/ /var/www/html/kaoqin/

RUN chown -R www-data:www-data /var/www/html/kaoqin

EXPOSE 9000
CMD ["php-fpm"]