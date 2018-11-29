<?php

namespace App;

/**
 * Interface Reader
 * @package App
 */
interface Reader
{
    public function readByLine();
    public function size();
}