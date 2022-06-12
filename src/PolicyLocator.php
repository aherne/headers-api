<?php

namespace Lucinda\Headers;

/**
 * Locates HTTP header policies for your application based on 'headers' and 'routes' XML tags
 */
class PolicyLocator
{
    private Policy $policy;

    /**
     * Sets policy detection
     *
     * @param \SimpleXMLElement $xml
     * @param string            $requestedPage
     */
    public function __construct(\SimpleXMLElement $xml, string $requestedPage)
    {
        $this->setPolicy($xml, $requestedPage);
    }

    /**
     * Detects policy based on default settings @ 'headers' and page-specific settings @ 'routes' XML tags
     *
     * @param  \SimpleXMLElement $xml
     * @param  string            $requestedPage
     * @throws ConfigurationException
     */
    private function setPolicy(\SimpleXMLElement $xml, string $requestedPage): void
    {
        $parent = $xml->headers;
        if (!$parent) {
            throw new ConfigurationException("Tag 'headers' missing");
        }

        // get default policy
        $this->policy = new Policy();
        $this->policy->setCachingDisabled($parent);
        $this->policy->setExpirationPeriod($parent);
        $this->policy->setAllowedRequestHeaders($parent);
        $this->policy->setAllowedResponseHeaders($parent);
        $this->policy->setCorsMaxAge($parent);
        $this->policy->setCredentialsAllowed($parent);

        // use route-specific policy, if found
        $info = $xml->xpath("//routes/route[@id='".$requestedPage."']");
        if (!empty($info)) {
            $child = $info[0];
            $this->policy->setCachingDisabled($child);
            $this->policy->setExpirationPeriod($child);
            $this->policy->setAllowedMethods($child);
            return;
        }
    }

    /**
     * Gets policy detected from XML
     *
     * @return Policy
     */
    public function getPolicy(): Policy
    {
        return $this->policy;
    }
}
