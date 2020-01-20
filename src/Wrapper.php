<?php
namespace Lucinda\Headers;

class Wrapper
{
    private $requestMethod;
    private $cachingPolicy;
    private $request;
    private $response;
    
    public function __construct(\SimpleXMLElement $xml, string $requestedPage, string $requestMethod, array $requestHeaders)
    {
        $this->requestMethod = $requestMethod;
        
        // detects caching_policy and performs cache validation
        $cpl = new CachingPolicyLocator($xml, $requestedPage);
        $this->cachingPolicy = $cpl->getPolicy();
        
        // set request and response objects
        $this->request = new Request($requestHeaders);
        $this->response = new Response();
    }
    
    public function getRequest(): Request
    {
        return $this->request;
    }
    
    public function validate(Cacheable $cacheable): int
    {
        $httpStatus = 200;
        if (!$this->cachingPolicy->isCachingDisabled()) {
            // performs cache validation
            $validator = new CacheValidator($this->request);
            $httpStatus = $validator->validate($cacheable, $this->requestMethod);
            
            // updates response headers
            $cacheControl = $this->response->setCacheControl();
            $cacheControl->setPublic(); // fix against session usage
            if ($etag = $cacheable->getEtag()) {
                $this->response->setEtag($etag);
            }
            if ($time = $cacheable->getTime()) {
                $this->response->setLastModifiedTime($time);
            }
            if ($expiration = $this->cachingPolicy->getExpirationPeriod()) {
                $cacheControl->setMaxAge($expiration);
            }
        }
        return $httpStatus;
    }
    
    public function getResponse(): Response
    {
        return $this->response;
    }
}

