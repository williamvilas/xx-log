<?php

namespace LogFormatter\Contracts;

interface FormatterInterface
{
    public function format(array $data): string;
}