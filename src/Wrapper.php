<?php

namespace Lucinda\Headers;

/**
 * Binds XML to request received from client in order to be able to perform cache or CORS validation later on and
 * set response headers accordingly
 */
class Wrapper
{
    private Policy $policy;
    private Request $request;
    private Response $response;

    /**
     * Detects headers policy from XML, encapsulates headers received from client and makes it possible to
     * set response headers
     *
     * @param \SimpleXMLElement    $xml
     * @param string               $requestedPage
     * @param array<string,string> $requestHeaders
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
     * Performs HTTP cache validation based on policy detected, request headers and http method, sets response
     * headers accordingly and returns response HTTP status
     *
     * @param  Cacheable $cacheable
     * @param  string    $requestMethod
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
     * Performs HTTP CORS validation based on policy detected, request headers and origin uri and sets
     * response headers accordingly
     *
     * @param  string|null $origin
     * @throws UserException
     */
    public function validateCors(string $origin = null): void
    {
        $validator = new CorsValidator($this->request, $this->policy, $origin);

        if ($allowedOrigin = $validator->getAllowedOrigin()) {
            $this->response->setAccessControlAllowOrigin($allowedOrigin);
        }

        if ($maxAge = $validator->getMaxAge()) {
            $this->response->setAccessControlMaxAge($maxAge);
        }

        if ($validator->isAllowCredentials()) {
            $this->response->setAccessControlAllowCredentials();
        }

        array_map(
            function ($value) {
                $this->response->addAccessControlAllowMethod($value);
            },
            $validator->getAllowedMethods()
        );

        array_map(
            function ($value) {
                $this->response->addAccessControlAllowHeader($value);
            },
            $validator->getAllowedHeaders()
        );

        array_map(
            function ($value) {
                $this->response->addAccessControlExposeHeader($value);
            },
            $validator->getExposedHeaders()
        );
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
