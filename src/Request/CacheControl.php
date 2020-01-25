<?php
namespace Lucinda\Headers\Request;

/**
 * Encapsulates value of HTTP request header: Cache-Control
 */
class CacheControl
{
    private $no_cache = false;
    private $no_store = false;
    private $max_age;
    private $max_stale;
    private $min_fresh;
    
    /**
     * Parses header value
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $p1 = explode(",", $value);
        foreach ($p1 as $element) {
            $k = "";
            $v = "";
            $position = strpos($element, "=");
            if ($position) {
                $k = trim(substr($element, 0, $position));
                $v = trim(substr($element, $position+1));
            } else {
                $k = trim($element);
            }
            
            switch ($k) {
                case "no-cache":
                    $this->no_cache = true;
                    break;
                case "no-store":
                    $this->no_store = true;
                    break;
                case "max-age":
                    $this->max_age = (is_numeric($v)?(int) $v:null);
                    break;
                case "max-stale":
                    $this->max_stale = (is_numeric($v)?(int) $v:null);
                    break;
                case "min-fresh":
                    $this->min_fresh = (is_numeric($v)?(int) $v:null);
                    break;
            }
        }
    }
    
    /**
     * Checks if header came with directive: no-cache
     *
     * @return bool
     */
    public function isNoCache(): bool
    {
        return $this->no_cache;
    }
    
    /**
     * Checks if header came with directive: no-store
     *
     * @return bool
     */
    public function isNoStore(): bool
    {
        return $this->no_store;
    }
    
    /**
     * Gets value of directive: max-age
     *
     * @return int|null
     */
    public function getMaxAge(): ?int
    {
        return $this->max_age;
    }
    
    /**
     * Gets value of directive: max-stale
     *
     * @return int|null
     */
    public function getMaxStaleAge(): ?int
    {
        return $this->max_stale;
    }
    
    /**
     * Gets value of directive: min-fresh
     *
     * @return int|null
     */
    public function getMinFreshAge(): ?int
    {
        return $this->min_fresh;
    }
}
