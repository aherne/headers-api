<?php

namespace Test\Lucinda\Headers\Response;

use Lucinda\UnitTest\Result;
use Lucinda\Headers\Response\CacheControl;

class CacheControlTest
{
    public function setPublic()
    {
        $object = new CacheControl();
        $object->setPublic();
        return new Result($object->toString()=="public");
    }


    public function setPrivate()
    {
        $object = new CacheControl();
        $object->setPrivate();
        return new Result($object->toString()=="private");
    }


    public function setNoCache()
    {
        $object = new CacheControl();
        $object->setNoCache();
        return new Result($object->toString()=="no-cache");
    }


    public function setNoStore()
    {
        $object = new CacheControl();
        $object->setNoStore();
        return new Result($object->toString()=="no-store");
    }


    public function setMustRevalidate()
    {
        $object = new CacheControl();
        $object->setMustRevalidate();
        return new Result($object->toString()=="must-revalidate");
    }


    public function setMaxAge()
    {
        $object = new CacheControl();
        $object->setMaxAge(10);
        return new Result($object->toString()=="max-age=10");
    }


    public function setNoTransform()
    {
        $object = new CacheControl();
        $object->setNoTransform();
        return new Result($object->toString()=="no-transform");
    }


    public function setProxyRevalidate()
    {
        $object = new CacheControl();
        $object->setProxyRevalidate();
        return new Result($object->toString()=="proxy-revalidate");
    }


    public function setProxyMaxAge()
    {
        $object = new CacheControl();
        $object->setProxyMaxAge(10);
        return new Result($object->toString()=="s-maxage=10");
    }


    public function toString()
    {
        $object = new CacheControl();
        $object->setNoCache();
        $object->setNoStore();
        $object->setMaxAge(10);
        return new Result($object->toString()=="no-cache, no-store, max-age=10");
    }
}
