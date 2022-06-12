<?php

namespace Lucinda\Headers\Response;

/**
 * Encapsulates HTTP response header: Strict-Transport-Security
 */
class StrictTransportSecurity
{
    public const MAX_AGE = 31536000;
    private bool $includeSubdomains = false;
    private bool $preload = false;

    /**
     * Makes rule apply for subdomains too
     *
     * @return void
     */
    public function setIncludeSubdomains(): void
    {
        $this->includeSubdomains = true;
    }

    /**
     * Makes rule preloaded
     *
     * @return void
     */
    public function setPreload(): void
    {
        $this->preload = true;
    }

    /**
     * Gets string representation of header value
     *
     * @return string
     */
    public function toString(): string
    {
        $output = "max-age: ".self::MAX_AGE;
        if ($this->includeSubdomains) {
            $output .= "; includeSubdomains";
        }
        if ($this->preload) {
            $output .= "; preload";
        }
        return $output;
    }
}
