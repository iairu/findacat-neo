FROM ubuntu:jammy
WORKDIR /app
COPY . /app
ENV DEBIAN_FRONTEND=noninteractive
RUN apt update
RUN apt-get --purge remove php-common
RUN apt install php-common php-mysql php-cli php-xml -y
RUN apt install mariadb-server -y
RUN apt install composer -y
RUN composer install
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
RUN chmod -R 775 storage bootstrap/cache
RUN cp -r /app/mariadb.service /etc/systemd/system
RUN service mariadb start && mariadb -u root -e "CREATE DATABASE homestead; GRANT ALL ON *.* TO 'homestead'@'localhost' IDENTIFIED BY 'secret' WITH GRANT OPTION; FLUSH PRIVILEGES;"
RUN php artisan key:generate
RUN service mariadb start && php artisan migrate
ENTRYPOINT ["sh", "entrypoint.sh"]