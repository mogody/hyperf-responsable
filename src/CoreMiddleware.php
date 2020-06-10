<?php

declare(strict_types=1);
/**
 * This file is part of mogody/hyperf-responsable.
 *
 * @link     https://github.com/mogody/hyperf-responsable
 * @document https://github.com/mogody/hyperf-responsable/blob/master/README.md
 * @contact  wenghang1228@gmail.com
 * @license  https://github.com/mogody/hyperf-responsable/blob/master/LICENSE
 */
namespace Mogody\Responsable;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Jsonable;
use Mogody\Responsable\Contract\Responsable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CoreMiddleware extends \Hyperf\HttpServer\CoreMiddleware
{
    /**
     * Transfer the non-standard response content to a standard response object.
     * @param array|Arrayable|Jsonable|string $response
     */
    protected function transferToResponse($response, ServerRequestInterface $request): ResponseInterface
    {
        if (is_string($response)) {
            return $this->response()->withAddedHeader('content-type', 'text/plain')->withBody(new SwooleStream($response));
        }

        if (is_array($response) || $response instanceof Arrayable) {
            if ($response instanceof Arrayable) {
                $response = $response->toArray();
            }
            return $this->response()
                ->withAddedHeader('content-type', 'application/json')
                ->withBody(new SwooleStream(json_encode($response, JSON_UNESCAPED_UNICODE)));
        }

        if ($response instanceof Jsonable) {
            return $this->response()
                ->withAddedHeader('content-type', 'application/json')
                ->withBody(new SwooleStream((string) $response));
        }

        if ($response instanceof Responsable) {
            return $response->toResponse($request);
        }

        return $this->response()->withAddedHeader('content-type', 'text/plain')->withBody(new SwooleStream((string) $response));
    }
}
