<?php

namespace Lucinda\Headers;

/**
 * Binds Request and Policy to validate CORS request
 */
class CorsValidator
{
    private ?string $origin = null;
    private ?int $maxAge = null;
    private bool $allowCredentials = false;
    /**
     * @var string[]
     */
    private array $allowedMethods = [];
    /**
     * @var string[]
     */
    private array $allowedHeaders = [];
    /**
     * @var string[]
     */
    private array $exposedHeaders = [];

    /**
     * Performs cors validation without setting headers
     *
     * @param Request $request
     * @param Policy  $policy
     * @param string  $origin
     */
    public function __construct(Request $request, Policy $policy, string $origin)
    {
        if ($request->getOrigin()) {
            $this->origin = ($origin ? $origin : "*");
            if ($policy->getCredentialsAllowed()) {
                $this->allowCredentials = true;
            }
        }

        if ($requestMethod = $request->getAccessControlRequestMethod()) {
            $this->setAllowedMethods($policy, $requestMethod);
        }

        if ($request->getAccessControlRequestHeaders()) {
            $this->setAllowedHeaders($policy);
        }

        if ($headers = $policy->getAllowedResponseHeaders()) {
            $this->exposedHeaders = $headers;
        }

        if ($duration = $policy->getCorsMaxAge()) {
            $this->maxAge = $duration;
        }
    }

    /**
     * Detects allowed request methods based on policy
     *
     * @param  Policy $policy
     * @param  string $requestMethod
     * @return void
     */
    private function setAllowedMethods(Policy $policy, string $requestMethod): void
    {
        $methods = $policy->getAllowedMethods();
        if (!empty($methods)) {
            $this->allowedMethods = $methods;
        } else {
            $this->allowedMethods[] = $requestMethod;
        }
    }

    /**
     * Detects allowed request headers based on policy
     *
     * @param  Policy $policy
     * @return void
     */
    private function setAllowedHeaders(Policy $policy): void
    {
        $headers = $policy->getAllowedRequestHeaders();
        if (!empty($headers)) {
            $this->allowedHeaders = $headers;
        } else {
            $this->allowedHeaders[] = "*";
        }
    }

    /**
     * Gets value of allowed origin
     *
     * @return string|null
     */
    public function getAllowedOrigin(): ?string
    {
        return $this->origin;
    }

    /**
     * Gets cors max age
     *
     * @return int|null
     */
    public function getMaxAge(): ?int
    {
        return $this->maxAge;
    }

    /**
     * Gets whether credentials are allowed
     *
     * @return bool
     */
    public function isAllowCredentials(): bool
    {
        return $this->allowCredentials;
    }

    /**
     * Gets allowed request methods
     *
     * @return string[]
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }

    /**
     * Gets allowed request headers
     *
     * @return string[]
     */
    public function getAllowedHeaders(): array
    {
        return $this->allowedHeaders;
    }

    /**
     * Gets exposed headers
     *
     * @return string[]
     */
    public function getExposedHeaders(): array
    {
        return $this->exposedHeaders;
    }
}
