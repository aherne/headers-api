<?php

namespace Test\Lucinda\Headers;

use Lucinda\Headers\CorsValidator;
use Lucinda\Headers\PolicyLocator;
use Lucinda\Headers\Request;
use Lucinda\UnitTest\Validator\Arrays;
use Lucinda\UnitTest\Validator\Booleans;
use Lucinda\UnitTest\Validator\Integers;
use Lucinda\UnitTest\Validator\Strings;

class CorsValidatorTest
{
    private CorsValidator $validator;

    public function __construct()
    {
        $xml = \simplexml_load_string(
            '
        <xml>
            <headers cache_expiration="10" allow_credentials="1" cors_max_age="5" allowed_origins="https://www.google.com" allowed_request_headers="X-Custom-Header, Upgrade-Insecure-Requests" allowed_response_headers="Content-Length, X-Kuma-Revision"/>
            <routes>
                <route id="index" allowed_methods="GET,POST"/>
                <route id="login" no_cache="1"/>
            </routes>
        </xml>
        '
        );
        $policy = (new PolicyLocator($xml, "index"))->getPolicy();
        $request = new Request(
            [
                "Origin"=>"https://www.google.com",
                "Access-Control-Request-Method"=>"POST",
                "Access-Control-Request-Headers"=>"X-Custom-Header, Upgrade-Insecure-Requests"
            ]
        );

        $this->validator = new CorsValidator($request, $policy, null);
    }

    public function getAllowedOrigin()
    {
        return (new Strings((string) $this->validator->getAllowedOrigin()))->assertEquals("https://www.google.com");
    }

    public function getMaxAge()
    {
        return (new Integers((int) $this->validator->getMaxAge()))->assertEquals(5);
    }

    public function isAllowCredentials()
    {
        return (new Booleans($this->validator->isAllowCredentials()))->assertTrue();
    }

    public function getAllowedMethods()
    {
        return (new Arrays($this->validator->getAllowedMethods()))->assertEquals(["GET", "POST"]);
    }

    public function getAllowedHeaders()
    {
        return (new Arrays($this->validator->getAllowedHeaders()))
            ->assertEquals(["X-Custom-Header", "Upgrade-Insecure-Requests"]);
    }

    public function getExposedHeaders()
    {
        return (new Arrays($this->validator->getExposedHeaders()))
            ->assertEquals(["Content-Length", "X-Kuma-Revision"]);
    }
}
