<?php

namespace Test\Lucinda\Headers\Response;

use Lucinda\Headers\Response\WwwAuthenticate;
use Lucinda\UnitTest\Validator\Strings;

class WwwAuthenticateTest
{
    public function addChallenge()
    {
        $object = new WwwAuthenticate("Basic", "my realm");
        $object->addChallenge("a", "b");
        return (new Strings($object->toString()))->assertEquals('Basic realm="my realm", a="b"');
    }

    public function toString()
    {
        $object = new WwwAuthenticate("Basic");
        return (new Strings($object->toString()))->assertEquals("Basic");
    }
}
