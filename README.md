# Advanced Logger

Advanced Logger is a Laravel package that automatically logs every HTTP request made to your Laravel application. Each request is assigned a unique hash, which can also be included in your standard logs to easily match log entries to a specific request.

This package is inspired by https://github.com/andersao/laravel-request-logger by Anderson Andrade.

## Installation

### Composer

Run `composer require dejwcake/advanced-logger` in your terminal.

### Laravel

This package is for Laravel 10, 11 or 12.

To publish config file, run

```shell
php artisan vendor:publish --provider="Brackets\AdvancedLogger\AdvancedLoggerServiceProvider"
```

## Configuration

All options are described in `config/advanced-logger.php`.

## Using request hash in standard log file

If you would like to include the request identifier in your standard log so you can match log events to a specific request, add the following to `config/logging.php`:

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

This log modifier can also be applied to other logging channels. However, it relies on an extended `LineFormatter`, so make sure the target channel is compatible with that formatter.

## How to develop this project

### Composer

Update dependencies:
```shell
docker compose run -it --rm test composer update
```

Composer normalization:
```shell
docker compose run -it --rm php-qa composer normalize
```

### Run tests

Run tests with pcov:
```shell
docker compose run -it --rm test ./vendor/bin/phpunit -d pcov.enabled=1
```

To regenerate snapshots use:
```shell
docker compose run -it --rm test ./vendor/bin/phpunit -d pcov.enabled=1 -d --update-snapshots
```

To switch between postgresql and mariadb change in `docker-compose.yml` DB_CONNECTION environmental variable:
```git
- DB_CONNECTION: pgsql
+ DB_CONNECTION: mysql
```

### Run code analysis tools (php-qa)

PHP compatibility:
```shell
docker compose run -it --rm php-qa phpcs --standard=.phpcs.compatibility.xml --cache=.phpcs.cache
```

Code style:
```shell
docker compose run -it --rm php-qa phpcs -s --colors --extensions=php
```

Fix style issues:
```shell
docker compose run -it --rm php-qa phpcbf -s --colors --extensions=php
```

Static analysis (phpstan):
```shell
docker compose run -it --rm php-qa phpstan analyse --configuration=phpstan.neon
```

Mess detector (phpmd):
```shell
docker compose run -it --rm php-qa phpmd ./src,./config,./tests ansi phpmd.xml --suffixes php --baseline-file phpmd.baseline.xml
```