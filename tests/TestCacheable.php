<?php
namespace Test\Lucinda\Headers;

use Lucinda\Headers\Cacheable;

class TestCacheable implements Cacheable
{
    public function getEtag(): string
    {
        return "qwerty";
    }

    public function getTime(): int
    {
        return time();
    }
}
