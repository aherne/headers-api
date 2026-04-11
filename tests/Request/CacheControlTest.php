<?php

namespace Test\Lucinda\Headers\Request;

use Lucinda\Headers\Request\CacheControl;
use Lucinda\UnitTest\Validator\Booleans;
use Lucinda\UnitTest\Validator\Integers;

class CacheControlTest
{
    private CacheControl $object;

    public function __construct()
    {
        $this->object = new CacheControl("no-cache, no-store, max-age=1, max-stale=2, min-fresh=3");
    }

    public function isNoCache()
    {
        return (new Booleans($this->object->isNoCache()))->assertTrue();
    }

    public function isNoStore()
    {
        return (new Booleans($this->object->isNoStore()))->assertTrue();
    }

    public function getMaxAge()
    {
        return (new Integers((int) $this->object->getMaxAge()))->assertEquals(1);
    }

    public function getMaxStaleAge()
    {
        return (new Integers((int) $this->object->getMaxStaleAge()))->assertEquals(2);
    }

    public function getMinFreshAge()
    {
        return (new Integers((int) $this->object->getMinFreshAge()))->assertEquals(3);
    }
}
