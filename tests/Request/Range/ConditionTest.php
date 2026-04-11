<?php

namespace Test\Lucinda\Headers\Request\Range;

use Lucinda\Headers\Request\Range\Condition;
use Lucinda\UnitTest\Validator\Integers;

class ConditionTest
{
    private Condition $object;

    public function __construct()
    {
        $this->object = new Condition("1", "2");
    }

    public function getStart()
    {
        return (new Integers((int) $this->object->getStart()))->assertEquals(1);
    }

    public function getEnd()
    {
        return (new Integers((int) $this->object->getEnd()))->assertEquals(2);
    }
}
