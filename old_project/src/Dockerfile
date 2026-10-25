FROM ubuntu:jammy
WORKDIR /app
COPY . /app
ENV DEBIAN_FRONTEND=noninteractive
RUN apt update
RUN apt-get --purge remove php-common
RUN apt install php-common php-mysql php-cli -y
RUN apt install mariadb-server -y
RUN cp -r /app/mariadb.service /etc/systemd/system
RUN service mariadb start && mariadb -u root -e "CREATE DATABASE homestead; GRANT ALL ON *.* TO 'homestead'@'localhost' IDENTIFIED BY 'secret' WITH GRANT OPTION; FLUSH PRIVILEGES;"
RUN mkdir -p /usr/lib/php/20210902
# RUN cp -r pdo_mysql.so /usr/lib/php/20210902
RUN cp -r php.ini /etc/php/8.1/cli
RUN php artisan key:generate
RUN service mariadb start && php artisan migrate
ENTRYPOINT ["sh", "entrypoint.sh"]