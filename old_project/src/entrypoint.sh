#!/bin/sh
#ping -t localhost
service mariadb start && php artisan serve --host 0.0.0.0 --port 8000