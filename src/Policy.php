<?php
namespace Lucinda\Headers;

/**
 * Encapsulates HTTP header policies for your application
 */
class Policy
{
    private $no_cache = false;
    private $expires;
    
    private $allowedCredentials = false;
    private $allowedMethods = [];
    private $allowedRequestHeaders = [];
    private $allowedResponseHeaders = [];
    private $corsMaxAge;
    
    /**
     * Sets whether or not caching is disabled based on value of "no_cache" XML attribute
     *
     * @param \SimpleXMLElement $xml
     */
    public function setCachingDisabled(\SimpleXMLElement $xml): void
    {
        if ($xml["no_cache"]!==null) {
            $this->no_cache = ((string) $xml["no_cache"]?true:false);
        }
    }
    
    /**
     * Checks whether or not caching is disabled
     *
     * @return boolean Possible values: TRUE, FALSE
     */
    public function getCachingDisabled(): bool
    {
        return $this->no_cache;
    }
    
    /**
     * Sets value to set Cache-Control max-age directive based on value of "cache_expiration" XML attribute
     *
     * @param \SimpleXMLElement $xml
     */
    public function setExpirationPeriod(\SimpleXMLElement $xml): void
    {
        if ($xml["cache_expiration"]!==null) {
            $this->expires = (integer) $xml["cache_expiration"];
        }
    }
    
    /**
     * Gets value to set Cache-Control max-age directive
     *
     * @return integer|null Possible values: an unsigned integer (seconds) or NULL (which means UNKNOWN)
     */
    public function getExpirationPeriod(): ?int
    {
        return $this->expires;
    }
    
    /**
     * Sets whether or not CORS Access-Control-Allow-Credentials should be activated based on value of "allow_credentials" XML attribute
     *
     * @param \SimpleXMLElement $xml
     */
    public function setCredentialsAllowed(\SimpleXMLElement $xml): void
    {
        if ($xml["allow_credentials"]!==null) {
            $this->allowedCredentials = ((string) $xml["allow_credentials"]?1:0);
        }
    }
    
    /**
     * Gets whether or not CORS Access-Control-Allow-Credentials should be activated
     *
     * @return boolean Possible values: TRUE, FALSE
     */
    public function getCredentialsAllowed(): bool
    {
        return $this->allowedCredentials;
    }
    
    /**
     * Sets value to set CORS Access-Control-Allow-Methods based on value of "allowed_methods" XML attribute
     *
     * @param \SimpleXMLElement $xml
     */
    public function setAllowedMethods(\SimpleXMLElement $xml): void
    {
        if ($xml["allowed_methods"]!==null) {
            $matches = [];
            preg_match_all("/\s*([^,]+)\s*/", (string) $xml["allowed_methods"], $matches);
            if (empty($matches)) {
                return;
            }
            foreach ($matches[0] as $value) {
                $value = trim($value);
                if (in_array($value, ["GET","HEAD","POST","PUT","DELETE","CONNECT","OPTIONS","TRACE","PATCH"])) {
                    $this->allowedMethods[] = $value;
                }
            }
        }
    }
    
    /**
     * Gets values to set CORS Access-Control-Allow-Methods
     *
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }
    
    /**
     * Sets value to set CORS Access-Control-Allow-Headers based on value of "allowed_request_headers" XML attribute
     *
     * @param \SimpleXMLElement $xml
     */
    public function setAllowedRequestHeaders(\SimpleXMLElement $xml): void
    {
        if ($xml["allowed_request_headers"]!==null) {
            $matches = [];
            preg_match_all("/\s*([^,]+)\s*/", (string) $xml["allowed_request_headers"], $matches);
            if (empty($matches)) {
                return;
            }
            foreach ($matches[0] as $value) {
                $value = trim($value);
                if ($value) {
                    $this->allowedRequestHeaders[] = $value;
                }
            }
        }
    }
    
    /**
     * Gets values to set CORS Access-Control-Allow-Headers
     *
     * @return array
     */
    public function getAllowedRequestHeaders(): array
    {
        return $this->allowedRequestHeaders;
    }
    
    /**
     * Sets value to set CORS Access-Control-Expose-Headers based on value of "allowed_response_headers" XML attribute
     *
     * @param \SimpleXMLElement $xml
     */
    public function setAllowedResponseHeaders(\SimpleXMLElement $xml): void
    {
        if ($xml["allowed_response_headers"]!==null) {
            $matches = [];
            preg_match_all("/\s*([^,]+)\s*/", (string) $xml["allowed_response_headers"], $matches);
            if (empty($matches)) {
                return;
            }
            foreach ($matches[0] as $value) {
                $value = trim($value);
                if ($value) {
                    $this->allowedResponseHeaders[] = $value;
                }
            }
        }
    }
    
    /**
     * Gets values to set CORS Access-Control-Expose-Headers
     *
     * @return array
     */
    public function getAllowedResponseHeaders(): array
    {
        return $this->allowedResponseHeaders;
    }
    
    /**
     * Sets value to set Access-Control-Max-Age later on based on value of "cors_max_age" XML attribute
     *
     * @param \SimpleXMLElement $xml
     */
    public function setCorsMaxAge(\SimpleXMLElement $xml): void
    {
        if ($xml["cors_max_age"]!==null) {
            $this->corsMaxAge = (integer) $xml["cors_max_age"];
        }
    }
    
    /**
     * Gets value to set Access-Control-Max-Age later on
     *
     * @return integer|null
     */
    public function getCorsMaxAge(): ?int
    {
        return $this->corsMaxAge;
    }
}
