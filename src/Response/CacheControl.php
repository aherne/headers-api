<?php
namespace Lucinda\Headers\Response;

/**
 * Encapsulates HTTP response header: Cache-Control
 */
class CacheControl
{
    private $public;
    private $private;
    private $no_cache;
    private $no_store;
    private $no_transform;
    private $must_revalidate;
    private $max_age;
    private $proxy_age;
    private $proxy_revalidate;
    private $proxy_max_age;
    
    /**
     * Indicates that the response MAY be cached by any cache, even if it would normally be non-cacheable or cacheable only within a non- shared cache
     */
    public function setPublic(): void
    {
        if ($this->private) {
            return;
        }
        $this->public = true;
    }
    
    /**
     * Indicates that all or part of the response message is intended for a single user and MUST NOT be cached by a shared cache.
     */
    public function setPrivate(): void
    {
        if ($this->public) {
            return;
        }
        $this->private = true;
    }
    
    /**
     * Indicates that cache MUST NOT use the response to satisfy a subsequent request without successful revalidation with the origin server.
     * This allows an origin server to prevent caching even by caches that have been configured to return stale responses to client requests.
     */
    public function setNoCache(): void
    {
        $this->no_cache = true;
    }
    
    /**
     * The cache should not store anything about the client request or server response (to enforce privacy).
     */
    public function setNoStore(): void
    {
        $this->no_store = true;
    }
    
    /**
     * The cache must verify the status of the stale resources before using it and expired ones should not be used.
     */
    public function setMustRevalidate(): void
    {
        $this->must_revalidate = true;
    }
    /**
     * Specifies the maximum amount of time a resource will be considered fresh compared to time of request.
     * The max-age directive on a response implies that the response is cacheable (i.e., "public") unless some other,
     * more restrictive cache directive is also present.
     *
     * @param integer $seconds
     */
    public function setMaxAge(int $seconds): void
    {
        $this->max_age = $seconds;
    }
    
    /**
     * PROXY: Sets time object has been in proxy cache. A cached response is "fresh" if its age does not exceed its freshness lifetime.
     *
     * @param integer $seconds Usually 0, which means it was just retrieved from proxy.
     */
    public function setProxyAge(int $seconds): void
    {
        $this->proxy_age = $seconds;
    }
    
    /**
     * PROXY: No transformations or conversions should be made to the resource. The Content-Encoding, Content-Range, Content-Type headers must not be modified by a proxy
     */
    public function setNoTransform(): void
    {
        $this->no_transform = true;
    }
    
    /**
     * PROXY: Same as must-revalidate, but it only applies to shared caches (e.g., proxies) and is ignored by a private cache.
     */
    public function setProxyRevalidate(): void
    {
        $this->proxy_revalidate = true;
    }
    
    /**
     * PROXY: Overrides max-age or the expires header, but it only applies to shared caches (e.g., proxies) and is ignored by a private cache.
     *
     * @param integer $seconds
     */
    public function setProxyMaxAge(int $seconds): void
    {
        $this->proxy_max_age = $seconds;
    }
    
    /**
     * Gets string representation of header value
     *
     * @return string
     */
    public function toString(): string
    {
        $cache_control = array();
        if ($this->public) {
            $cache_control[]="public";
        }
        if ($this->private) {
            $cache_control[]="private";
        }
        if ($this->no_cache) {
            $cache_control[]="no-cache";
        }
        if ($this->no_store) {
            $cache_control[]="no-store";
        }
        if ($this->no_transform) {
            $cache_control[]="no-transform";
        }
        if ($this->must_revalidate) {
            $cache_control[]="must-revalidate";
        }
        if ($this->proxy_revalidate) {
            $cache_control[]="proxy-revalidate";
        }
        if ($this->max_age) {
            $cache_control[]="max-age=".$this->max_age;
        }
        if ($this->proxy_max_age) {
            $cache_control[]="s-maxage=".$this->proxy_max_age;
        }
        return implode(", ", $cache_control);
    }
}
