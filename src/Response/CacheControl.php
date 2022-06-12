<?php

namespace Lucinda\Headers\Response;

/**
 * Encapsulates HTTP response header: Cache-Control
 */
class CacheControl
{
    private bool $public = false;
    private bool $private = false;
    private bool $noCache = false;
    private bool $noStore = false;
    private bool $noTransform = false;
    private bool $mustRevalidate = false;
    private ?int $maxAge = null;
    private bool $proxyRevalidate = false;
    private ?int $proxyMaxAge = null;

    /**
     * Indicates that the response MAY be cached by any cache, even if it would normally be non-cacheable or
     * cacheable only within a non- shared cache
     */
    public function setPublic(): void
    {
        if ($this->private) {
            return;
        }
        $this->public = true;
    }

    /**
     * Indicates that all or part of the response message is intended for a single user and MUST NOT be cached
     * by a shared cache.
     */
    public function setPrivate(): void
    {
        if ($this->public) {
            return;
        }
        $this->private = true;
    }

    /**
     * Indicates that cache MUST NOT use the response to satisfy a subsequent request without successful
     * revalidation with the origin server. This allows an origin server to prevent caching even by caches
     * that have been configured to return stale responses to client requests.
     */
    public function setNoCache(): void
    {
        $this->noCache = true;
    }

    /**
     * The cache should not store anything about the client request or server response (to enforce privacy).
     */
    public function setNoStore(): void
    {
        $this->noStore = true;
    }

    /**
     * The cache must verify the status of the stale resources before using it and expired ones should not be used.
     */
    public function setMustRevalidate(): void
    {
        $this->mustRevalidate = true;
    }
    /**
     * Specifies the maximum amount of time a resource will be considered fresh compared to time of request.
     * The max-age directive on a response implies that the response is cacheable (i.e., "public") unless
     * some other, more restrictive cache directive is also present.
     *
     * @param integer $seconds
     */
    public function setMaxAge(int $seconds): void
    {
        $this->maxAge = $seconds;
    }

    /**
     * PROXY: No transformations or conversions should be made to the resource. The Content-Encoding,
     * Content-Range, Content-Type headers must not be modified by a proxy
     */
    public function setNoTransform(): void
    {
        $this->noTransform = true;
    }

    /**
     * PROXY: Same as must-revalidate, but it only applies to shared caches (e.g., proxies) and is ignored by
     * a private cache.
     */
    public function setProxyRevalidate(): void
    {
        $this->proxyRevalidate = true;
    }

    /**
     * PROXY: Overrides max-age or the expires header, but it only applies to shared caches (e.g., proxies) and
     * is ignored by a private cache.
     *
     * @param integer $seconds
     */
    public function setProxyMaxAge(int $seconds): void
    {
        $this->proxyMaxAge = $seconds;
    }

    /**
     * Gets string representation of header value
     *
     * @return string
     */
    public function toString(): string
    {
        $cacheControl = [];
        if ($this->public) {
            $cacheControl[]="public";
        }
        if ($this->private) {
            $cacheControl[]="private";
        }
        if ($this->noCache) {
            $cacheControl[]="no-cache";
        }
        if ($this->noStore) {
            $cacheControl[]="no-store";
        }
        if ($this->noTransform) {
            $cacheControl[]="no-transform";
        }
        if ($this->mustRevalidate) {
            $cacheControl[]="must-revalidate";
        }
        if ($this->proxyRevalidate) {
            $cacheControl[]="proxy-revalidate";
        }
        if ($this->maxAge) {
            $cacheControl[]="max-age=".$this->maxAge;
        }
        if ($this->proxyMaxAge) {
            $cacheControl[]="s-maxage=".$this->proxyMaxAge;
        }
        return implode(", ", $cacheControl);
    }
}
