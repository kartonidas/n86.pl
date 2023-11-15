## Install APP

- Install application [php composer.phar install]
- Copy .env files [cp .env.example .env]
- Set database connection, payment configuration in .env file
- Make storage dir readable [chmod -R 777 storage]
- Generate laravel APP KEY [php artisan key:generate]
- Run migration [php artisan migrate]
- Run and configure supervisor (https://laravel.com/docs/10.x/queues#supervisor-configuration)