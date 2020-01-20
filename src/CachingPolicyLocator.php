<?php
namespace Lucinda\Headers;

class CachingPolicyLocator
{
    private $policy;

    public function __construct(\SimpleXMLElement $xml, string $requestedPage)
    {
        $this->setPolicy($xml, $requestedPage);
    }

    private function setPolicy(\SimpleXMLElement $xml, string $requestedPage): void
    {
        $caching = $xml->http_caching;
        if (!$caching) {
            throw new ConfigurationException("Tag 'http_caching' missing");
        }
        
        // use route-specific policy, if found
        $tmp = (array) $caching;
        if (!empty($tmp["route"])) {
            foreach ($tmp["route"] as $info) {
                $route = $info["url"];
                if ($route === null) {
                    throw new ConfigurationException("Attribute 'url' is mandatory for 'route' subtag of 'http_caching' tag");
                }
                if ($route == $requestedPage) {
                    $this->policy = new CachingPolicy($info);
                    return;
                }
            }
        }
        
        // if not, use default
        $this->policy = new CachingPolicy($caching);
    }

    public function getPolicy(): CachingPolicy
    {
        return $this->policy;
    }
}
