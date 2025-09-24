<?php

namespace LogFormatter\Helpers;

use Illuminate\Support\Str;

class RequestIdGenerator
{
    public static function generate(): string
    {
        return Str::uuid()->toString();
    }
}