<?php

namespace Lucinda\Headers;

use Lucinda\Headers\Request\Method;

/**
 * Encapsulates HTTP header policies for your application
 */
class Policy
{
    private bool $noCache = false;
    private ?int $expires = null;

    private bool $allowedCredentials = false;

    /**
     * @var string[]
     */
    private array $allowedMethods = [];
    /**
     * @var string[]
     */
    private array $allowedRequestHeaders = [];
    /**
     * @var string[]
     */
    private array $allowedResponseHeaders = [];
    private ?int $corsMaxAge = null;

    /**
     * Sets whether caching is disabled based on value of "no_cache" XML attribute
     *
     * @param \SimpleXMLElement $xml
     */
    public function setCachingDisabled(\SimpleXMLElement $xml): void
    {
        if ($xml["no_cache"]!==null) {
            $this->noCache = (bool)((string)$xml["no_cache"]);
        }
    }

    /**
     * Checks whether caching is disabled
     *
     * @return boolean Possible values: TRUE, FALSE
     */
    public function getCachingDisabled(): bool
    {
        return $this->noCache;
    }

    /**
     * Sets value to set Cache-Control max-age directive based on value of "cache_expiration" XML attribute
     *
     * @param \SimpleXMLElement $xml
     */
    public function setExpirationPeriod(\SimpleXMLElement $xml): void
    {
        if ($xml["cache_expiration"]!==null) {
            $this->expires = (int) $xml["cache_expiration"];
        }
    }

    /**
     * Gets value to set Cache-Control max-age directive
     *
     * @return int|null Possible values: an unsigned integer (seconds) or NULL (which means UNKNOWN)
     */
    public function getExpirationPeriod(): ?int
    {
        return $this->expires;
    }

    /**
     * Sets whether or not CORS Access-Control-Allow-Credentials should be activated based on value of
     * "allow_credentials" XML attribute
     *
     * @param \SimpleXMLElement $xml
     */
    public function setCredentialsAllowed(\SimpleXMLElement $xml): void
    {
        if ($xml["allow_credentials"]!==null) {
            $this->allowedCredentials = (bool)((string)$xml["allow_credentials"]);
        }
    }

    /**
     * Gets whether CORS Access-Control-Allow-Credentials should be activated
     *
     * @return bool Possible values: TRUE, FALSE
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
                if (Method::tryFrom($value)) {
                    $this->allowedMethods[] = $value;
                }
            }
        }
    }

    /**
     * Gets values to set CORS Access-Control-Allow-Methods
     *
     * @return string[]
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
     * @return string[]
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
     * @return string[]
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
            $this->corsMaxAge = (int) $xml["cors_max_age"];
        }
    }

    /**
     * Gets value to set Access-Control-Max-Age later on
     *
     * @return int|null
     */
    public function getCorsMaxAge(): ?int
    {
        return $this->corsMaxAge;
    }
}
