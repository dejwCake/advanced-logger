# Upgrade Guide from v1 to v2

## Requirements

| Dependency | v1          | v2       |
|------------|-------------|----------|
| PHP        | ^8.2        | ^8.5     |
| Laravel    | ^10.0–^12.0 | ^12.0    |
| PHPUnit    | ^11.5       | ^13.0    |

The `ext-json` requirement has been removed (bundled in PHP since 8.0).

## Breaking changes

### All classes are now `final`

All source classes are declared `final`. If you were extending any of them, you will need to use composition instead.

**Affected classes:** `RequestLogger`, `RequestLoggerService`, `RequestLoggerHandler`, `RequestLoggerListenerHandler`, `RequestLogJob`, `RequestInterpolation`, `ResponseInterpolation`, `LineWithHashFormatter`, `HashLogCustomizer`, `Benchmark`.

### Removed classes

- `Brackets\AdvancedLogger\Providers\EventServiceProvider` — event listener registration moved directly into `AdvancedLoggerServiceProvider::boot()`
- `Brackets\AdvancedLogger\Listeners\RequestLoggerListener` — subscriber wrapper removed, `RequestLoggerListenerHandler` is registered directly

If you were referencing these classes, remove the references. No action needed if you only use the package through its config.

### Removed methods from `RequestLogger`

The following legacy methods have been removed:

- `RequestLogger::useFiles()`
- `RequestLogger::useDailyFiles()`

These were no-op stubs from early Laravel versions and had no effect.

### Constructor signature changes

All classes now use dependency injection instead of helpers/Facades. If you are manually instantiating any class, update the constructor arguments:

**`RequestLogger`** — now requires `Repository`, `Container`, `LogManager`:
```php
// v1
$logger = new RequestLogger();

// v2 — use the container
$logger = app(RequestLogger::class);
```

**`RequestLoggerHandler`** — now requires `Repository`, `Application` as first two parameters:
```php
// v1
new RequestLoggerHandler($filename, $maxFiles, $level, ...);

// v2
new RequestLoggerHandler($config, $app, $filename, $maxFiles, $level, ...);
```

**`RequestLoggerListenerHandler`** — now requires `Repository`, `Container`, `BusDispatcher`:
```php
// v1
new RequestLoggerListenerHandler();

// v2 — use the container
$handler = app(RequestLoggerListenerHandler::class);
```

**`ResponseInterpolation`** — now requires `Repository`, `Application`:
```php
// v1
new ResponseInterpolation();

// v2 — use the container
$interpolation = app(ResponseInterpolation::class);
```

**`LineWithHashFormatter`** — now requires `Repository` as first parameter:
```php
// v1
new LineWithHashFormatter($format, $dateFormat, ...);

// v2
new LineWithHashFormatter($config, $format, $dateFormat, ...);
```

**`HashLogCustomizer`** — now requires `Container`:
```php
// v1
new HashLogCustomizer();

// v2 — use the container
$customizer = app(HashLogCustomizer::class);
```

**`RequestLogJob::handle()`** — now requires `RequestLoggerService` via method injection:
```php
// v1
$job->handle(); // used app() internally

// v2
$job->handle($requestLoggerService); // injected by the container when dispatched
```

**`RequestLoggerService`** — now requires `Repository` as first parameter:
```php
// v1
new RequestLoggerService($logger, $requestInterpolation, $responseInterpolation);

// v2
new RequestLoggerService($config, $logger, $requestInterpolation, $responseInterpolation);
```

### Visibility changes

All `protected` methods and properties have been changed to `private` (since all classes are `final`). If you were accessing protected members via reflection or inheritance, this will break.

### Constants

- `RequestLoggerService::LOG_CONTEXT` changed from `protected const` to `private const string`
- `RequestLoggerService::$formats` property changed to `private const array FORMATS`
- `LineWithHashFormatter::KEY` changed from `public const` to `public const string`

## Migration steps

1. Update `composer.json`:
   ```json
   "require": {
       "dejwcake/advanced-logger": "^2.0"
   }
   ```

2. Run `composer update dejwcake/advanced-logger`

3. If you published the config file, no changes are needed — the config format is unchanged.

4. If you referenced any removed classes (`EventServiceProvider`, `RequestLoggerListener`), remove those references.

5. If you manually instantiated any package class, switch to resolving from the container:
   ```php
   $logger = app(RequestLogger::class);
   ```

6. If you extended any package class, refactor to use composition or decoration instead.
