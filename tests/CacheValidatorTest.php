<?php

namespace Test\Lucinda\Headers;

use Lucinda\Headers\CacheValidator;
use Lucinda\Headers\Request;
use Lucinda\UnitTest\Result;

class CacheValidatorTest
{
    public function validate()
    {
        $results = [];

        $cacheable = new TestCacheable();
        $requestMethod = "GET";

        // test no validation
        $request = new Request(["Host"=>"www.google.com"]);
        $validator = new CacheValidator($request);
        $results[] = new Result($validator->validate($cacheable, $requestMethod)==200, "no validation");

        // test failed etag validation
        $request = new Request(["If-Match"=>"asdfg"]);
        $validator = new CacheValidator($request);
        $results[] = new Result($validator->validate($cacheable, $requestMethod)==412, "not matching If-Match");

        // test failed etag validation
        $request = new Request(["If-Match"=>"qwerty"]);
        $validator = new CacheValidator($request);
        $results[] = new Result($validator->validate($cacheable, $requestMethod)==200, "matching If-Match");

        // test failed etag validation
        $request = new Request(["If-None-Match"=>"asdfg"]);
        $validator = new CacheValidator($request);
        $results[] = new Result($validator->validate($cacheable, $requestMethod)==200, "not matching If-None-Match");

        // test failed etag validation
        $request = new Request(["If-None-Match"=>"qwerty"]);
        $validator = new CacheValidator($request);
        $results[] = new Result($validator->validate($cacheable, $requestMethod)==304, "matching If-None-Match");

        // test failed date validation
        $request = new Request(["If-Unmodified-Since"=>date("D, d M Y H:i:s T", time()-100)]);
        $validator = new CacheValidator($request);
        $results[] = new Result($validator->validate($cacheable, $requestMethod)==412, "not matching If-Unmodified-Since");

        // test failed date validation
        $request = new Request(["If-Unmodified-Since"=>date("D, d M Y H:i:s T", time())]);
        $validator = new CacheValidator($request);
        $results[] = new Result($validator->validate($cacheable, $requestMethod)==200, "matching If-Unmodified-Since");

        // test failed date validation
        $request = new Request(["If-Modified-Since"=>date("D, d M Y H:i:s T", time()-100)]);
        $validator = new CacheValidator($request);
        $results[] = new Result($validator->validate($cacheable, $requestMethod)==200, "not matching If-Modified-Since");

        // test failed date validation
        $request = new Request(["If-Modified-Since"=>date("D, d M Y H:i:s T", time())]);
        $validator = new CacheValidator($request);
        $results[] = new Result($validator->validate($cacheable, $requestMethod)==304, "matching If-Modified-Since");

        return $results;
    }
}
