<?php
namespace Lucinda\Headers\Request;

/**
 * Encapsulates value of HTTP request header: Authorization
 */
class Authorization
{
    private string $type;
    private string $credentials;
    
    /**
     * Parses header value
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $position = strpos($value, " ");
        if ($position === false) {
            return;
        }
        $this->type = substr($value, 0, $position);
        $this->credentials = trim(substr($value, $position+1));
    }
    
    /**
     * Gets authorization type (usually: basic)
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
    
    /**
     * Gets authorization credentials (usually: some token)
     *
     * @return string
     */
    public function getCredentials(): string
    {
        return $this->credentials;
    }
}
