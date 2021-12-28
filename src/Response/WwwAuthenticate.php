<?php
namespace Lucinda\Headers\Response;

/**
 * Encapsulates HTTP response header: WWW-Authenticate
 */
class WwwAuthenticate
{
    private string $type;
    private ?string $realm = null;
    private array $challenges = [];

    /**
     * Constructs header based on type (usually: basic) and va
     *
     * @param string $type
     * @param ?string $realm
     */
    public function __construct(string $type, ?string $realm =null)
    {
        $this->type = $type;
        $this->realm = $realm;
    }
    
    /**
     * Adds a challenge (setting) to header (eg: Charset->UTF8)
     *
     * @param string $key
     * @param string $value
     */
    public function addChallenge(string $key, string $value): void
    {
        $this->challenges[$key] = $value;
    }
        
    /**
     * Gets string representation of header value
     *
     * @return string
     */
    public function toString(): string
    {
        $response = $this->type.($this->realm?" realm=\"".$this->realm."\"":"");
        foreach ($this->challenges as $key=>$value) {
            $response .= ", ".$key."=\"".$value."\"";
        }
        return $response;
    }
}
