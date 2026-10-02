# AGENTS.md — dejwcake/advanced-logger

HTTP request/response logging for Laravel with configurable formats, handlers and hash-based
log correlation. Composer `dejwcake/advanced-logger`, namespace `Brackets\AdvancedLogger`
(fork of `brackets/advanced-logger`). Part of the Craftable ecosystem — see README.md.

## Layout

- The service provider listens to `RequestHandled` directly (no `EventServiceProvider`).
- `RequestLoggerListenerHandler` dispatches `RequestLogJob` sync or queued → `RequestLoggerService`
  interpolates the format and logs through `RequestLogger`.
- Interpolations resolve placeholders such as `{method}`, `{status}`, `{user-agent}`.
- `Benchmark` (static) tracks timing and produces the correlation hash.

## Commands

Everything runs in Docker from the package root — never against a host PHP. The full,
copy-pasteable list (composer, every QA tool and
the "whole PHP suite" one-liner) is in **README.md → "How to develop this project"**.
The ones you need most:

```shell
docker compose run --rm test composer update
docker compose run --rm test ./vendor/bin/phpunit                         # in-memory SQLite
docker compose run --rm php-qa phpcs -s --colors --extensions=php
docker compose run --rm php-qa phpcbf -s --colors --extensions=php       # auto-fix style
docker compose run --rm php-qa phpstan analyse --configuration=phpstan.neon
docker compose run --rm php-qa phpmd ./config,./src,./tests ansi phpmd.xml --suffixes php --baseline-file phpmd.baseline.xml
docker compose run --rm php-qa phpcs --standard=.phpcs.compatibility.xml --cache=.phpcs.cache
docker compose run --rm php-qa composer normalize
```

A change is done when phpcs, phpstan, phpmd and the test suite are green.

## Code conventions

- PHP `^8.5`, Laravel 13. Every file starts with `declare(strict_types=1);`.
- **No Facades** — inject contracts through the constructor.
- **No helpers**, with these exceptions: `trans()` / `__()` are allowed everywhere; `app()` only in
  models, traits and places where DI is genuinely hard to provide.
- Constructor property promotion. `final` classes and `readonly` wherever possible — prefer a
  `final readonly class`, otherwise readonly properties. A readonly property is public rather than
  hidden behind a getter.
- Always import with `use`; never inline `\Fully\Qualified\Names`.
- Alias the colliding `Repository` contracts:
  `use Illuminate\Contracts\Config\Repository as Config;`,
  `use Illuminate\Contracts\Cache\Repository as Cache;`.
- Name a property after its type: `TranslationImportService $translationImportService`, not `$service`.
- Build strings with `sprintf()` — no `"{$var}"` interpolation and no `.` concatenation.
- Mark overrides with `#[Override]` — **except** a method that overrides a *trait* method
  (e.g. `HasFactory::newFactory()`): PHP 8.5.3 segfaults on that.
- Before adding a native type to an overriding property/parameter, check the parent. If the parent
  is untyped (Laravel's `$fillable`, `$hidden`, a command's `$description`, …) the child must stay
  untyped too.
- Fix new phpstan/phpmd findings in code. Baselines are for accepted, existing debt only — inspect
  the baseline diff before committing it.

## Testing conventions

- PHPUnit 13 + Orchestra Testbench 11. Test namespaces mirror `src/`.
- Several tested methods of one class → a directory named after the class with one
  `<Method>Test.php` per method.
- Feature tests when several real classes collaborate; Unit tests for isolated logic (mock the
  rest). Don't write tests for service providers or install commands.
- PHPUnit assertions are static: `self::assert*()`. Laravel's instance assertions
  (`$this->assertDatabaseHas()`, response asserts) stay on `$this`.
- Resolve services with `$this->app->make()`, never `app()`.
- Test-only models and stubs live in the `tests/` root.

## Package notes

- All concrete classes are `final` — so methods are `private`, not `protected`, and use `self::`.
  `BaseInterpolation` is the one `abstract` class.
- Typed constants (`private const string …`), `match` over `switch`.
- Unit tests mock collaborators with Mockery (`ReflectionClass::newInstanceWithoutConstructor()`
  for final classes); test stubs `TestBaseInterpolation` and `TestLogger` sit in `tests/`.
- No database: tests run on in-memory SQLite and the compose file has no DB services.

## Versioning

The package is on **2.x** and stays there through the Laravel 13 / PHP 8.5 upgrade — don't add
v3 upgrade sections or bump the `branch-alias`. User-facing changes go to `UPGRADE.md` when
consumers have to act.
