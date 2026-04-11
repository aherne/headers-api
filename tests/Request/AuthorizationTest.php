<?php

namespace Test\Lucinda\Headers\Request;

use Lucinda\Headers\Request\Authorization;
use Lucinda\UnitTest\Validator\Strings;

class AuthorizationTest
{
    private Authorization $object;

    public function __construct()
    {
        $this->object = new Authorization("Bearer test");
    }

    public function getType()
    {
        return (new Strings($this->object->getType()))->assertEquals("Bearer");
    }

    public function getCredentials()
    {
        return (new Strings($this->object->getCredentials()))->assertEquals("test");
    }
}
