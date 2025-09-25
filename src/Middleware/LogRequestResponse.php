<?php

namespace LogFormatter\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LogFormatter\Helpers\RequestIdGenerator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LogRequestResponse
{
    private const SENSITIVE_FIELDS = [
        'password', 'password_confirmation', 'current_password', 'new_password',
        'token', 'api_key', 'secret', 'credit_card', 'cvv', 'ssn', 'cpf', 'cnpj',
        'auth_token', 'access_token', 'refresh_token',
    ];

    private const BINARY_CONTENT_TYPES = [
        'image/', 'video/', 'audio/', 'application/octet-stream',
        'application/pdf', 'application/zip', 'application/gzip',
    ];

    private const SENSITIVE_HEADERS = [
        'authorization', 'cookie', 'set-cookie', 'x-api-key', 'x-auth-token',
    ];

    private const MAX_RESPONSE_SIZE = 10000;
    private const LARGE_STRING_THRESHOLD = 1000;
    private const BINARY_RATIO_THRESHOLD = 0.1;

    private bool $shouldLogRequest;
    private bool $shouldLogResponse;

    public function __construct()
    {
        // Configurações podem vir de environment variables
        $this->shouldLogRequest = config('logging.request.enabled', true);
        $this->shouldLogResponse = config('logging.response.enabled', true);
    }

    public function handle(Request $request, \Closure $next)
    {
        $requestId = $request->header('Request-Id') ?? RequestIdGenerator::generate();
        $request->attributes->set('Request-Id', $requestId);

        // Log assíncrono da request
        if ($this->shouldLogRequest) {
            $this->logRequestAsync($request, $requestId);
        }

        $response = $next($request);

        // Log assíncrono da response
        if ($this->shouldLogResponse) {
            $this->logResponseAsync($response, $requestId);
        }

        return $response;
    }

    private function logRequestAsync(Request $request, string $requestId): void
    {
        // Usar register_shutdown_function para execução após response
        register_shutdown_function(function () use ($request, $requestId) {
            $logData = [
                'type'      => 'request',
                'requestId' => $requestId,
                'method'    => $request->getMethod(),
                'path'      => $request->getPathInfo(),
                'headers'   => $this->filterHeadersQuick($request->headers->all()),
                'body'      => $this->filterBodyQuick($request),
                'timestamp' => microtime(true),
            ];

            // Log em background sem bloquear a response
            $this->backgroundLog('Request via api', $logData);
        });
    }

    private function logResponseAsync($response, string $requestId): void
    {
        register_shutdown_function(function () use ($response, $requestId) {
            $logData = [
                'type'      => 'response',
                'requestId' => $requestId,
                'status'    => $response->getStatusCode(),
                'headers'   => $this->filterHeadersQuick($response->headers->all()),
                'body'      => $this->filterResponseBodyQuick($response),
                'timestamp' => microtime(true),
            ];

            $this->backgroundLog('Response via api', $logData);
        });
    }

    private function backgroundLog(string $message, array $context): void
    {
        // Usar queue ou execução em background
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        try {
            Log::info($message, $context);
        } catch (\Throwable $e) {
            // Falha silenciosa para não impactar a aplicação
            error_log('Logging failed: ' . $e->getMessage());
        }
    }

    private function filterBodyQuick(Request $request): array
    {
        $body = $request->except($request->files->keys());

        // Filtro rápido de campos sensíveis
        return $this->filterSensitiveFieldsQuick($body);
    }

    private function filterResponseBodyQuick($response)
    {
        $contentType = $response->headers->get('Content-Type', '');

        if ($this->isBinaryContentQuick($contentType)) {
            return '[BINARY_CONTENT]';
        }

        $content = $response->getContent();

        // Verificação rápida de tamanho antes de processar
        if (strlen($content) > self::MAX_RESPONSE_SIZE) {
            return '[LARGE_CONTENT]';
        }

        if (str_contains($contentType, 'application/json') && $content) {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->filterSensitiveFieldsQuick($decoded);
            }
        }

        return $content;
    }

    private function filterSensitiveFieldsQuick(array $data): array
    {
        $sensitiveFields = array_flip(self::SENSITIVE_FIELDS);

        array_walk_recursive($data, function (&$value, $key) use ($sensitiveFields) {
            if (isset($sensitiveFields[strtolower($key)])) {
                $value = '[REDACTED]';
            } elseif (is_string($value) && strlen($value) > self::LARGE_STRING_THRESHOLD) {
                $value = '[LARGE_DATA]';
            }
        });

        return $data;
    }

    private function isBinaryContentQuick(string $contentType): bool
    {
        foreach (self::BINARY_CONTENT_TYPES as $binaryType) {
            if (str_starts_with($contentType, $binaryType)) {
                return true;
            }
        }
        return false;
    }

    private function filterHeadersQuick(array $headers): array
    {
        $sensitiveHeaders = array_flip(self::SENSITIVE_HEADERS);
        $filtered = [];

        foreach ($headers as $key => $value) {
            $lowerKey = strtolower($key);
            if (isset($sensitiveHeaders[$lowerKey])) {
                $filtered[$key] = '[REDACTED]';
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    // Métodos otimizados para arrays grandes
    private function isBase64EncodedQuick(string $data): bool
    {
        // Verificação mais rápida - apenas valida formato base64
        if (strlen($data) % 4 !== 0 || !preg_match('/^[a-zA-Z0-9\/+]*={0,2}$/', $data)) {
            return false;
        }

        // Decodificação rápida com limite de tamanho
        if (strlen($data) > 5000) {
            $sample = substr($data, 0, 1000);
            $decoded = base64_decode($sample, true);
            return $decoded !== false && $this->hasBinaryContent($decoded);
        }

        $decoded = base64_decode($data, true);
        return $decoded !== false && $this->hasBinaryContent($decoded);
    }

    private function hasBinaryContent(string $data): bool
    {
        // Verificação mais rápida de conteúdo binário
        $sampleSize = min(100, strlen($data));
        $sample = substr($data, 0, $sampleSize);

        $nonPrintable = 0;
        for ($i = 0; $i < $sampleSize; $i++) {
            $char = ord($sample[$i]);
            if ($char < 32 && $char !== 9 && $char !== 10 && $char !== 13) {
                $nonPrintable++;
            }
        }

        return ($nonPrintable / $sampleSize) > self::BINARY_RATIO_THRESHOLD;
    }
}