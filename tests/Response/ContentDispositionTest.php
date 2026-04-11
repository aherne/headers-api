<?php

namespace Test\Lucinda\Headers\Response;

use Lucinda\Headers\Response\ContentDisposition;
use Lucinda\UnitTest\Validator\Strings;

class ContentDispositionTest
{
    public function setFileName()
    {
        $object = new ContentDisposition("attachment");
        $object->setFileName("abc.jpg");
        return (new Strings($object->toString()))->assertEquals('attachment; filename="abc.jpg"');
    }

    public function toString()
    {
        $object = new ContentDisposition("inline");
        return (new Strings($object->toString()))->assertEquals("inline");
    }
}
