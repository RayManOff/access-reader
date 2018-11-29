<?php

namespace App;

/**
 * Interface LogParser
 * @package App
 */
interface LogParser
{
    public function parse(string $data) : array;
}