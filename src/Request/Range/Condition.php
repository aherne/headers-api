<?php

namespace Lucinda\Headers\Request\Range;

/**
 * Encapsulates conditions set in a Range HTTP request header
 */
class Condition
{
    private ?int $start = null;
    private ?int $end = null;

    /**
     * Sets up a condition based on start and end directives.
     *
     * @param string $start
     * @param string $end
     */
    public function __construct(string $start, string $end)
    {
        if ($start!=="") {
            $this->start = (int) $start;
        }
        if ($end!="") {
            $this->end = (int) $end;
        }
    }

    /**
     * Gets value of range start
     *
     * @return int|null
     */
    public function getStart(): ?int
    {
        return $this->start;
    }

    /**
     * Gets value of range end
     *
     * @return int|null
     */
    public function getEnd(): ?int
    {
        return $this->end;
    }
}
