<?php
namespace Lucinda\Headers;

class PreflightWrapper
{
    private $request;
    private $response;
    
    public function __construct(\SimpleXMLElement $xml, array $requestHeaders)
    {
        $this->request = new PreflightRequest($requestHeaders);
        /**
    private $allowCredentials;
    private $allowHeaders = []; // <> Access-Control-Request-Headers
    private $allowMethods = [];
    private $allowOrigin; // <> Origin
    private $exposeHeaders = [];
    private $maxAge;
         */
        $this->response = new PreflightResponse();
    }
    
    public function getRequest(): PreflightRequest
    {
        return $this->request;
    }
    
    public function getResponse(): PreflightResponse
    {
        return $this->response;
    }
}

