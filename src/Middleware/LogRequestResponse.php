<?php

namespace LogFormatter\Middleware;

use Illuminate\Http\Request;
use LogFormatter\Helpers\RequestIdGenerator;
use LogFormatter\Services\RequestLogger;

class LogRequestResponse
{
    public function __construct(
        private RequestLogger $logger
    ) {}

    public function handle(Request $request, \Closure $next)
    {
        $requestId = RequestIdGenerator::generate();
        $request->attributes->set('requestId', $requestId);

        $this->logger->log('info', [
            'type'      => 'request',
            'requestId' => $requestId,
            'method'    => $request->getMethod(),
            'path'      => $request->getPathInfo(),
            'headers'   => $request->headers->all(),
            'body'      => $request->all(),
        ]);

        $response = $next($request);

        $this->logger->log('info', [
            'type'      => 'response',
            'requestId' => $requestId,
            'status'    => $response->getStatusCode(),
            'headers'   => $response->headers->all(),
            'body'      => $response->getContent(),
        ]);

        return $response;
    }
}