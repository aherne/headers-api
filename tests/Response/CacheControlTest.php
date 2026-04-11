<?php

namespace Test\Lucinda\Headers\Response;

use Lucinda\Headers\Response\CacheControl;
use Lucinda\UnitTest\Validator\Strings;

class CacheControlTest
{
    public function setPublic()
    {
        $object = new CacheControl();
        $object->setPublic();
        return (new Strings($object->toString()))->assertEquals("public");
    }

    public function setPrivate()
    {
        $object = new CacheControl();
        $object->setPrivate();
        return (new Strings($object->toString()))->assertEquals("private");
    }

    public function setNoCache()
    {
        $object = new CacheControl();
        $object->setNoCache();
        return (new Strings($object->toString()))->assertEquals("no-cache");
    }

    public function setNoStore()
    {
        $object = new CacheControl();
        $object->setNoStore();
        return (new Strings($object->toString()))->assertEquals("no-store");
    }

    public function setMustRevalidate()
    {
        $object = new CacheControl();
        $object->setMustRevalidate();
        return (new Strings($object->toString()))->assertEquals("must-revalidate");
    }

    public function setMaxAge()
    {
        $object = new CacheControl();
        $object->setMaxAge(10);
        return (new Strings($object->toString()))->assertEquals("max-age=10");
    }

    public function setNoTransform()
    {
        $object = new CacheControl();
        $object->setNoTransform();
        return (new Strings($object->toString()))->assertEquals("no-transform");
    }

    public function setProxyRevalidate()
    {
        $object = new CacheControl();
        $object->setProxyRevalidate();
        return (new Strings($object->toString()))->assertEquals("proxy-revalidate");
    }

    public function setProxyMaxAge()
    {
        $object = new CacheControl();
        $object->setProxyMaxAge(10);
        return (new Strings($object->toString()))->assertEquals("s-maxage=10");
    }

    public function toString()
    {
        $object = new CacheControl();
        $object->setNoCache();
        $object->setNoStore();
        $object->setMaxAge(10);
        return (new Strings($object->toString()))->assertEquals("no-cache, no-store, max-age=10");
    }
}
