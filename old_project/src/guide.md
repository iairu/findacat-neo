# DB initialization
config/app.php -> DEBUG true
cp .env.example .env
apt update
apt install mariadb-server
mysql_secure_installation
mariadb
CREATE DATABASE homestead;
GRANT ALL ON *.* TO 'homestead'@'localhost' IDENTIFIED BY 'secret' WITH GRANT OPTION;
FLUSH PRIVILEGES;
exit

# PHP adjustments
sudo vi /etc/php/8.1/cli/php.ini 
upload_max_filesize = 1024MB
post_max_size = 1024MB
memory_limit = -1
max_execution_time = 3650
max_input_time = 3650

# Laravel setup procedure
php artisan key:generate
php artisan migrate
php artisan serve

# Laravel reset procedure
As CSV integrity can't be guaranteed:
- Create a backup using GZ first with valid integrity
- Use CSV import feature
- If integrity breaks restore given GZ backup

Alternative:
php artisan migrate:reset
php artisan migrate
php artisan serve
