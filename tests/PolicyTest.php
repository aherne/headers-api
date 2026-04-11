<?php

namespace Test\Lucinda\Headers;

use Lucinda\Headers\Policy;
use Lucinda\UnitTest\Validator\Arrays;
use Lucinda\UnitTest\Validator\Booleans;
use Lucinda\UnitTest\Validator\Integers;

class PolicyTest
{
    private Policy $object;
    private \SimpleXMLElement $xmlHeaders;
    private \SimpleXMLElement $xmlRoutes;

    public function __construct()
    {
        $this->object = new Policy();
        $this->xmlHeaders = \simplexml_load_string(
            '
        <headers no_cache="1" cache_expiration="10" allow_credentials="1" cors_max_age="5" allowed_origins="https://www.google.com, https://www.github.com" allowed_request_headers="X-Custom-Header, Upgrade-Insecure-Requests" allowed_response_headers="Content-Length, X-Kuma-Revision"/>
        '
        );
        $this->xmlRoutes = \simplexml_load_string(
            '
        <route id="index" allowed_methods="GET,POST" allowed_origins="https://route.example.com"/>
        '
        );
    }

    public function setCachingDisabled()
    {
        $this->object->setCachingDisabled($this->xmlHeaders);
        return (new Booleans(true))->assertTrue();
    }

    public function getCachingDisabled()
    {
        return (new Booleans($this->object->getCachingDisabled()))->assertTrue();
    }

    public function setExpirationPeriod()
    {
        $this->object->setExpirationPeriod($this->xmlHeaders);
        return (new Booleans(true))->assertTrue();
    }

    public function getExpirationPeriod()
    {
        return (new Integers((int) $this->object->getExpirationPeriod()))->assertEquals(10);
    }

    public function setCredentialsAllowed()
    {
        $this->object->setCredentialsAllowed($this->xmlHeaders);
        return (new Booleans(true))->assertTrue();
    }

    public function getCredentialsAllowed()
    {
        return (new Booleans($this->object->getCredentialsAllowed()))->assertTrue();
    }

    public function setAllowedMethods()
    {
        $this->object->setAllowedMethods($this->xmlRoutes);
        return (new Booleans(true))->assertTrue();
    }

    public function getAllowedMethods()
    {
        return (new Arrays($this->object->getAllowedMethods()))->assertEquals(["GET", "POST"]);
    }

    public function setAllowedOrigins()
    {
        $this->object->setAllowedOrigins($this->xmlHeaders);
        return (new Booleans(true))->assertTrue();
    }

    public function getAllowedOrigins()
    {
        return (new Arrays($this->object->getAllowedOrigins()))
            ->assertEquals(["https://www.google.com", "https://www.github.com"]);
    }

    public function setAllowedRequestHeaders()
    {
        $this->object->setAllowedRequestHeaders($this->xmlHeaders);
        return (new Booleans(true))->assertTrue();
    }

    public function getAllowedRequestHeaders()
    {
        return (new Arrays($this->object->getAllowedRequestHeaders()))
            ->assertEquals(["X-Custom-Header", "Upgrade-Insecure-Requests"]);
    }

    public function setAllowedResponseHeaders()
    {
        $this->object->setAllowedResponseHeaders($this->xmlHeaders);
        return (new Booleans(true))->assertTrue();
    }

    public function getAllowedResponseHeaders()
    {
        return (new Arrays($this->object->getAllowedResponseHeaders()))
            ->assertEquals(["Content-Length", "X-Kuma-Revision"]);
    }

    public function setCorsMaxAge()
    {
        $this->object->setCorsMaxAge($this->xmlHeaders);
        return (new Booleans(true))->assertTrue();
    }

    public function getCorsMaxAge()
    {
        return (new Integers((int) $this->object->getCorsMaxAge()))->assertEquals(5);
    }
}
