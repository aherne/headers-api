<?php

namespace Test\Lucinda\Headers\Response;

use Lucinda\Headers\Response\StrictTransportSecurity;
use Lucinda\UnitTest\Validator\Booleans;
use Lucinda\UnitTest\Validator\Strings;

class StrictTransportSecurityTest
{
    private StrictTransportSecurity $object;

    public function __construct()
    {
        $this->object = new StrictTransportSecurity();
    }

    public function setIncludeSubdomains()
    {
        $this->object->setIncludeSubdomains();
        return (new Booleans(true))->assertTrue("tested via toString");
    }

    public function setPreload()
    {
        $this->object->setPreload();
        return (new Booleans(true))->assertTrue("tested via toString");
    }

    public function toString()
    {
        return (new Strings($this->object->toString()))
            ->assertEquals("max-age=31536000; includeSubDomains; preload");
    }
}
