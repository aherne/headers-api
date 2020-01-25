<?php
namespace Lucinda\Headers\Response;

/**
 * Encapsulates HTTP response header: Content-Disposition
 */
class ContentDisposition
{
    private $type;
    private $fileName=[];
    
    /**
     * Sets type (can be: inline, attachment)
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Sets attachment file name (in which case type MUST be: attachment)
     *
     * @param string $fileName
     * @param bool $isEncoded
     */
    public function setFileName(string $fileName, bool $isEncoded=false): void
    {
        $this->fileName = ["name"=>$fileName, "encoded"=>$isEncoded];
    }
    
    
    /**
     * Gets string representation of header value
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->type.($this->fileName?"; ".($this->fileName["encoded"]?"filename*":"filename")."=\"".$this->fileName["name"]."\"":"");
    }
}
