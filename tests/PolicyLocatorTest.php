<?php

namespace Test\Lucinda\Headers;

use Lucinda\Headers\PolicyLocator;
use Lucinda\UnitTest\Validator\Arrays;

class PolicyLocatorTest
{
    public function getPolicy(): array
    {
        $locator = new PolicyLocator(
            \simplexml_load_string(
                '
<xml>
    <headers allowed_origins="https://www.google.com" allowed_request_headers="X-Custom-Header, Upgrade-Insecure-Requests"/>
    <routes>
        <route id="login" allowed_methods="GET,POST" allowed_origins="https://login.example.com"/>
    </routes>
</xml>
'
            ),
            "login"
        );
        $policy = $locator->getPolicy();

        return [
            (new Arrays($policy->getAllowedMethods()))->assertEquals(["GET", "POST"]),
            (new Arrays($policy->getAllowedOrigins()))->assertEquals(["https://login.example.com"]),
            (new Arrays($policy->getAllowedRequestHeaders()))
                ->assertEquals(["X-Custom-Header", "Upgrade-Insecure-Requests"])
        ];
    }
}
