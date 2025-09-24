<?php


namespace LogFormatter\Contracts;

interface LoggerInterface
{
    public function log(string $level, array $data): void;
}