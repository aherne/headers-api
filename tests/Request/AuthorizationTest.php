<?php
namespace Test\Lucinda\Headers\Request;

use Lucinda\Headers\Request\Authorization;
use Lucinda\UnitTest\Result;

class AuthorizationTest
{
    private $object;
    
    public function __construct()
    {
        $this->object = new Authorization("Bearer test");
    }

    public function getType()
    {
        return new Result($this->object->getType()=="Bearer");
    }
        

    public function getCredentials()
    {
        return new Result($this->object->getCredentials()=="test");
    }
}
