<?php

namespace Test\Lucinda\Headers;

use Lucinda\Headers\Cacheable;

class TestCacheable implements Cacheable
{
    private string $etag;
    private int $time;

    public function __construct(string $etag = "qwerty", ?int $time = null)
    {
        $this->etag = $etag;
        $this->time = $time ?? 1700000000;
    }

    public function getEtag(): string
    {
        return $this->etag;
    }

    public function getTime(): int
    {
        return $this->time;
    }
}
