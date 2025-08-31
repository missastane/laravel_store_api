# laravel_api_store installation

1. Download the project
2. Run `cp .env.example .env` command
3. Run `composer install` command
4. Run `php artisan key:generate` command
5. Create a database and set .env file
6. Create new clientId from `OAuth 2.0 Client IDs` from Google Credentials and fill `google oath2` part in env
7. Fill mail info in env file
8. also you must have google recaptcha Id to fill that part in env file
9. Run `php artisan migrate` command
10. Run `php artisan serve` command and run the project from url address

Thank You