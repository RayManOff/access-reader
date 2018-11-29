<?php

namespace App;

/**
 * @author Gadel Raymanov <raymanovg@gmail.com>
 */
class RegexAccessParser
{
    private $pattern;
    private $needFields = [];

    public function __construct(string $pattern = null, $needFields = [])
    {
        if (!$pattern) {
            throw new \InvalidArgumentException('Pattern is necessary');
        }

        $this->pattern = $pattern;
        $this->needFields = array_flip($needFields);
    }
    public function parse(string $data) : array
    {
        if (!preg_match($this->pattern, $data, $matches)) {
            throw new \RuntimeException('Cannot parse this data');
        }

        return array_intersect_key($matches, $this->needFields);
    }
}