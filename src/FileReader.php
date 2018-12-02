<?php
namespace App;

/**
 * @author Gadel Raymanov <raymanovg@gmail.com>
 */
class FileReader implements Reader
{
    private $resource;
    private $filesize;
    
    public function __construct(string $filename)
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('There is no file ' . $filename);
        }

        $this->resource = fopen($filename, 'r');
        if (!is_resource($this->resource)) {
            throw new \RuntimeException('Cannot read the file ' . $filename);
        }

        $this->filesize = (int) filesize($filename);
    }

    public function size() : int
    {
        return $this->filesize;
    }

    /**
     * @return \Generator
     */
    public function readByLine()
    {
        while (!feof($this->resource)) {
            $buffer = fgets($this->resource);
            if ($buffer === false) {
                continue;
            }

            $buffer = trim($buffer);
            if (empty($buffer)) {
                continue;
            }

            yield $buffer;
        }
    }
}