<?php
namespace Lucinda\Headers;

/**
 * Binds XML to request received from client in order to be able to perform cache or CORS validation later on and set response headers accordingly
 */
class Wrapper
{
    private $policy;
    private $request;
    private $response;
    
    /**
     * Detects headers policy from XML, encapsulates headers received from client and makes it possible to set response headers
     *
     * @param \SimpleXMLElement $xml
     * @param string $requestedPage
     * @param array $requestHeaders
     */
    public function __construct(\SimpleXMLElement $xml, string $requestedPage, array $requestHeaders)
    {
        // detects header policies from XML
        $cpl = new PolicyLocator($xml, $requestedPage);
        $this->policy = $cpl->getPolicy();
        
        // set request object based on headers received from client
        $this->request = new Request($requestHeaders);
        
        // sets response object encapsulating headers to send back to client
        $this->response = new Response();
    }
    
    /**
     * Gets encapsulated HTTP request headers received from client
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
    
    /**
     * Performs HTTP cache validation based on policy detected, request headers and http method, sets response headers accordingly and returns response HTTP status
     *
     * @param Cacheable $cacheable
     * @param string $requestMethod
     * @return int
     */
    public function validateCache(Cacheable $cacheable, string $requestMethod): int
    {
        $httpStatus = 200;
        if (!$this->policy->getCachingDisabled()) {
            // performs cache validation
            $validator = new CacheValidator($this->request);
            $httpStatus = $validator->validate($cacheable, $requestMethod);
            
            // updates response headers
            $cacheControl = $this->response->setCacheControl();
            $cacheControl->setPublic(); // fix against session usage
            if ($expiration = $this->policy->getExpirationPeriod()) {
                $cacheControl->setMaxAge($expiration);
            }
            if ($etag = $cacheable->getEtag()) {
                $this->response->setEtag($etag);
            }
            if ($time = $cacheable->getTime()) {
                $this->response->setLastModifiedTime($time);
            }
        }
        return $httpStatus;
    }
    
    /**
     * Performs HTTP cache validation based on policy detected, request headers and origin uri and sets response headers accordingly
     *
     * @param string $origin
     */
    public function validateCors(string $origin = null): void
    {
        if ($this->request->getOrigin()) {
            $this->response->setAccessControlAllowOrigin($origin?$origin:"*");
            if ($this->policy->getCredentialsAllowed()) {
                $this->response->setAccessControlAllowCredentials();
            }
        }
        if ($requestMethod = $this->request->getAccessControlRequestMethod()) {
            $methods = $this->policy->getAllowedMethods();
            if (!empty($methods)) {
                foreach ($methods as $method) {
                    $this->response->addAccessControlAllowMethod($method);
                }
            } else {
                $this->response->addAccessControlAllowMethod($requestMethod);
            }
        }
        if ($this->request->getAccessControlRequestHeaders()) {
            $headers = $this->policy->getAllowedRequestHeaders();
            if (!empty($headers)) {
                foreach ($headers as $header) {
                    $this->response->addAccessControlAllowHeaders($header);
                }
            } else {
                $this->response->addAccessControlAllowHeaders("*");
            }
        }
        if ($headers = $this->policy->getAllowedResponseHeaders()) {
            foreach ($headers as $header) {
                $this->response->addAccessControlExposeHeaders($header);
            }
        }
        if ($duration = $this->policy->getCorsMaxAge()) {
            $this->response->setAccessControlMaxAge($duration);
        }
    }
    
    /**
     * Gets encapsulated HTTP response headers to send back
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
