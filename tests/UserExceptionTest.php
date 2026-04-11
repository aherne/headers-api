<?php

namespace Test\Lucinda\Headers;

use Lucinda\Headers\UserException;
use Lucinda\UnitTest\Validator\Objects;
use Lucinda\UnitTest\Validator\Strings;

class UserExceptionTest
{
    public function inheritance(): array
    {
        $exception = new UserException("test");
        return [
            (new Objects($exception))->assertInstanceOf(\Exception::class),
            (new Strings($exception->getMessage()))->assertEquals("test")
        ];
    }
}
