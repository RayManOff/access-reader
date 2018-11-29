<?php
namespace App;

/**
 * @author Gadel Raymanov <raymanovg@gmail.com>
 */
class FileReader implements Reader
{
    private $resource;
    
    public function __construct(string $filename)
    {
        if (!file_exists($filename)) {
            return new \InvalidArgumentException('There is no file ' . $filename);
        }

        $this->resource = fopen($filename, 'r');
        if (!is_resource($this->resource)) {
            return new \RuntimeException('Cannot read the file ' . $filename);
        }
    }

    public function readByLine()
    {
        while (!feof($this->resource)) {
            $buffer = fgets($this->resource);
            if ($buffer !== false) {
                yield $buffer;
            }
        }
    }
}