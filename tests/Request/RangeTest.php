<?php

namespace Test\Lucinda\Headers\Request;

use Lucinda\Headers\Request\Range;
use Lucinda\UnitTest\Validator\Arrays;
use Lucinda\UnitTest\Validator\Booleans;
use Lucinda\UnitTest\Validator\Strings;

class RangeTest
{
    private Range $object;

    public function __construct()
    {
        $this->object = new Range("bytes=200-1000, 19000-");
    }

    public function getUnit()
    {
        return (new Strings($this->object->getUnit()))->assertEquals("bytes");
    }

    public function getConditions(): array
    {
        $conditions = $this->object->getConditions();
        return [
            (new Arrays($conditions))->assertSize(2),
            (new Strings((string) $conditions[0]->getStart()))->assertEquals("200"),
            (new Strings((string) $conditions[0]->getEnd()))->assertEquals("1000"),
            (new Strings((string) $conditions[1]->getStart()))->assertEquals("19000"),
            (new Booleans($conditions[1]->getEnd() === null))->assertTrue()
        ];
    }
}
