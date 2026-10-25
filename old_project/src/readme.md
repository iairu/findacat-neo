<h1 align="center">🐈 Find A Cat</h1>

## About
Find A Cat is a web application based on Laravel to help people find and manage cats, cat pedigrees and calculate inbreeding of cat pairs to determine if mating is a good idea. Please read my thesis to get a better idea about the functionality and intentions behind this application. My thesis includes a guide on how to setup an admin account by flagging it as such in the database after registration.

See for yourself: https://findacat.eu

## Features
This application uses English, Slovak and supports multiple languages.

## How to Install

### Server Requirements

This application can be installed on local server and online server with these specifications:

- Recommended setup requires just Docker
- Alternative setup requires PHP 8.1 (and meet other [Laravel 8.x server requirements](https://laravel.com/docs/8.x/deployment#server-requirements)) and a MySQL or MariaDB database

### Installation Steps

It is recommended to use **Docker**:
1. Ensure you have Docker installed on your machine.
2. Clone the repo: `git clone https://github.com/iairu/findacat.git`
3. `cd findacat`
4. `cp .env.example .env`
5. Build the Docker image:
   ```sh
   docker build -t findacat .
   ```
6. Run the Docker container:
   ```sh
   docker run -d -p 8000:8000 findacat
   ```
7. The application should now be accessible at `http://localhost:8000`

Without Docker:
1. Clone the repo : `git clone https://github.com/iairu/findacat.git`
2. `cd findacat`
3. `composer install`
4. `cp .env.example .env`
5. `php artisan key:generate`
6. Create **database on MySQL**:
   ```sql
   mariadb
   CREATE DATABASE findacat;
   GRANT ALL ON *.* TO 'findacat'@'localhost' IDENTIFIED BY 'secret' WITH GRANT OPTION;
   FLUSH PRIVILEGES;
   exit
   ```
7. **Set database credentials** in `.env` file
8. `php artisan migrate`
9. `php artisan storage:link`
10. `php artisan serve`

## License

Find A Cat project (this `src` folder) is based on Silsilah (https://github.com/nafiesl/silsilah) and open-sourced software licensed under the [MIT license](LICENSE) with the exception of my thesis (above `thesis` folder) and database demo files used to demonstrate import capabilities of the web interface (above `db_demo` folder).
