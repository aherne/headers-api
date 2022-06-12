<?php

namespace Test\Lucinda\Headers\Response;

use Lucinda\Headers\Response\StrictTransportSecurity;
use Lucinda\UnitTest\Result;

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
        return new Result(true, "tested via toString");
    }


    public function setPreload()
    {
        $this->object->setPreload();
        return new Result(true, "tested via toString");
    }


    public function toString()
    {
        return new Result($this->object->toString()=="max-age: 31536000; includeSubdomains; preload");
    }
}
