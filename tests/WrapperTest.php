<?php

namespace Test\Lucinda\Headers;

use Lucinda\Headers\Request;
use Lucinda\Headers\Response;
use Lucinda\Headers\Wrapper;
use Lucinda\UnitTest\Validator\Arrays;
use Lucinda\UnitTest\Validator\Integers;
use Lucinda\UnitTest\Validator\Objects;

class WrapperTest
{
    private \SimpleXMLElement $xml;

    public function __construct()
    {
        $this->xml = \simplexml_load_string(
            '
<xml>
    <headers cache_expiration="10" allow_credentials="1" cors_max_age="5" allowed_origins="https://www.google.com" allowed_request_headers="X-Custom-Header, Upgrade-Insecure-Requests" allowed_response_headers="Content-Length, X-Kuma-Revision"/>
    <routes>
        <route id="index" allowed_methods="GET,POST"/>
        <route id="login" no_cache="1"/>
        <route id="admin" allow_credentials="0" cors_max_age="9" allowed_origins="https://admin.example.com" allowed_methods="PUT" allowed_request_headers="X-Admin-Header" allowed_response_headers="X-Admin-Token"/>
    </routes>
</xml>
'
        );
    }

    public function getRequest(): array
    {
        $wrapper = new Wrapper(
            $this->xml,
            "index",
            ["Accept"=>"text/html, application/xml;q=0.9, */*;q=0.8", "Accept-Charset"=>"utf-8, iso-8859-1;q=0.5"]
        );

        return [
            (new Objects($wrapper->getRequest()))->assertInstanceOf(Request::class),
            (new Arrays($wrapper->getRequest()->getAccept()))->assertEquals(["text/html", "application/xml"]),
            (new Arrays($wrapper->getRequest()->getAcceptCharset()))->assertEquals(["utf-8", "iso-8859-1"])
        ];
    }

    public function validateCache(): array
    {
        $results = [];
        $cacheable = new TestCacheable();
        $expectedResponse = [
            "Cache-Control"=>"public, max-age=10",
            "ETag"=>'"qwerty"',
            "Last-Modified"=>gmdate("D, d M Y H:i:s T", $cacheable->getTime())
        ];

        $wrapper = new Wrapper($this->xml, "index", ["Accept"=>"text/html, application/xml;q=0.9, */*;q=0.8"]);
        $results[] = (new Integers($wrapper->validateCache($cacheable, "GET")))
            ->assertEquals(200, "cacheable but no conditionals");
        $results[] = (new Arrays($wrapper->getResponse()->toArray()))
            ->assertEquals($expectedResponse, "cacheable but no conditionals");

        $wrapper = new Wrapper($this->xml, "login", []);
        $results[] = (new Integers($wrapper->validateCache($cacheable, "GET")))
            ->assertEquals(200, "not cacheable");
        $results[] = (new Arrays($wrapper->getResponse()->toArray()))
            ->assertEquals([], "not cacheable");

        $wrapper = new Wrapper($this->xml, "index", ["If-None-Match"=>"asdfg"]);
        $results[] = (new Integers($wrapper->validateCache($cacheable, "GET")))
            ->assertEquals(200, "cacheable but conditionals fail");
        $results[] = (new Arrays($wrapper->getResponse()->toArray()))
            ->assertEquals($expectedResponse, "cacheable but conditionals fail");

        $wrapper = new Wrapper($this->xml, "index", ["If-None-Match"=>"qwerty"]);
        $results[] = (new Integers($wrapper->validateCache($cacheable, "GET")))
            ->assertEquals(304, "cacheable and conditionals match");
        $results[] = (new Arrays($wrapper->getResponse()->toArray()))
            ->assertEquals($expectedResponse, "cacheable and conditionals match");

        return $results;
    }

    public function validateCors()
    {
        $wrapper = new Wrapper(
            $this->xml,
            "index",
            [
                "Origin"=>"https://www.google.com",
                "Access-Control-Request-Method"=>"POST",
                "Access-Control-Request-Headers"=>"X-Custom-Header, Upgrade-Insecure-Requests"
            ]
        );
        $wrapper->validateCors("https://www.google.com");

        return (new Arrays($wrapper->getResponse()->toArray()))->assertEquals(
            [
                "Access-Control-Allow-Credentials"=>"true",
                "Access-Control-Allow-Headers"=>"X-Custom-Header, Upgrade-Insecure-Requests",
                "Access-Control-Allow-Methods"=>"GET, POST",
                "Access-Control-Allow-Origin"=>"https://www.google.com",
                "Access-Control-Expose-Headers"=>"Content-Length, X-Kuma-Revision",
                "Access-Control-Max-Age"=>5,
                "Vary"=>"Origin"
            ]
        );
    }

    public function validateCorsWithRouteOverrides()
    {
        $wrapper = new Wrapper(
            $this->xml,
            "admin",
            [
                "Origin"=>"https://admin.example.com",
                "Access-Control-Request-Method"=>"PUT",
                "Access-Control-Request-Headers"=>"X-Admin-Header"
            ]
        );
        $wrapper->validateCors();

        return (new Arrays($wrapper->getResponse()->toArray()))->assertEquals(
            [
                "Access-Control-Allow-Headers"=>"X-Admin-Header",
                "Access-Control-Allow-Methods"=>"PUT",
                "Access-Control-Allow-Origin"=>"https://admin.example.com",
                "Access-Control-Expose-Headers"=>"X-Admin-Token",
                "Access-Control-Max-Age"=>9,
                "Vary"=>"Origin"
            ]
        );
    }

    public function getResponse(): array
    {
        $cacheable = new TestCacheable();
        $wrapper = new Wrapper($this->xml, "index", ["If-None-Match"=>"asdfg"]);
        $httpStatus = $wrapper->validateCache($cacheable, "GET");
        $response = $wrapper->getResponse();

        return [
            (new Integers($httpStatus))->assertEquals(200),
            (new Objects($response))->assertInstanceOf(Response::class),
            (new Arrays($response->toArray()))->assertEquals(
                [
                    "Cache-Control"=>"public, max-age=10",
                    "ETag"=>'"qwerty"',
                    "Last-Modified"=>gmdate("D, d M Y H:i:s T", $cacheable->getTime())
                ]
            )
        ];
    }
}
