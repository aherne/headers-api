<?php
namespace Test\Lucinda\Headers\Request;

use Lucinda\Headers\Request\CacheControl;
use Lucinda\UnitTest\Result;

class CacheControlTest
{
    private $object;
    
    public function __construct()
    {
        $this->object = new CacheControl("no-cache, no-store, max-age=1, max-stale=2, min-fresh=3");
    }
    
    public function isNoCache()
    {
        return new Result($this->object->isNoCache());
    }
        

    public function isNoStore()
    {
        return new Result($this->object->isNoStore());
    }
        

    public function getMaxAge()
    {
        return new Result($this->object->getMaxAge()==1);
    }
        

    public function getMaxStaleAge()
    {
        return new Result($this->object->getMaxStaleAge()==2);
    }
        

    public function getMinFreshAge()
    {
        return new Result($this->object->getMinFreshAge()==3);
    }
}
