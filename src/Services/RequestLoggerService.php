<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Services;

use Brackets\AdvancedLogger\Interpolations\RequestInterpolation;
use Brackets\AdvancedLogger\Interpolations\ResponseInterpolation;
use Brackets\AdvancedLogger\Loggers\RequestLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class RequestLoggerService
{
    protected const LOG_CONTEXT = 'RESPONSE';

    /** @var array<string, string> */
    protected array $formats = [
        'full' => '{request-hash} | HTTP/{http-version} {status} | {remote-addr} | {user} | {method} {url} {query} | {response-time} s | {user-agent} | {referer}',
        'combined' => '{remote-addr} - {remote-user} [{date}] "{method} {url} HTTP/{http-version}" {status} {content-length} "{referer}" "{user-agent}"',
        'common' => '{remote-addr} - {remote-user} [{date}] "{method} {url} HTTP/{http-version}" {status} {content-length}',
        'dev' => '{method} {url} {status} {response-time} s - {content-length}',
        'short' => '{remote-addr} {remote-user} {method} {url} HTTP/{http-version} {status} {content-length} - {response-time} s',
        'tiny' => '{method} {url} {status} {content-length} - {response-time} s',
    ];

    public function __construct(
        protected RequestLogger $logger,
        protected RequestInterpolation $requestInterpolation,
        protected ResponseInterpolation $responseInterpolation,
    ) {
    }

    public function log(Request $request, Response $response): void
    {
        $this->requestInterpolation->setRequest($request);

        $this->responseInterpolation->setResponse($response);

        if (config('advanced-logger.request.enabled')) {
            $format = config('advanced-logger.request.format', 'full');
            $format = Arr::get($this->formats, $format, $format);

            $message = $this->responseInterpolation->interpolate($format);
            $message = $this->requestInterpolation->interpolate($message);

            $this->logger->log(config('advanced-logger.request.level', 'info'), $message, [
                static::LOG_CONTEXT,
            ]);
        }
    }
}
