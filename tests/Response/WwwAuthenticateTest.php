<?php

namespace Test\Lucinda\Headers\Response;

use Lucinda\Headers\Response\WwwAuthenticate;
use Lucinda\UnitTest\Result;

class WwwAuthenticateTest
{
    public function addChallenge()
    {
        $object = new WwwAuthenticate("Basic", "my realm");
        $object->addChallenge("a", "b");
        return new Result($object->toString()=='Basic realm="my realm", a="b"');
    }


    public function toString()
    {
        $object = new WwwAuthenticate("Basic");
        return new Result($object->toString()=='Basic');
    }
}
