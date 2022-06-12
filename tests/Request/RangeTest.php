<?php

namespace Test\Lucinda\Headers\Request;

use Lucinda\Headers\Request\Range;
use Lucinda\UnitTest\Result;

class RangeTest
{
    private $object;

    public function __construct()
    {
        $this->object = new Range("bytes=200-1000, 19000-");
    }

    public function getUnit()
    {
        return new Result($this->object->getUnit()=="bytes");
    }


    public function getConditions()
    {
        $conditions = $this->object->getConditions();
        return new Result($conditions[0]->getStart()==200 && $conditions[0]->getEnd()==1000 && $conditions[1]->getStart()==19000 && $conditions[1]->getEnd()==null);
    }
}
