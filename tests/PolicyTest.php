<?php
namespace Test\Lucinda\Headers;

use Lucinda\Headers\Policy;
use Lucinda\UnitTest\Result;

class PolicyTest
{
    private $object;
    private $xml_headers;
    private $xml_routes;
    
    public function __construct()
    {
        $this->object = new Policy();
        $this->xml_headers = \simplexml_load_string('
        <headers no_cache="1" cache_expiration="10" allow_credentials="1" cors_max_age="5" allowed_request_headers="X-Custom-Header, Upgrade-Insecure-Requests" allowed_response_headers="Content-Length, X-Kuma-Revision"/>
        ');
        $this->xml_routes = \simplexml_load_string('
        <route url="index" allowed_methods="GET,POST"/>
        ');
    }

    public function setCachingDisabled()
    {
        $this->object->setCachingDisabled($this->xml_headers);
        return new Result(true);
    }
        

    public function getCachingDisabled()
    {
        return new Result($this->object->getCachingDisabled());
    }
        

    public function setExpirationPeriod()
    {
        $this->object->setExpirationPeriod($this->xml_headers);
        return new Result(true);
    }
        

    public function getExpirationPeriod()
    {
        return new Result($this->object->getExpirationPeriod()==10);
    }
        

    public function setCredentialsAllowed()
    {
        $this->object->setCredentialsAllowed($this->xml_headers);
        return new Result(true);
    }
        

    public function getCredentialsAllowed()
    {
        return new Result($this->object->getCredentialsAllowed());
    }
        

    public function setAllowedMethods()
    {
        $this->object->setAllowedMethods($this->xml_routes);
        return new Result(true);
    }
        

    public function getAllowedMethods()
    {
        return new Result($this->object->getAllowedMethods()==["GET","POST"]);
    }
        

    public function setAllowedRequestHeaders()
    {
        $this->object->setAllowedRequestHeaders($this->xml_headers);
        return new Result(true);
    }
        

    public function getAllowedRequestHeaders()
    {
        return new Result($this->object->getAllowedRequestHeaders()==["X-Custom-Header","Upgrade-Insecure-Requests"]);
    }
        

    public function setAllowedResponseHeaders()
    {
        $this->object->setAllowedResponseHeaders($this->xml_headers);
        return new Result(true);
    }
        

    public function getAllowedResponseHeaders()
    {
        return new Result($this->object->getAllowedResponseHeaders()==["Content-Length","X-Kuma-Revision"]);
    }
        

    public function setCorsMaxAge()
    {
        $this->object->setCorsMaxAge($this->xml_headers);
        return new Result(true);
    }
        

    public function getCorsMaxAge()
    {
        return new Result($this->object->getCorsMaxAge()==5);
    }
}
