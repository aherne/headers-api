<?php
namespace Test\Lucinda\Headers\Request\Range;

use Lucinda\Headers\Request\Range\Condition;
use Lucinda\UnitTest\Result;

class ConditionTest
{
    private $object;
    
    public function __construct()
    {
        $this->object = new Condition("1", "2");
    }

    public function getStart()
    {
        return new Result($this->object->getStart()==1);
    }
        

    public function getEnd()
    {
        return new Result($this->object->getEnd()==2);
    }
}
