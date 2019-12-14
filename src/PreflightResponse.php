<?php
namespace Lucinda\Headers;

/**
 * Encapsulates response headers for a preflight request that came along with an OPTIONS method
 */
class PreflightResponse
{
    private $allowCredentials;
    private $allowHeaders = []; // <> Access-Control-Request-Headers
    private $allowMethods = [];
    private $allowOrigin; // <> Origin
    private $exposeHeaders = [];
    private $maxAge;
    
    /**
     * Sets value of HTTP header: Access-Control-Allow-Credentials
     */
    public function setAccessControlAllowCredentials(): void
    {
        $this->allowCredentials = "true";
    }
    
    /**
     * Sets value of HTTP header: Access-Control-Allow-Headers
     *
     * @param string $headerName
     */
    public function setAccessControlAllowHeaders(string $headerName = "*"): void
    {
        $this->allowHeaders[] = $headerName;
    }
    
    /**
     * Sets value of HTTP header: Access-Control-Allow-Method
     *
     * @param string $requestMethod
     */
    public function setAccessControlAllowMethod(string $requestMethod): void
    {
        if (!in_array($requestMethod, ["GET","HEAD","POST","PUT","DELETE","CONNECT","OPTIONS","TRACE","PATCH"])) {
            return;
        }
        $this->allowMethods[] = $requestMethod;
    }
    
    /**
     * Sets value of HTTP header: Access-Control-Allow-Origin
     *
     * @param string $origin
     */
    public function setAccessControlAllowOrigin(string $origin = "*"): void
    {
        $this->allowOrigin = $origin;
    }
    
    /**
     * Sets value of HTTP header: Access-Control-Expose-Headers
     *
     * @param string $headerName
     */
    public function setAccessControlExposeHeaders(string $headerName = "*"): void
    {
        $this->exposeHeaders[] = $headerName;
    }
    
    /**
     * Sets value of HTTP header: Access-Control-Max-Age
     *
     * @param int $duration
     */
    public function setAccessControlMaxAge(int $duration): void
    {
        $this->maxAge = $duration;
    }
    
    /**
     * Gets all response headers as key-value pairs.
     *
     * @return string[string]
     */
    public function toArray(): array
    {
        $headers = [];
        if ($this->allowCredentials) {
            $headers["Access-Control-Allow-Credentials"] = $this->allowCredentials;
        }
        // indicate which headers can be used during the actual request.
        if ($this->allowHeaders) {
            $headers["Access-Control-Allow-Headers"] = implode(", ", $this->allowHeaders);
        }
        if ($this->allowMethods) {
            $headers["Access-Control-Allow-Methods"] = implode(", ", $this->allowMethods);
        }
        if ($this->allowOrigin) {
            $headers["Access-Control-Allow-Origin"] = $this->allowOrigin;
        }
        // indicates which headers can be exposed as part of the response
        if ($this->exposeHeaders) {
            $headers["Access-Control-Expose-Headers"] = implode(", ", $this->exposeHeaders);
        }
        if ($this->maxAge) {
            $headers["Access-Control-Max-Age"] = $this->maxAge;
        }
        return $headers;
    }
}
