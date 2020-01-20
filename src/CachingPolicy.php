<?php
namespace Lucinda\Headers;

/**
 * Encapsulates basic HTTP caching policies rules.
 */
class CachingPolicy
{
    private $no_cache;
    private $expires;
    
    public function __construct(\SimpleXMLElement $xml)
    {
        if ($xml["no_cache"]!==null) {
            $this->no_cache = ((string) $xml["no_cache"]?true:false);
        }
        if ($xml["expiration"]!==null) {
            $this->expires = (integer) $xml["expiration"];
        }
    }
    
    /**
     * Checks whether or not caching is disabled.
     *
     * @return boolean Possible values: TRUE, FALSE or NULL (which means UNKNOWN)
     */
    public function isCachingDisabled(): ?bool
    {
        return $this->no_cache;
    }
    
    /**
     * Gets period from original server response by which entry expires in local (browser) cache.
     *
     * @return integer Possible values: an unsigned integer (seconds) or NULL (which means UNKNOWN)
     */
    public function getExpirationPeriod(): ?int
    {
        return $this->expires;
    }
}
