<?php

namespace Test\Lucinda\Headers;

use Lucinda\Headers\CorsValidator;
use Lucinda\Headers\Policy;
use Lucinda\Headers\PolicyLocator;
use Lucinda\Headers\Request;
use Lucinda\UnitTest\Result;

class CorsValidatorTest
{
    private CorsValidator $validator;

    public function __construct()
    {
        $xml = \simplexml_load_string(
            '
        <xml>
            <headers cache_expiration="10" allow_credentials="1" cors_max_age="5" allowed_request_headers="X-Custom-Header, Upgrade-Insecure-Requests" allowed_response_headers="Content-Length, X-Kuma-Revision"/>
            <routes>
                <route id="index" allowed_methods="GET,POST"/>
                <route id="login" no_cache="1"/>
            </routes>
        </xml>
        '
        );
        $cpl = new PolicyLocator($xml, "index");
        $policy = $cpl->getPolicy();

        $request = new Request(
            [
            "Origin"=>"https://www.google.com",
            "Access-Control-Request-Method"=>"POST",
            "Access-Control-Request-Headers"=>"X-Custom-Header, Upgrade-Insecure-Requests"
            ]
        );

        $this->validator = new CorsValidator($request, $policy, "https://www.google.com");
    }

    public function getAllowedOrigin()
    {
        return new Result($this->validator->getAllowedOrigin()=="https://www.google.com");
    }


    public function getMaxAge()
    {
        return new Result($this->validator->getMaxAge()==5);
    }


    public function isAllowCredentials()
    {
        return new Result($this->validator->isAllowCredentials());
    }


    public function getAllowedMethods()
    {
        return new Result($this->validator->getAllowedMethods()==["GET","POST"]);
    }


    public function getAllowedHeaders()
    {
        return new Result($this->validator->getAllowedHeaders()==["X-Custom-Header", "Upgrade-Insecure-Requests"]);
    }


    public function getExposedHeaders()
    {
        return new Result($this->validator->getExposedHeaders()==["Content-Length", "X-Kuma-Revision"]);
    }
}
