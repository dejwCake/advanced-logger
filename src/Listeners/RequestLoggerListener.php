<?php

namespace Brackets\AdvancedLogger\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Http\Events\RequestHandled;

class RequestLoggerListener
{
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            RequestHandled::class,
            RequestLoggerListenerHandler::class
        );
    }
}
