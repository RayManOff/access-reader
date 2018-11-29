<?php

namespace App;

/**
 * @author Gadel Raymanov <raymanovg@gmail.com>
 */
class AccessLogStatsBuilder
{
    private $reader;
    private $parser;

    public function __construct(Reader $reader, LogParser $parser)
    {
        $this->reader = $reader;
        $this->parser = $parser;
    }

    public function getStats()
    {
        return [];
    }
}