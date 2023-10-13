FROM php:8.2-apache
USER root
COPY install_composer.sh /usr/local/bin
RUN chmod 775 /usr/local/bin/install_composer.sh
COPY _docker/app/build_artifacts/localhost.conf /etc/apache2/sites-available/localhost.conf
COPY _docker/app/build_artifacts/php.ini /usr/local/etc/php/php.ini
#RUN cat /etc/httpd/conf/httpd.conf >> /etc/apache2/apache2.conf
RUN cat /etc/apache2/sites-available/localhost.conf > /etc/apache2/sites-enabled/000-default.conf
RUN ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/rewrite.load
RUN a2enmod headers
RUN service apache2 restart

RUN apt-get update
RUN apt-get install -y libcurl4-openssl-dev pkg-config libssl-dev
RUN dpkg-reconfigure libcurl4-openssl-dev
RUN apt-get install -y libxml2-dev zip
RUN apt-get install -y mariadb-client && rm -rf /var/lib/apt
RUN docker-php-ext-install pdo pdo_mysql
RUN pecl install mongodb

RUN apt-get update && apt-get install -y wget
RUN ls /usr/local/lib/php/extensions/

RUN /usr/local/bin/install_composer.sh
RUN mkdir /var/log/pvapi
RUN chown -R www-data:www-data /var/log/pvapi
USER www-data
ENV CONFIG_PATH=/var/www/html/api
RUN mkdir -p /var/www/html/api/querymodule/
COPY querymodule /var/www/html/api/querymodule/
COPY app.ini /var/www/html/api/