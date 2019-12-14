<?php
namespace Lucinda\Headers;

/**
 * Encapsulates preflight request headers that came along with an OPTIONS request method 
 */
class PreflightRequest
{
    private $accessControlRequestHeaders = [];
    private $accessControlRequestMethod;
    private $origin;
    
    /**
     * Reads headers received
     *
     * @param string[string] $headers
     */
    public function __construct(array $headers)
    {
        if (!empty($headers["Access-Control-Request-Headers"])) {
            $matches = [];
            preg_match_all("/\s*([^,]+)\s*/", $headers["Access-Control-Request-Headers"], $matches);
            $this->accessControlRequestHeaders = (!empty($matches[1])?$matches[1]:[]);
        }
        
        if (!empty($headers["Access-Control-Request-Method"])) {
            $value = trim($headers["Access-Control-Request-Method"]);
            if (in_array($value, ["GET","HEAD","POST","PUT","DELETE","CONNECT","OPTIONS","TRACE","PATCH"])) {
                $this->accessControlRequestMethod = $value;
            }
        }
        
        if (!empty($headers["Origin"])) {
            $this->origin = $value;
        }
    }
    
    /**
     * Gets value of HTTP header: Access-Control-Request-Headers
     *  
     * @return array
     */
    public function getAccessControlRequestHeaders(): array
    {
        return $this->accessControlRequestHeaders;
    }
    
    /**
     * Gets value of HTTP header: Access-Control-Request-Method
     *
     * @return string
     */
    public function getAccessControlRequestMethod(): string
    {
        return $this->accessControlRequestMethod;
    }
    
    /**
     * Gets value of HTTP header: Origin
     *
     * @return string
     */
    public function getOrigin(): string
    {
        return $this->origin;
    }
}

