<?php

namespace App;

/**
 * @author Gadel Raymanov <raymanovg@gmail.com>
 */
class RegexParser implements LogParser
{
    const PATH_KEY = 'path';
    const STATUS_KEY = 'status';
    const SIZE_KEY = 'size';
    const URL_KEY = 'url';
    const USER_AGENT_KEY = 'user_agent';

    private $pattern = '/^.+"[A-Z]+\s(?<%s>\/[\S]+)\s.+"\s+(?<%s>\d+)[\s-]+(?<%s>\d+).+"(?<%s>\S+)"\s+"(?<%s>.+)"$/';

    public function __construct()
    {
        $this->pattern = sprintf(
            $this->pattern,
            self::PATH_KEY,
            self::STATUS_KEY,
            self::SIZE_KEY,
            self::URL_KEY,
            self::USER_AGENT_KEY
        );
    }

    public function parse(string $data) : array
    {
        if (!preg_match($this->pattern, $data, $matches)) {
            throw new \RuntimeException('Cannot parse [ ' . $data . ' ]');
        }

        return $this->build($matches);
    }

    protected function build($matches) : array
    {
        return [
            self::STATUS_KEY => (int) $matches[self::STATUS_KEY],
            self::PATH_KEY => $matches[self::PATH_KEY],
            self::SIZE_KEY => (int) $matches[self::SIZE_KEY],
            self::URL_KEY => $matches[self::URL_KEY],
            self::USER_AGENT_KEY => $matches[self::USER_AGENT_KEY]
        ];
    }
}