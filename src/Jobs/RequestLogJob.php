<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Jobs;

use Brackets\AdvancedLogger\Services\RequestLoggerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\HttpFoundation\Response;

class RequestLogJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected Request $request, protected Response $response)
    {
    }

    public function handle(): void
    {
        $requestLoggerService = app(RequestLoggerService::class);
        $requestLoggerService->log($this->request, $this->response);
    }
}
