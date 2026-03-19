<?php

declare(strict_types=1);

namespace Brackets\AdvancedLogger\Interpolations;

use Brackets\AdvancedLogger\Contracts\InterpolationContract;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseInterpolation implements InterpolationContract
{
    protected ?Request $request = null;
    protected ?Response $response = null;

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    protected function escape(string $text): string
    {
        return preg_replace('/\s/', "\\s", $text);
    }

    protected function convertToString(array|string|int|null $value): string
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        if (is_null($value)) {
            $value = 'null';
        }

        return (string) $value;
    }

    protected function formatSizeUnits(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . 'GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . 'MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . 'KB';
        }

        if ($bytes > 1) {
            return $bytes . 'B';
        }

        if ($bytes === 1) {
            return $bytes . ' byte';
        }

        return '0B';
    }
}
