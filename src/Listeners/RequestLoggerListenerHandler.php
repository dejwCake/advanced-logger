<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Listeners;

use Brackets\AdvancedLogger\Jobs\RequestLogJob;
use Brackets\AdvancedLogger\Services\Benchmark;
use Brackets\AdvancedLogger\Services\RequestLoggerService;
use Exception;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;

final class RequestLoggerListenerHandler
{
    public function __construct(
        private readonly Repository $config,
        private readonly Container $container,
        private readonly BusDispatcher $busDispatcher,
    ) {
    }

    /**
     * @throws Exception
     */
    public function handle(RequestHandled $event): void
    {
        Benchmark::end($this->config->get('advanced-logger.request.benchmark', 'application'));

        if (!$this->excluded($event->request)) {
            $task = $this->container->make(RequestLogJob::class, ['request' => $event->request, 'response' => $event->response]);
            $queueName = $this->config->get('advanced-logger.request.queue');
            if (is_null($queueName)) {
                $task->handle($this->container->make(RequestLoggerService::class));
            } else {
                $this->busDispatcher->dispatch(is_string($queueName) ? $task->onQueue($queueName) : $task);
            }
        }
    }

    /**
     * Check if current path is not excluded
     */
    private function excluded(Request $request): bool
    {
        $excludedPaths = $this->config->get('advanced-logger.request.excluded-paths');
        if ($excludedPaths === null || $excludedPaths === []) {
            return false;
        }
        foreach ($excludedPaths as $excludedPath) {
            if ($request->is($excludedPath)) {
                return true;
            }
        }

        return false;
    }
}
