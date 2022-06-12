<?php

namespace Lucinda\Headers\Request;

/**
 * Encapsulates value of HTTP request header: Cache-Control
 */
class CacheControl
{
    private bool $noCache = false;
    private bool $noStore = false;
    private ?int $maxAge = null;
    private ?int $maxStale = null;
    private ?int $minFresh = null;

    /**
     * Parses header value
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $parts = explode(",", $value);
        foreach ($parts as $element) {
            $key = "";
            $value = "";
            $position = strpos($element, "=");
            if ($position) {
                $key = trim(substr($element, 0, $position));
                $value = trim(substr($element, $position+1));
            } else {
                $key = trim($element);
            }

            $this->setDirectives($key, $value);
        }
    }

    /**
     * Sets Cache-Control directives
     *
     * @param  string $key
     * @param  string $value
     * @return void
     */
    private function setDirectives(string $key, string $value): void
    {
        switch ($key) {
        case "no-cache":
            $this->noCache = true;
            break;
        case "no-store":
            $this->noStore = true;
            break;
        case "max-age":
            $this->maxAge = (is_numeric($value) ? (int) $value : null);
            break;
        case "max-stale":
            $this->maxStale = (is_numeric($value) ? (int) $value : null);
            break;
        case "min-fresh":
            $this->minFresh = (is_numeric($value) ? (int) $value : null);
            break;
        }
    }

    /**
     * Checks if header came with directive: no-cache
     *
     * @return bool
     */
    public function isNoCache(): bool
    {
        return $this->noCache;
    }

    /**
     * Checks if header came with directive: no-store
     *
     * @return bool
     */
    public function isNoStore(): bool
    {
        return $this->noStore;
    }

    /**
     * Gets value of directive: max-age
     *
     * @return int|null
     */
    public function getMaxAge(): ?int
    {
        return $this->maxAge;
    }

    /**
     * Gets value of directive: max-stale
     *
     * @return int|null
     */
    public function getMaxStaleAge(): ?int
    {
        return $this->maxStale;
    }

    /**
     * Gets value of directive: min-fresh
     *
     * @return int|null
     */
    public function getMinFreshAge(): ?int
    {
        return $this->minFresh;
    }
}
