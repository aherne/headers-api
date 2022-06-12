<?php

namespace Test\Lucinda\Headers;

use Lucinda\Headers\PolicyLocator;
use Lucinda\UnitTest\Result;

class PolicyLocatorTest
{
    public function getPolicy()
    {
        $locator = new PolicyLocator(
            \simplexml_load_string(
                '
<xml>
    <headers allowed_request_headers="X-Custom-Header, Upgrade-Insecure-Requests"/>
    <routes>
        <route id="login" allowed_methods="GET,POST"/>
    </routes>
</xml>
'
            ),
            "login"
        );
        $policy = $locator->getPolicy();
        return new Result($policy->getAllowedMethods()==["GET","POST"] && $policy->getAllowedRequestHeaders()==["X-Custom-Header", "Upgrade-Insecure-Requests"]);
    }
}
