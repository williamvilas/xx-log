<?php

namespace Tests\Unit\Formatters;

use LogFormatter\Formatters\JsonFormatter;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

class JsonFormatterTest extends TestCase
{
    private JsonFormatter $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new JsonFormatter();
    }

    public function testFormatWithLogRecord(): void
    {
        $record = new LogRecord(
            datetime: new \DateTimeImmutable('2025-09-25T12:00:00+00:00'),
            channel: 'test',
            level: Level::Info,
            message: 'Test log message',
            context: ['foo' => 'bar']
        );

        $result = $this->formatter->format($record);
        $decoded = json_decode(trim($result), true);

        $this->assertSame('laravel', $decoded['application']);
        $this->assertSame('local', $decoded['environment']);
        $this->assertSame('INFO', $decoded['level']);
        $this->assertSame('Test log message', $decoded['message']);
        $this->assertSame(['foo' => 'bar'], $decoded['context']);
        $this->assertSame('2025-09-25T12:00:00+00:00', $decoded['datetime']);
    }

    public function testFormatWithArrayRecordWithDatetime(): void
    {
        $record = [
            'level_name' => 'ERROR',
            'message'    => 'Something went wrong',
            'context'    => ['x' => 1],
            'datetime'   => new \DateTimeImmutable('2025-09-25T13:00:00+00:00'),
        ];

        $result = $this->formatter->format($record);
        $decoded = json_decode(trim($result), true);

        $this->assertSame('ERROR', $decoded['level']);
        $this->assertSame('Something went wrong', $decoded['message']);
        $this->assertSame(['x' => 1], $decoded['context']);
        $this->assertSame('2025-09-25T13:00:00+00:00', $decoded['datetime']);
    }

    public function testFormatWithArrayRecordWithoutDatetime(): void
    {
        $record = [
            'level_name' => 'DEBUG',
            'message'    => 'Missing datetime',
            'context'    => [],
        ];

        $result = $this->formatter->format($record);
        $decoded = json_decode(trim($result), true);

        $this->assertSame('DEBUG', $decoded['level']);
        $this->assertSame('Missing datetime', $decoded['message']);
        $this->assertSame([], $decoded['context']);
        $this->assertNotEmpty($decoded['datetime']); // gerado automaticamente
    }

    public function testFormatBatch(): void
    {
        $records = [
            [
                'level_name' => 'INFO',
                'message'    => 'Batch 1',
                'context'    => [],
                'datetime'   => new \DateTimeImmutable('2025-09-25T14:00:00+00:00'),
            ],
            [
                'level_name' => 'WARNING',
                'message'    => 'Batch 2',
                'context'    => ['a' => 'b'],
                'datetime'   => new \DateTimeImmutable('2025-09-25T15:00:00+00:00'),
            ],
        ];

        $results = $this->formatter->formatBatch($records);

        $this->assertCount(2, $results);

        $decoded1 = json_decode(trim($results[0]), true);
        $decoded2 = json_decode(trim($results[1]), true);

        $this->assertSame('INFO', $decoded1['level']);
        $this->assertSame('Batch 1', $decoded1['message']);
        $this->assertSame('WARNING', $decoded2['level']);
        $this->assertSame('Batch 2', $decoded2['message']);
    }
}