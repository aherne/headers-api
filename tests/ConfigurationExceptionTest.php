<?php

namespace Test\Lucinda\Headers;

use Lucinda\Headers\ConfigurationException;
use Lucinda\UnitTest\Validator\Objects;
use Lucinda\UnitTest\Validator\Strings;

class ConfigurationExceptionTest
{
    public function inheritance(): array
    {
        $exception = new ConfigurationException("test");
        return [
            (new Objects($exception))->assertInstanceOf(\Exception::class),
            (new Strings($exception->getMessage()))->assertEquals("test")
        ];
    }
}
