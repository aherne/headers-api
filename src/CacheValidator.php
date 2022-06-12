<?php

namespace Lucinda\Headers;

use Lucinda\Headers\Request\CacheControl;

/**
 * Performs validation of Cacheable representation of requested resource based on headers encapsulated by
 * Request already.
 */
class CacheValidator
{
    private Request $request;

    /**
     * Constructs a cache validator.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Validates resource according to IETF specifications.
     *
     * @param  Cacheable $cacheable Cached representation of requested resource.
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
     * Matches If-Match, If-None-Match, If-Modified-Since, If-Unmodified-Since request headers to Cacheable and
     * returns resulting http status code.
     *
     * @param  Cacheable $cacheable
     * @param  string    $requestMethod
     * @return int
     */
    private function checkConditionals(Cacheable $cacheable, string $requestMethod): int
    {
        $etag = $cacheable->getEtag();
        $date = $cacheable->getTime();

        if ($httpCode = $this->checkIfMatch($etag)) {
            return $httpCode;
        } elseif ($httpCode = $this->checkIfNoneMatch($etag, $requestMethod)) {
            return $httpCode;
        } elseif ($httpCode = $this->checkIfUnmodifiedSince($date)) {
            return $httpCode;
        } elseif ($httpCode = $this->checkIfModifiedSince($date, $requestMethod)) {
            return $httpCode;
        } else {
            return 200;
        }
    }

    /**
     * Matches Cache-Control request header to Cacheable to see if 304 HTTP status response should actually be
     * HTTP status 200
     *
     * @param  Cacheable    $cacheable
     * @param  CacheControl $cacheControl
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
        $freshness = ($maxAge ? $maxAge : 0);
        $staleness = ($age <= $freshness ? 0 : $age - $freshness);

        if ($this->checkMaxAge($maxAge, $age)
            || $this->checkMaxStaleAge($cacheControl->getMaxStaleAge(), $staleness, $freshness)
            || $this->checkMinFreshAge($cacheControl->getMinFreshAge(), $freshness, $age)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks If-Match request header and returns appropriate HTTP status code
     *
     * @param  string $etag
     * @return int|null
     */
    private function checkIfMatch(string $etag): ?int
    {
        $ifMatch = $this->request->getIfMatch();
        if ($ifMatch) {
            if (!$etag) {
                return 412;
            } elseif ($ifMatch == "*" || $etag == $ifMatch) {
                return 200;
            } else {
                return 412;
            }
        }
        return null;
    }

    /**
     * Checks If-None-Match request header and returns appropriate HTTP status code
     *
     * @param  string $etag
     * @param  string $requestMethod
     * @return int|null
     */
    private function checkIfNoneMatch(string $etag, string $requestMethod): ?int
    {
        $ifNoneMatch = $this->request->getIfNoneMatch();
        if ($ifNoneMatch) {
            if (!$etag || !in_array($requestMethod, ["GET","HEAD"])) {
                return 412;
            } elseif ($ifNoneMatch == "*" || $ifNoneMatch != $etag) {
                return 200;
            } else {
                return 304;
            }
        }
        return null;
    }

    /**
     * Checks If-Unmodified-Since request header and returns appropriate HTTP status code
     *
     * @param  int $date
     * @return int|null
     */
    private function checkIfUnmodifiedSince(int $date): ?int
    {
        $ifUnmodifiedSince = $this->request->getIfUnmodifiedSince();
        if ($ifUnmodifiedSince) {
            if (!$date || $date>$ifUnmodifiedSince) {// if modified since TIME
                return 412;
            } else { // if not modified since TIME
                return 200;
            }
        }
        return null;
    }

    /**
     * Checks If-Modified-Since request header and returns appropriate HTTP status code
     *
     * @param  int    $date
     * @param  string $requestMethod
     * @return int|null
     */
    private function checkIfModifiedSince(int $date, string $requestMethod): ?int
    {
        $ifModifiedSince = $this->request->getIfModifiedSince();
        if ($ifModifiedSince && in_array($requestMethod, ["GET","HEAD"])) {
            if (!$date) {
                return 412;
            } elseif ($date>$ifModifiedSince) { // if modified after TIME
                return 200;
            } elseif ($date==$ifModifiedSince) { // if modified at TIME
                return 304;
            } else { // if modified before TIME
                /**
                 * This is an error situation (header date should NEVER be newer than source date):
                 * answer with 200 OK to force cache refresh
                 */
                return 412;
            }
        }
        return null;
    }

    /**
     * Checks max-age directive in Cache-Control request header for a match
     *
     * @param  int|null $maxAge
     * @param  int      $age
     * @return bool
     */
    private function checkMaxAge(?int $maxAge, int $age): bool
    {
        return $maxAge!==null && ($maxAge == -1 || $age > $maxAge);
    }

    /**
     * Checks max-stale-age directive in Cache-Control request header for a match
     *
     * @param  int|null $maxStaleAge
     * @param  int      $staleness
     * @param  int      $freshness
     * @return bool
     */
    private function checkMaxStaleAge(?int $maxStaleAge, int $staleness, int $freshness): bool
    {
        return $maxStaleAge!==null && ($maxStaleAge == -1 || $maxStaleAge > $staleness || $freshness > $maxStaleAge);
    }

    /**
     * Checks min-fresh directive in Cache-Control request header for a match
     *
     * @param  int|null $minFreshAge
     * @param  int      $freshness
     * @param  int      $age
     * @return bool
     */
    private function checkMinFreshAge(?int $minFreshAge, int $freshness, int $age): bool
    {
        return $minFreshAge!==null && ($minFreshAge == -1 || ($freshness - $age) < $minFreshAge);
    }
}
