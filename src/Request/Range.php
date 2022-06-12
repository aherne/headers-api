<?php

namespace Lucinda\Headers\Request;

use Lucinda\Headers\Request\Range\Condition;

/**
 * Encapsulates value of HTTP request header: Range
 */
class Range
{
    private string $unit;
    /**
     * @var Condition[]
     */
    private array $conditions = [];

    /**
     * Parses header value
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $position = strpos($value, "=");
        if ($position===false) {
            return;
        }
        $this->unit = substr($value, 0, $position);

        $matches = [];
        preg_match_all("/(([0-9]+)?\-([0-9]+)?)/", substr($value, $position+1), $matches);
        foreach ($matches[0] as $i=>$value) {
            $this->conditions[]=new Condition($matches[2][$i], $matches[3][$i]);
        }
    }

    /**
     * Gets range unit (usually: bytes)
     *
     * @return string
     */
    public function getUnit(): string
    {
        return $this->unit;
    }

    /**
     * Gets range conditions
     *
     * @return Condition[]
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }
}
