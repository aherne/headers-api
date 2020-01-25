<?php
namespace Test\Lucinda\Headers\Response;

use Lucinda\Headers\Response\ContentDisposition;
use Lucinda\UnitTest\Result;

class ContentDispositionTest
{
    public function setFileName()
    {
        $object = new ContentDisposition("attachment");
        $object->setFileName("abc.jpg");
        return new Result($object->toString()=='attachment; filename="abc.jpg"');
    }
        

    public function toString()
    {
        $object = new ContentDisposition("inline");
        return new Result($object->toString()=='inline');
    }
}
