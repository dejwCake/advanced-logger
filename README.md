# Advanced Logger

Advanced logger is a laravel package used to automatically log every request made to you laravel application. Each request is also identified by hash, which can be used in standard log to identify the request.

This package has been inspired by package https://github.com/andersao/laravel-request-logger from Anderson Andrade. 

## Installation

### Composer

Run `composer require dejwcake/advanced-logger` in your terminal.

### Laravel

This package is for Laravel 10 or 11.

To publish config file, run

```shell
php artisan vendor:publish --provider="Brackets\AdvancedLogger\AdvancedLoggerServiceProvider"
```

## Configuration

All options are described in `config/advanced-logger.php`.

## Using request hash in standard log file

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

## Composer

To develop this package, you need to have composer installed. To run composer command use:
```shell
  docker compose run -it --rm test composer update
```

For composer normalization:
```shell
  docker compose run -it --rm php-qa composer normalize
```

## Run tests

To run tests use this docker environment.
```shell
  docker compose run -it --rm test vendor/bin/phpunit -d pcov.enabled=1
```

To switch between postgresql and mariadb change in `docker-compose.yml` DB_CONNECTION environmental variable:
```git
- DB_CONNECTION: pgsql
+ DB_CONNECTION: mysql
```

## Run code analysis tools

To be sure, that your code is clean, you can run code analysis tools. To do this, run:

For php compatibility:
```shell
  docker compose run -it --rm php-qa phpcs --standard=.phpcs.compatibility.xml --cache=.phpcs.cache
```

For code style:
```shell
  docker compose run -it --rm php-qa phpcs -s --colors --extensions=php
```

or to fix issues:
```shell
  docker compose run -it --rm php-qa phpcbf -s --colors --extensions=php
```

For static analysis:
```shell
  docker compose run -it --rm php-qa phpstan analyse --configuration=phpstan.neon
```

For mess detector:
```shell
  docker compose run -it --rm php-qa phpmd ./src,./config,./tests ansi phpmd.xml --suffixes php --baseline-file phpmd.baseline.xml
```