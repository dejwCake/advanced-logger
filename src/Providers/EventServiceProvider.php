<?php

namespace Brackets\AdvancedLogger\Providers;

use Brackets\AdvancedLogger\Listeners\RequestLoggerListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string>
     */
    protected $listen = [

    ];

    /**
     * The subscriber classes to register.
     *
     * @var array<class-string>
     */
    protected $subscribe = [
        RequestLoggerListener::class,
    ];
}
