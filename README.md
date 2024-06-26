# Advanced Logger

Advanced logger is a laravel package used to automatically log every request made to you laravel application. Each request is also identified by hash, which can be used in standard log to identify the request.

This package has been inspired by package https://github.com/andersao/laravel-request-logger from Anderson Andrade. 

## Installation

### Composer

Run `composer require brackets/advanced-logger` in your terminal.

### Laravel

This package is for Laravel 5.5, 5.6 and 5.7, so it has auto discovery.

To publish config file, run

```shell
php artisan vendor:publish --provider="Brackets\AdvancedLogger\AdvancedLoggerServiceProvider"
```

## Configuration

All options are described in `config/advanced-logger.php`.

## Using request hash in standard log file in Laravel 5.7

If you would like to have request identifier in you standard log, to match log events with request you could add to `config/logging.php`

```php
'tap' => [Brackets\AdvancedLogger\LogCustomizers\HashLogCustomizer::class],
```

to `daily` channel. The resulted code should looks like

```php
    'channels' => [
        ...

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'days' => 14,
            'tap' => [Brackets\AdvancedLogger\LogCustomizers\HashLogCustomizer::class],
        ],

        ...
    ],
```

This log modifier can be used also in other channels, however it uses extended `LineFormatter`.

## Run tests

To run tests use this docker environment.

```shell
  docker-compose run -it test vendor/bin/phpunit
```

To switch between postgresql and mariadb change in `docker-compose.yml` DB_CONNECTION environmental variable:

```git
- DB_CONNECTION: pgsql
+ DB_CONNECTION: mysql
```
