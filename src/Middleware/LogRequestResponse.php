<?php

namespace LogFormatter\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LogFormatter\Helpers\RequestIdGenerator;

class LogRequestResponse
{
    public function handle(Request $request, \Closure $next)
    {
        $requestId = $request->header('Request-Id', RequestIdGenerator::generate());
        $request->attributes->set('Request-Id', $requestId);

        Log::info('Request via api', [
            'type'      => 'request',
            'requestId' => $requestId,
            'method'    => $request->getMethod(),
            'path'      => $request->getPathInfo(),
            'headers'   => $request->headers->all(),
            'body'      => $request->all(),
        ]);

        $response = $next($request);

        Log::info('Response via api', [
            'type'      => 'response',
            'requestId' => $requestId,
            'status'    => $response->getStatusCode(),
            'headers'   => $response->headers->all(),
            'body'      => $response->getContent(),
        ]);

        return $response;
    }
}