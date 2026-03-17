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

final class RequestLogJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly Request $request, private readonly Response $response)
    {
    }

    public function handle(RequestLoggerService $requestLoggerService): void
    {
        $requestLoggerService->log($this->request, $this->response);
    }
}
