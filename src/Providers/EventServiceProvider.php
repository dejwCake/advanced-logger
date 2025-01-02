<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Providers;

use Brackets\AdvancedLogger\Listeners\RequestLoggerListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<string, array<int, string>>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $listen = [

    ];

    /**
     * The subscriber classes to register.
     *
     * @var array<class-string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $subscribe = [
        RequestLoggerListener::class,
    ];
}
