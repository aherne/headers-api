<?php
namespace Lucinda\Headers;

use Lucinda\Headers\Request\CacheControl;

/**
 * Performs validation of Cacheable representation of requested resource based on headers encapsulated by Request already.
 */
class CacheValidator
{
    private $request;
    
    /**
     * Constructs a cache validator.
     *
     * @param Request $request
     */
    public function __construct(Request $request): void
    {
        $this->request = $request;
    }
    
    /**
     * Validates resource according to IETF specifications.
     *
     * @param Cacheable $cacheable Cached representation of requested resource.
     * @return integer HTTP status code
     */
    public function validate(Cacheable $cacheable, string $requestMethod): int
    {
        $cacheControl = $this->request->getCacheControl();
        
        if ($cacheControl!=null && ($cacheControl->isNoCache() || $cacheControl->isNoStore())) {
            return 200;
        }
        
        $statusCode = $this->checkConditionals($cacheable, $requestMethod);
        
        if ($cacheControl!=null && $statusCode==304 && $this->checkCacheControl($cacheable, $cacheControl)) {
            $statusCode = 200;
        }
        
        return $statusCode;
    }
    
    /**
     * Matches If-Match, If-None-Match, If-Modified-Since, If-Unmodified-Since request headers to Cacheable and returns resulting http status code.
     * 
     * @param Cacheable $cacheable
     * @param string $requestMethod
     * @return int
     */
    private function checkConditionals(Cacheable $cacheable, string $requestMethod): int
    {
        $etag = $cacheable->getEtag();
        $date = $cacheable->getTime();
        
        // apply If-Match
        $ifMatch = $this->request->getIfMatch();
        if ($ifMatch) {
            if(!$etag) {
                return 412;
            } else if ($ifMatch == "*" || $etag == $ifMatch) {
                return 200;
            } else {
                return 412;
            }
        }
        
        // apply If-None-Match
        $ifNoneMatch = $this->request->getIfNoneMatch();
        if ($ifNoneMatch) {
            if (!$etag || !in_array($requestMethod, ["GET","HEAD"])) {
                return 412;
            } else if ($ifNoneMatch == "*" || $ifNoneMatch != $etag) {
                return 200;
            } else {
                return 304;
            }
        }
        
        // apply If-Unmodified-Since
        $ifUnmodifiedSince = $this->request->getIfUnmodifiedSince();
        if ($ifUnmodifiedSince) {
            if (!$date || $date>$ifUnmodifiedSince) {
                return 412;
            } else {
                return 200;
            }
        }
        
        // apply If-Modified-Since
        $ifModifiedSince = $this->request->getIfModifiedSince();
        if ($ifModifiedSince && in_array($requestMethod, ["GET","HEAD"])) {
            if(!$date) {
                return 412;
            } else if ($date>$ifModifiedSince) {
                return 200;
            } else {
                return 304;
            }
        }
        
        return 200;
    }
    
    /**
     * Matches Cache-Control request header to Cacheable to see if 304 HTTP status response should actually be HTTP status 200
     * 
     * @param Cacheable $cacheable
     * @param CacheControl $cacheControl
     * @return bool
     */
    private function checkCacheControl(Cacheable $cacheable, CacheControl $cacheControl): bool
    {
        $date = $cacheable->getTime();
        if (!$date) {
            // if resource has no time representation, ignore: max-age, max-stale, min-fresh 
            return false;
        }
        
        $age = time() - $date;
        
        $maxAge = $cacheControl->getMaxAge();        
        if ($maxAge!==null && ($maxAge == -1 || $age > $maxAge)) {
            return true;
        }
        
        $freshness = ($maxAge?$maxAge:0);
        $staleness = ($age <= $freshness?0:$age - $freshness);
        
        $maxStaleAge = $cacheControl->getMaxStaleAge();
        if ($maxStaleAge!==null && ($maxStaleAge == -1 || $maxStaleAge > $staleness || $freshness > $maxStaleAge)) {
            return true;
        }
        
        $minFreshAge = $cacheControl->getMinFreshAge();
        if ($minFreshAge!==null && ($minFreshAge == -1 || ($freshness - $age) < $minFreshAge)) {
            return true;
        }
        
        return false;
    }
}
