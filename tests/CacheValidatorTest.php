<?php

namespace Test\Lucinda\Headers;

use Lucinda\Headers\CacheValidator;
use Lucinda\Headers\Request;
use Lucinda\UnitTest\Validator\Integers;

class CacheValidatorTest
{
    public function validate(): array
    {
        $results = [];
        $cacheable = new TestCacheable();
        $requestMethod = "GET";

        $request = new Request(["Host"=>"www.google.com"]);
        $validator = new CacheValidator($request);
        $results[] = (new Integers($validator->validate($cacheable, $requestMethod)))
            ->assertEquals(200, "no validation");

        $request = new Request(["If-Match"=>"asdfg"]);
        $validator = new CacheValidator($request);
        $results[] = (new Integers($validator->validate($cacheable, $requestMethod)))
            ->assertEquals(412, "not matching If-Match");

        $request = new Request(["If-Match"=>"qwerty"]);
        $validator = new CacheValidator($request);
        $results[] = (new Integers($validator->validate($cacheable, $requestMethod)))
            ->assertEquals(200, "matching If-Match");

        $request = new Request(["If-None-Match"=>"asdfg"]);
        $validator = new CacheValidator($request);
        $results[] = (new Integers($validator->validate($cacheable, $requestMethod)))
            ->assertEquals(200, "not matching If-None-Match");

        $request = new Request(["If-None-Match"=>"qwerty"]);
        $validator = new CacheValidator($request);
        $results[] = (new Integers($validator->validate($cacheable, $requestMethod)))
            ->assertEquals(304, "matching If-None-Match");

        $request = new Request(["If-Unmodified-Since"=>gmdate("D, d M Y H:i:s T", $cacheable->getTime()-100)]);
        $validator = new CacheValidator($request);
        $results[] = (new Integers($validator->validate($cacheable, $requestMethod)))
            ->assertEquals(412, "not matching If-Unmodified-Since");

        $request = new Request(["If-Unmodified-Since"=>gmdate("D, d M Y H:i:s T", $cacheable->getTime())]);
        $validator = new CacheValidator($request);
        $results[] = (new Integers($validator->validate($cacheable, $requestMethod)))
            ->assertEquals(200, "matching If-Unmodified-Since");

        $request = new Request(["If-Modified-Since"=>gmdate("D, d M Y H:i:s T", $cacheable->getTime()-100)]);
        $validator = new CacheValidator($request);
        $results[] = (new Integers($validator->validate($cacheable, $requestMethod)))
            ->assertEquals(200, "not matching If-Modified-Since");

        $request = new Request(["If-Modified-Since"=>gmdate("D, d M Y H:i:s T", $cacheable->getTime())]);
        $validator = new CacheValidator($request);
        $results[] = (new Integers($validator->validate($cacheable, $requestMethod)))
            ->assertEquals(304, "matching If-Modified-Since");

        return $results;
    }
}
